<?php

namespace App\Http\Controllers;

use App\Models\ClosureType;
use App\Models\Company;
use App\Models\MetalType;
use App\Models\Order;
use App\Models\RingSize;
use App\Models\SettingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

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

        // 2. Start the query
        $query = Order::query()->with(['company', 'creator']);

        if ($admin->id !== 1) {
            $query->where('submitted_by', $admin->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_details', 'like', "%$search%")
                    ->orWhere('jewellery_details', 'like', "%$search%")
                    ->orWhere('diamond_details', 'like', "%$search%")
                    ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%$search%"));
            });
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('diamond_status')) {
            $query->where('diamond_status', $request->diamond_status);
        }

        $orders = $query->latest()->paginate(10);
        return view('orders.index', compact('orders'));
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

            // Upload files to Cloudinary
            $images = $this->uploadToCloudinary($request, 'images', 'orders/images', 10);
            $pdfs = $this->uploadToCloudinary($request, 'order_pdfs', 'orders/pdfs', 5, true);

            // Create and save the order
            $order = new Order();
            $this->assignOrderFields($order, $validated);
            $order->images = json_encode($images);
            $order->order_pdfs = json_encode($pdfs);
            $order->submitted_by = Auth::guard('admin')->id();

            $order->save();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'images_count' => count($images),
                'pdfs_count' => count($pdfs)
            ]);

            return redirect()->route('orders.index')
                ->with('success', 'Order created successfully! ' . count($images) . ' images and ' . count($pdfs) . ' PDFs uploaded to Cloudinary.');

        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
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

            // Handle new file uploads to Cloudinary
            $newImages = $this->uploadToCloudinary($request, 'images', 'orders/images', 10);
            $newPdfs = $this->uploadToCloudinary($request, 'order_pdfs', 'orders/pdfs', 5, true);

            // Decode existing JSON data safely
            $existingImages = json_decode($order->images ?? '[]', true) ?: [];
            $existingPdfs = json_decode($order->order_pdfs ?? '[]', true) ?: [];

            // Merge old + new files
            $order->images = json_encode(array_merge($existingImages, $newImages));
            $order->order_pdfs = json_encode(array_merge($existingPdfs, $newPdfs));

            // Update other fields
            $this->assignOrderFields($order, $validated);
            $order->submitted_by = Auth::guard('admin')->id();

            $order->save();

            Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'new_images' => count($newImages),
                'new_pdfs' => count($newPdfs)
            ]);

            return redirect()->route('orders.index')
                ->with('success', 'Order updated successfully! Added ' . count($newImages) . ' new images and ' . count($newPdfs) . ' new PDFs.');

        } catch (\Exception $e) {
            Log::error('Order update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    /**
     * Show the Order details.
     */
    public function show(Order $order)
    {
        $admin = Auth::guard('admin')->user();

        // If not super admin AND not the creator, abort
        if ($admin->id !== 1 && $order->submitted_by !== $admin->id) {
            abort(403, 'Unauthorized action.');
        }

        $metalTypes = MetalType::all();
        $ringSizes = RingSize::all();
        $settingTypes = SettingType::all();
        $closureTypes = ClosureType::all();
        $companies = Company::all();

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
        try {
            // Delete images from Cloudinary
            $images = is_string($order->images) ? json_decode($order->images, true) : ($order->images ?? []);
            foreach ($images as $image) {
                if (isset($image['public_id'])) {
                    $this->deleteFromCloudinary($image['public_id'], 'image');
                }
            }

            // Delete PDFs from Cloudinary
            $pdfs = is_string($order->order_pdfs) ? json_decode($order->order_pdfs, true) : ($order->order_pdfs ?? []);
            foreach ($pdfs as $pdf) {
                if (isset($pdf['public_id'])) {
                    $this->deleteFromCloudinary($pdf['public_id'], 'raw');
                }
            }

            $order->delete();

            Log::info('Order deleted successfully', ['order_id' => $order->id]);

            return redirect()->route('orders.index')->with('success', 'Order and all associated files deleted successfully from Cloudinary.');

        } catch (\Exception $e) {
            Log::error('Order deletion failed: ' . $e->getMessage());
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
            'client_details' => 'required|string',
            'diamond_status' => 'nullable|string|in:processed,completed,diamond_purchased,factory_making,diamond_completed',
            'company_id' => 'required|exists:companies,id',
            'gross_sell' => 'nullable|numeric|min:0',
            'dispatch_date' => 'nullable|date',
            'note' => 'nullable|in:priority,non_priority',
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
        $order->client_details = $validated['client_details'];
        $order->jewellery_details = $validated['jewellery_details'] ?? null;
        $order->diamond_details = $validated['diamond_details'] ?? null;
        $order->gold_detail_id = $validated['gold_detail_id'] ?? null;
        $order->ring_size_id = $validated['ring_size_id'] ?? null;
        $order->setting_type_id = $validated['setting_type_id'] ?? null;
        $order->earring_type_id = $validated['earring_type_id'] ?? null;
        $order->diamond_status = $validated['diamond_status'] ?? null;
        $order->gross_sell = $validated['gross_sell'] ?? 0;
        $order->company_id = $validated['company_id'];
        $order->note = $validated['note'] ?? null;
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

        $companies = Company::all();
        $metalTypes = MetalType::all();
        $ringSizes = RingSize::all();
        $settingTypes = SettingType::all();
        $closureTypes = ClosureType::all();

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