<?php

namespace App\Http\Controllers;

use App\Models\ClosureType;
use App\Models\Company;
use App\Models\Diamond;
use App\Models\MetalType;
use App\Models\Order;
use App\Models\RingSize;
use App\Models\SettingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\CurrencyService;

class OrderController extends Controller
{
    private $cloudinary;

    public function __construct()
    {
        // Initialize Cloudinary with direct configuration
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    /**
     * Display a listing of orders with filters.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        // Define shipped statuses
        $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];

        // 2. Start the base query (apply admin + search filters first)
        $baseQuery = Order::query()->with(['company', 'creator']);

        // Super admin sees all orders, regular admin sees only their submitted orders
        if (!$admin->is_super) {
            $baseQuery->where('submitted_by', $admin->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%$search%")
                    ->orWhere('client_email', 'like', "%$search%")
                    ->orWhere('client_address', 'like', "%$search%")
                    ->orWhere('jewellery_details', 'like', "%$search%")
                    ->orWhere('diamond_details', 'like', "%$search%")
                    ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%$search%"));
            });
        }

        // Count shipped orders (before excluding from base)
        $shippedOrdersCount = (clone $baseQuery)
            ->whereIn('diamond_status', $shippedStatuses)
            ->count();

        // Compute totals and breakdowns EXCLUDING shipped orders
        $nonShippedQuery = (clone $baseQuery)->whereNotIn('diamond_status', $shippedStatuses);
        $totalOrders = $nonShippedQuery->count();
        $orderTypeCounts = (clone $nonShippedQuery)
            ->select('order_type', DB::raw('count(*) as total'))
            ->groupBy('order_type')
            ->pluck('total', 'order_type')
            ->toArray();
        $statusCounts = (clone $nonShippedQuery)
            ->select('diamond_status', DB::raw('count(*) as total'))
            ->groupBy('diamond_status')
            ->pluck('total', 'diamond_status')
            ->toArray();

        // Now apply optional filters for the listing
        $query = clone $baseQuery;

        // If shipped filter is applied, show only shipped orders
        if ($request->filled('shipped') && $request->shipped == '1') {
            $query->whereIn('diamond_status', $shippedStatuses);
        } else {
            // Otherwise, hide shipped orders from main listing
            $query->whereNotIn('diamond_status', $shippedStatuses);
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('diamond_status')) {
            $query->where('diamond_status', $request->diamond_status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(10);
        return view('orders.index', compact('orders', 'totalOrders', 'orderTypeCounts', 'statusCounts', 'shippedOrdersCount'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        return view('orders.create');
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateOrder($request);

            // Check if diamond SKU is provided and validate it's not already sold
            if (!empty($validated['diamond_sku'])) {
                $diamond = Diamond::where('sku', $validated['diamond_sku'])->first();

                if (!$diamond) {
                    return back()->withInput()->with('error', 'Diamond with SKU "' . $validated['diamond_sku'] . '" not found.');
                }

                if ($diamond->is_sold_out === 'Sold') {
                    return back()->withInput()->with('error', 'Diamond with SKU "' . $validated['diamond_sku'] . '" is already sold. Please select a different diamond.');
                }
            }

            DB::beginTransaction();

            // Create the order first
            $order = new Order();
            $this->assignOrderFields($order, $validated);
            $order->submitted_by = Auth::guard('admin')->id();
            $order->save();

            // Upload files to Cloudinary (wrapped in try-catch for cleanup)
            try {
                $images = $this->uploadToCloudinary($request, 'images', 'orders/images', 10);
                $pdfs = $this->uploadToCloudinary($request, 'order_pdfs', 'orders/pdfs', 5, true);

                $order->images = json_encode($images);
                $order->order_pdfs = json_encode($pdfs);
                $order->save();

            } catch (\Exception $e) {
                // If upload fails after order created, still commit order but log the upload failure
                Log::error('File upload failed during order creation', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
                $images = [];
                $pdfs = [];
            }

            // If diamond SKU is provided, mark it as sold
            if (!empty($validated['diamond_sku'])) {
                Log::info('Attempting to mark diamond as sold', [
                    'sku' => $validated['diamond_sku'],
                    'sold_price' => $validated['gross_sell'] ?? 0
                ]);

                $diamondController = new DiamondController();
                // Convert sold price from INR to USD before marking diamond as sold
                $currencyService = app(CurrencyService::class);
                $soldPriceInr = $validated['gross_sell'] ?? 0;
                $soldPriceUsd = $currencyService->inrToUsd((float) $soldPriceInr) ?? 0;
                $result = $diamondController->markSoldOutBySku($validated['diamond_sku'], $soldPriceUsd);

                if ($result) {
                    Log::info('Diamond successfully marked as sold', ['diamond_id' => $result->id]);

                    // Notify all admins about the diamond sale
                    $currentAdmin = Auth::guard('admin')->user();
                    $allAdmins = \App\Models\Admin::where('id', '!=', $currentAdmin->id)->get();

                    foreach ($allAdmins as $admin) {
                        try {
                            $admin->notify(new \App\Notifications\DiamondSoldNotification($result, $currentAdmin));
                        } catch (\Throwable $e) {
                            Log::error('Failed to send diamond sold notification', [
                                'admin_id' => $admin->id,
                                'diamond_id' => $result->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    Log::warning('Failed to mark diamond as sold - diamond not found or error occurred', [
                        'sku' => $validated['diamond_sku']
                    ]);
                }
            }

            DB::commit();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_type' => $order->order_type,
                'images_count' => count($images),
                'pdfs_count' => count($pdfs),
                'diamond_sku' => $validated['diamond_sku'] ?? null,
                'created_by' => Auth::guard('admin')->id()
            ]);

            return redirect()->route('orders.index')
                ->with('success', 'Order created successfully! ' . count($images) . ' images and ' . count($pdfs) . ' PDFs uploaded to Cloudinary.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors - pass through
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing an order.
     */
    public function edit(Order $order)
    {
        return view('orders.edit', compact('order'));
    }

    /**
     * Update an existing order in storage.
     */
    public function update(Request $request, Order $order)
    {
        try {
            $validated = $this->validateOrder($request);

            DB::beginTransaction();

            // Handle new file uploads to Cloudinary
            $newImages = $this->uploadToCloudinary($request, 'images', 'orders/images', 10);
            $newPdfs = $this->uploadToCloudinary($request, 'order_pdfs', 'orders/pdfs', 5, true);

            // Decode existing JSON data safely
            $existingImages = json_decode($order->images ?? '[]', true) ?: [];
            $existingPdfs = json_decode($order->order_pdfs ?? '[]', true) ?: [];

            // Merge old + new files
            $order->images = json_encode(array_merge($existingImages, $newImages));
            $order->order_pdfs = json_encode(array_merge($existingPdfs, $newPdfs));

            // Track if diamond SKU changed
            $oldDiamondSku = $order->diamond_sku;
            $newDiamondSku = $validated['diamond_sku'] ?? null;

            // Validate new diamond SKU is not already sold (if changed)
            if (!empty($newDiamondSku) && $newDiamondSku !== $oldDiamondSku) {
                $diamond = Diamond::where('sku', $newDiamondSku)->first();

                if (!$diamond) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Diamond with SKU "' . $newDiamondSku . '" not found.');
                }

                if ($diamond->is_sold_out === 'Sold') {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Diamond with SKU "' . $newDiamondSku . '" is already sold. Please select a different diamond.');
                }
            }

            // Update other fields
            $this->assignOrderFields($order, $validated);
            // Track who modified this order (original creator in submitted_by stays unchanged)
            $order->last_modified_by = Auth::guard('admin')->id();

            $order->save();

            // If diamond SKU changed or was newly added, mark the new one as sold
            if (!empty($newDiamondSku) && $newDiamondSku !== $oldDiamondSku) {
                $diamondController = new DiamondController();
                // Convert sold price from INR to USD before marking diamond as sold
                $currencyService = app(CurrencyService::class);
                $soldPriceInr = $validated['gross_sell'] ?? 0;
                $soldPriceUsd = $currencyService->inrToUsd((float) $soldPriceInr) ?? 0;
                $diamondController->markSoldOutBySku($newDiamondSku, $soldPriceUsd);
            }

            DB::commit();

            Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'new_images' => count($newImages),
                'new_pdfs' => count($newPdfs),
                'old_diamond_sku' => $oldDiamondSku,
                'new_diamond_sku' => $newDiamondSku,
                'updated_by' => Auth::guard('admin')->id()
            ]);

            return redirect()->route('orders.index')
                ->with('success', 'Order updated successfully! Added ' . count($newImages) . ' new images and ' . count($newPdfs) . ' new PDFs.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::guard('admin')->id()
            ]);
            return back()->withInput()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    /**
     * Show the Order details.
     */
    public function show(Order $order)
    {
        $admin = Auth::guard('admin')->user();

        // Super admin can view all orders, regular admin can only view their own
        if (!$admin->is_super && $order->submitted_by !== $admin->id) {
            abort(403, 'Unauthorized action.');
        }

        // Eager load relationships
        $order->load(['goldDetail', 'ringSize', 'settingType', 'earringDetail', 'company', 'creator']);

        $metalTypes = Cache::remember('metal_types_all', 3600, fn() => MetalType::all());
        $ringSizes = Cache::remember('ring_sizes_all', 3600, fn() => RingSize::all());
        $settingTypes = Cache::remember('setting_types_all', 3600, fn() => SettingType::all());
        $closureTypes = Cache::remember('closure_types_all', 3600, fn() => ClosureType::all());
        $companies = Cache::remember('companies_all', 3600, fn() => Company::all());

        return view('orders.show', compact(
            'order',
            'metalTypes',
            'ringSizes',
            'settingTypes',
            'closureTypes',
            'companies'
        ));
    }

    /**
     * Delete an order and its attached files from Cloudinary.
     */
    public function destroy(Order $order)
    {
        $orderId = $order->id;
        $deletedImages = 0;
        $deletedPdfs = 0;

        try {
            // Delete images from Cloudinary
            $images = is_string($order->images) ? json_decode($order->images, true) : ($order->images ?? []);
            foreach ($images as $image) {
                if (isset($image['public_id'])) {
                    if ($this->deleteFromCloudinary($image['public_id'], 'image')) {
                        $deletedImages++;
                    }
                }
            }

            // Delete PDFs from Cloudinary
            $pdfs = is_string($order->order_pdfs) ? json_decode($order->order_pdfs, true) : ($order->order_pdfs ?? []);
            foreach ($pdfs as $pdf) {
                if (isset($pdf['public_id'])) {
                    if ($this->deleteFromCloudinary($pdf['public_id'], 'raw')) {
                        $deletedPdfs++;
                    }
                }
            }

            $order->delete();

            Log::info('Order deleted successfully', [
                'order_id' => $orderId,
                'deleted_images' => $deletedImages,
                'deleted_pdfs' => $deletedPdfs,
                'deleted_by' => Auth::guard('admin')->id()
            ]);

            return redirect()->route('orders.index')->with('success', 'Order and all associated files deleted successfully from Cloudinary.');

        } catch (\Exception $e) {
            Log::error('Order deletion failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'deleted_images' => $deletedImages,
                'deleted_pdfs' => $deletedPdfs
            ]);
            return back()->with('error', 'Failed to delete order: ' . $e->getMessage());
        }
    }

    /**
     * Validate form input for all order types.
     */
    private function validateOrder(Request $request): array
    {
        $rules = [
            'order_type' => 'required|in:ready_to_ship,custom_diamond,custom_jewellery',
            'client_name' => 'required|string|max:191',
            'client_address' => 'required|string',
            'client_mobile' => 'nullable|string|max:40',
            'client_tax_id' => 'nullable|string|max:100',
            'client_email' => 'required|email|max:191',
            'diamond_sku' => 'nullable|string|max:191',
            'diamond_status' => 'nullable|string|in:r_order_in_process,r_order_shipped,d_diamond_in_discuss,d_diamond_in_making,d_diamond_completed,d_diamond_in_certificate,d_order_shipped,j_diamond_in_progress,j_diamond_completed,j_diamond_in_discuss,j_cad_in_progress,j_cad_done,j_order_completed,j_order_in_qc,j_qc_done,j_order_shipped,j_order_hold',
            'company_id' => 'required|exists:companies,id',
            'gross_sell' => 'nullable|numeric|min:0',
            'dispatch_date' => 'nullable|date',
            'note' => 'nullable|in:priority,non_priority',
            'special_notes' => 'nullable|string|max:2000',
            'shipping_company_name' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'tracking_url' => 'nullable|url',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240',
            'order_pdfs.*' => 'nullable|mimes:pdf|max:10240',
        ];

        switch ($request->order_type) {
            case 'ready_to_ship':
                $rules += [
                    'jewellery_details' => 'nullable|string',
                    'diamond_details' => 'nullable|string',
                    'product_other' => 'nullable|string|max:191',
                    'gold_detail_id' => 'nullable|exists:metal_types,id',
                    'ring_size_id' => 'nullable|exists:ring_sizes,id',
                    'setting_type_id' => 'nullable|exists:setting_types,id',
                    'earring_type_id' => 'nullable|exists:closure_types,id',
                ];
                break;

            case 'custom_diamond':
                $rules += [
                    'diamond_details' => 'required|string',
                ];
                break;

            case 'custom_jewellery':
                $rules += [
                    'jewellery_details' => 'required|string',
                    'diamond_details' => 'nullable|string',
                    'product_other' => 'nullable|string|max:191',
                    'gold_detail_id' => 'nullable|exists:metal_types,id',
                    'ring_size_id' => 'nullable|exists:ring_sizes,id',
                    'setting_type_id' => 'nullable|exists:setting_types,id',
                    'earring_type_id' => 'nullable|exists:closure_types,id',
                ];
                break;
        }

        return $request->validate($rules);
    }

    /**
     * Assign common validated fields to Order model.
     */
    private function assignOrderFields(Order $order, array $validated): void
    {
        $order->order_type = $validated['order_type'];
        $order->client_name = $validated['client_name'] ?? null;
        $order->client_address = $validated['client_address'] ?? null;
        $order->client_mobile = $validated['client_mobile'] ?? null;
        $order->client_tax_id = $validated['client_tax_id'] ?? null;
        $order->client_email = $validated['client_email'] ?? null;
        $order->jewellery_details = $validated['jewellery_details'] ?? null;
        $order->diamond_details = $validated['diamond_details'] ?? null;
        $order->diamond_sku = $validated['diamond_sku'] ?? null;
        $order->gold_detail_id = $validated['gold_detail_id'] ?? null;
        $order->ring_size_id = $validated['ring_size_id'] ?? null;
        $order->setting_type_id = $validated['setting_type_id'] ?? null;
        $order->earring_type_id = $validated['earring_type_id'] ?? null;
        $order->product_other = $validated['product_other'] ?? null;
        $order->diamond_status = $validated['diamond_status'] ?? null;
        $order->gross_sell = $validated['gross_sell'] ?? 0;
        $order->company_id = $validated['company_id'];
        $order->note = $validated['note'] ?? null;
        $order->special_notes = $validated['special_notes'] ?? null;
        $order->shipping_company_name = $validated['shipping_company_name'] ?? null;
        $order->tracking_number = $validated['tracking_number'] ?? null;
        $order->tracking_url = $validated['tracking_url'] ?? null;
        $order->dispatch_date = $validated['dispatch_date'] ?? null;
    }

    /**
     * Upload files to Cloudinary using direct SDK.
     */
    private function uploadToCloudinary(Request $request, string $field, string $folder, int $maxFiles, bool $isPdf = false): array
    {
        $uploadedFiles = [];

        if (!$request->hasFile($field)) {
            return $uploadedFiles;
        }

        $files = $request->file($field);

        foreach ($files as $index => $file) {
            if ($index >= $maxFiles) {
                Log::warning("Max files limit reached for {$field}");
                break;
            }

            try {
                // Validate file
                if (!$file->isValid()) {
                    Log::error("Invalid file upload: {$file->getClientOriginalName()}");
                    continue;
                }

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $timestamp = time();
                $uniqueId = uniqid();

                // Create unique public_id
                $publicId = "{$folder}/{$timestamp}_{$uniqueId}";

                // Upload options
                $uploadOptions = [
                    'public_id' => $publicId,
                    'folder' => $folder,
                ];

                Log::info("Uploading to Cloudinary", [
                    'file' => $file->getClientOriginalName(),
                    'type' => $isPdf ? 'PDF' : 'Image',
                    'size' => $file->getSize()
                ]);

                // Upload using Cloudinary Upload API
                $uploadApi = $this->cloudinary->uploadApi();

                if ($isPdf) {
                    // For PDFs
                    $uploadOptions['resource_type'] = 'raw';
                    $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);
                } else {
                    // For images with optimization
                    $uploadOptions['transformation'] = [
                        'quality' => 'auto:good',
                        'fetch_format' => 'auto'
                    ];
                    $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);
                }

                // Store file information
                $fileInfo = [
                    'url' => $result['secure_url'],
                    'public_id' => $result['public_id'],
                    'name' => $originalName . '.' . $extension,
                    'format' => $extension,
                    'size' => $file->getSize(),
                    'resource_type' => $isPdf ? 'raw' : 'image',
                    'uploaded_at' => now()->toDateTimeString(),
                ];

                $uploadedFiles[] = $fileInfo;

                Log::info("Successfully uploaded to Cloudinary", [
                    'file' => $originalName,
                    'url' => $fileInfo['url'],
                    'public_id' => $result['public_id']
                ]);

            } catch (\Exception $e) {
                Log::error('Cloudinary upload failed', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'line' => $e->getLine()
                ]);

                // Continue with next file
                continue;
            }
        }

        return $uploadedFiles;
    }

    /**
     * Delete single file from Cloudinary
     */
    private function deleteFromCloudinary(string $publicId, string $resourceType = 'image'): bool
    {
        try {
            $uploadApi = $this->cloudinary->uploadApi();
            $uploadApi->destroy($publicId, ['resource_type' => $resourceType]);

            Log::info("File deleted from Cloudinary", [
                'public_id' => $publicId,
                'resource_type' => $resourceType
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete from Cloudinary', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Dynamically load form partial based on order type.
     */
    public function loadFormPartial($type, Request $request)
    {
        $view = match ($type) {
            'ready_to_ship' => 'orders.partials.ready_to_ship',
            'custom_diamond' => 'orders.partials.custom_diamond',
            'custom_jewellery' => 'orders.partials.custom_jewellery',
            default => null,
        };

        if (!$view || !view()->exists($view)) {
            return response('<div class="alert alert-danger">Invalid form type selected.</div>', 404);
        }

        $order = null;
        if ($request->has('edit') && $request->edit === 'true' && $request->has('id')) {
            $order = Order::find($request->id);
        }

        $companies = Cache::remember('companies_all', 3600, fn() => Company::all());
        $metalTypes = Cache::remember('metal_types_all', 3600, fn() => MetalType::all());
        $ringSizes = Cache::remember('ring_sizes_all', 3600, fn() => RingSize::all());
        $settingTypes = Cache::remember('setting_types_all', 3600, fn() => SettingType::all());
        $closureTypes = Cache::remember('closure_types_all', 3600, fn() => ClosureType::all());

        return view($view, compact(
            'order',
            'companies',
            'metalTypes',
            'ringSizes',
            'settingTypes',
            'closureTypes'
        ))->render();
    }
}