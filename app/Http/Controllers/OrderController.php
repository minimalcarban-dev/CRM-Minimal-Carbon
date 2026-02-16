<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\ClosureType;
use App\Models\Company;
use App\Models\Diamond;
use App\Models\MetalType;
use App\Models\Order;
use App\Models\RingSize;
use App\Models\SettingType;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\CurrencyService;
use App\Models\OrderDraft;
use App\Notifications\DiamondSoldNotification;
use App\Models\MeleeTransaction;
use App\Models\MeleeDiamond;
use App\Services\ShippingTrackingService;
use App\Notifications\OrderUpdatedNotification;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Support\Facades\Notification;
use App\Services\AuditLogger;

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
        $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped']; // Define shipped statuses
        $baseQuery = Order::query()->with(['company', 'creator']); // Start the base query (apply admin + search filters first)

        // Super admin sees all orders, regular admin sees only their submitted orders
        // Unless they have 'orders.view_team' permission which allows viewing team orders
        if (!$admin->is_super) {
            // Check if admin has view_team permission
            if (!$admin->hasPermission('orders.view_team')) {
                $baseQuery->where('submitted_by', $admin->id);
            }

            // Restrict visibility of dispatched orders:
            // Normal admin cannot see shipped orders older than 10 days
            $baseQuery->where(function ($q) use ($shippedStatuses) {
                // 1. Order is NOT in shipped status (or status is null)
                $q->whereNotIn('diamond_status', $shippedStatuses)
                    ->orWhereNull('diamond_status')
                    // 2. OR Order IS shipped, but dispatch_date is within the last 10 days
                    ->orWhere(function ($subQ) use ($shippedStatuses) {
                        $subQ->whereIn('diamond_status', $shippedStatuses)
                            ->whereDate('dispatch_date', '>=', now()->subDays(10));
                    });
            });
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

        // Count In Transit orders based on tracking_status
        $inTransitCount = (clone $baseQuery)
            ->where('tracking_status', 'In Transit')
            ->count();

        // Compute totals and breakdowns EXCLUDING shipped orders
        // Note: whereNotIn excludes NULL values, so we need to explicitly include them  
        $nonShippedQuery = (clone $baseQuery)->where(function ($q) use ($shippedStatuses) {
            $q->whereNotIn('diamond_status', $shippedStatuses)
                ->orWhereNull('diamond_status');
        });
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

        // ===== TODAY'S SALES STATS (NEW) =====
        $todaysSales = Order::whereDate('created_at', now()->toDateString())
            // ->whereIn('diamond_status', $shippedStatuses)
            ->sum('gross_sell');
        $todaysOrderCount = Order::whereDate('created_at', now()->toDateString())
            // ->whereIn('diamond_status', $shippedStatuses)
            ->count();

        // Month Sales Stats - ALL orders count as sales (prepaid model)
        $monthSales = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('gross_sell');

        // Get company sales progress for active companies
        $companySalesStats = Company::where('status', 'active')
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'logo' => $company->logo,
                    'currency_symbol' => $company->currency_symbol,
                    'todays_orders' => $company->todays_order_count,
                    'todays_sales' => $company->todays_sales,
                    'month_to_date' => $company->month_to_date_sales,
                    'current_target' => $company->current_month_target,
                    'target_progress' => $company->target_progress,
                ];
            });

        // Now apply optional filters for the listing
        $query = clone $baseQuery;

        // If shipped filter is applied, show only shipped orders
        if ($request->filled('shipped') && $request->shipped == '1') {
            $query->whereIn('diamond_status', $shippedStatuses);
        } elseif ($request->filled('in_transit') && $request->in_transit == '1') {
            $query->where('tracking_status', 'In Transit');
        } else {
            // Otherwise, hide shipped orders from main listing
            // Note: whereNotIn excludes NULL values, so we need to explicitly include them
            $query->where(function ($q) use ($shippedStatuses) {
                $q->whereNotIn('diamond_status', $shippedStatuses)
                    ->orWhereNull('diamond_status');
            });
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

        // Overdue filter
        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->whereDate('dispatch_date', '<', now()->startOfDay())
                ->where(function ($q) use ($shippedStatuses) {
                    $q->whereNotIn('diamond_status', $shippedStatuses)
                        ->orWhereNull('diamond_status');
                });
        }

        $orders = $query->latest()->paginate(20);
        return view('orders.index', compact(
            'orders',
            'totalOrders',
            'orderTypeCounts',
            'statusCounts',
            'shippedOrdersCount',
            'inTransitCount',
            'todaysSales',
            'monthSales',
            'todaysOrderCount',
            'companySalesStats'
        ));
    }


    public function create(Request $request)
    {
        $draft = null;
        $draftData = [];

        // Check if resuming from a draft
        if ($request->has('draft_id')) {
            $draft = OrderDraft::find($request->draft_id);
            if ($draft) {
                $draftData = $draft->form_data ?? [];
            }
        }

        return view('orders.create', compact('draft', 'draftData'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validateOrder($request);

            // Collect all diamond SKUs to process (from both old single field and new array field)
            $allSkus = [];
            if (!empty($validated['diamond_skus']) && is_array($validated['diamond_skus'])) {
                $allSkus = array_filter(array_unique($validated['diamond_skus']));
            } elseif (!empty($validated['diamond_sku'])) {
                $allSkus = [$validated['diamond_sku']];
            }

            // Validate all diamond SKUs first (before creating order)
            $validatedDiamonds = [];
            foreach ($allSkus as $sku) {
                $diamond = Diamond::where('sku', $sku)->first();

                if (!$diamond) {
                    $errorMsg = 'Diamond with SKU "' . $sku . '" not found.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $errorMsg], 422);
                    }
                    return back()->withInput()->with('error', $errorMsg);
                }

                if ($diamond->is_sold_out === 'Sold') {
                    $errorMsg = 'Diamond with SKU "' . $sku . '" is already sold. Please remove it and select a different diamond.';
                    if ($request->expectsJson()) {
                        return response()->json(['success' => false, 'message' => $errorMsg], 422);
                    }
                    return back()->withInput()->with('error', $errorMsg);
                }

                $validatedDiamonds[] = $diamond;
            }

            DB::beginTransaction();

            // Create the order first
            $order = new Order();
            $this->assignOrderFields($order, $validated);
            $order->submitted_by = Auth::guard('admin')->id();

            // Auto-create or reuse client
            $clientId = null;
            if (!empty($validated['client_email']) || !empty($validated['client_name'])) {
                $client = Client::firstOrCreate(
                    ['email' => $validated['client_email'] ?? null],
                    [
                        'name' => $validated['client_name'] ?? null,
                        'address' => $validated['client_address'] ?? null,
                        'mobile' => $validated['client_mobile'] ?? null,
                        'tax_id' => $validated['client_tax_id'] ?? null,
                        'created_by' => Auth::guard('admin')->id(),
                    ]
                );
                // Update client if any fields differ
                $client->update([
                    'name' => $validated['client_name'] ?? $client->name,
                    'address' => $validated['client_address'] ?? $client->address,
                    'mobile' => $validated['client_mobile'] ?? $client->mobile,
                    'tax_id' => $validated['client_tax_id'] ?? $client->tax_id,
                ]);
                $clientId = $client->id;
            }
            $order->client_id = $clientId;

            $order->save();

            // Upload files to Cloudinary (wrapped in try-catch for cleanup)
            try {
                $images = $this->uploadToCloudinary($request, 'images', 'orders/images', 10);
                $pdfs = $this->uploadToCloudinary($request, 'order_pdfs', 'orders/pdfs', 5, true);

                // Model handles JSON conversion automatically via casts
                $order->images = $images;
                $order->order_pdfs = $pdfs;
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

            // Mark all validated diamonds as sold
            $soldDiamonds = [];
            if (!empty($validatedDiamonds)) {
                $diamondController = new DiamondController();

                // Get individual diamond prices from request (already in USD)
                $diamondPrices = $validated['diamond_prices'] ?? [];

                foreach ($validatedDiamonds as $diamond) {
                    // Get specific price for this diamond (already in USD, no conversion needed)
                    $soldPriceUsd = (float) ($diamondPrices[$diamond->sku] ?? 0);

                    Log::info('Attempting to mark diamond as sold', [
                        'sku' => $diamond->sku,
                        'sold_price' => $soldPriceUsd
                    ]);

                    $result = $diamondController->markSoldOutBySku($diamond->sku, $soldPriceUsd);

                    if ($result) {
                        Log::info('Diamond successfully marked as sold', ['diamond_id' => $result->id]);
                        $soldDiamonds[] = $result;
                    } else {
                        Log::warning('Failed to mark diamond as sold', ['sku' => $diamond->sku]);
                    }
                }

                // Notify all admins about the diamond sale(s) - batch notification
                if (!empty($soldDiamonds)) {
                    /** @var Admin $currentAdmin */
                    $currentAdmin = Auth::guard('admin')->user();
                    $allAdmins = Admin::where('id', '!=', $currentAdmin->id)->get();

                    if ($allAdmins->isNotEmpty()) {
                        foreach ($soldDiamonds as $soldDiamond) {
                            Notification::send($allAdmins, new DiamondSoldNotification($soldDiamond, $currentAdmin));
                        }
                    }
                }
            }

            // --- Melee Stock Deduction Logic ---
            if (!empty($order->melee_diamond_id) && $order->melee_pieces > 0) {
                MeleeTransaction::create([
                    'melee_diamond_id' => $order->melee_diamond_id,
                    'transaction_type' => 'out',
                    'pieces' => abs($order->melee_pieces),
                    'carat_weight' => abs($order->melee_carat ?? 0),
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'created_by' => Auth::guard('admin')->id(),
                    'notes' => 'Stock used in Order #' . $order->id,
                ]);
            }

            DB::commit();

            // --- Notify Super Admins about the new order ---
            $superAdmins = Admin::where('is_super', true)->get();
            /** @var Admin $createdBy */
            $createdBy = Auth::guard('admin')->user();
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new OrderCreatedNotification($order, $createdBy));
            }

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_type' => $order->order_type,
                'images_count' => count($images),
                'pdfs_count' => count($pdfs),
                'diamond_sku' => $validated['diamond_sku'] ?? null,
                'melee_stock_id' => $order->melee_diamond_id,
                'created_by' => Auth::guard('admin')->id()
            ]);

            $successMsg = 'Order created successfully! ' . count($images) . ' images and ' . count($pdfs) . ' PDFs uploaded.';

            // Order created successfully - clear any auto-save drafts for this admin
            OrderDraftController::clearAutoSaveDraft(
                Auth::guard('admin')->id(),
                $validated['order_type'] ?? null
            );

            // Also delete the specific draft if resuming from one
            if ($request->has('draft_id')) {
                OrderDraft::where('id', $request->draft_id)->delete();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'redirect' => route('orders.index'),
                    'order_id' => $order->id
                ]);
            }

            return redirect()->route('orders.index')->with('success', $successMsg);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors - save as draft and pass through
            $this->saveDraftOnError($request, $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => Auth::guard('admin')->id()
            ]);

            $errorMsg = 'Failed to create order: ' . $e->getMessage();

            // Save draft on error
            $draft = $this->saveDraftOnError($request, $errorMsg);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'draft_id' => $draft?->id
                ], 500);
            }

            // Redirect to draft edit if saved successfully
            if ($draft) {
                return redirect()->route('orders.drafts.resume', $draft->id)
                    ->with('error', $errorMsg)
                    ->with('info', 'Your order has been saved as a draft. You can resume editing from here.');
            }

            return back()->withInput()->with('error', $errorMsg);
        }
    }

    /**
     * Save order data as draft when an error occurs.
     */
    private function saveDraftOnError(Request $request, string $errorMessage): ?OrderDraft
    {
        try {
            $formData = $request->except(['_token', '_method', 'images', 'order_pdfs']);

            $draft = new OrderDraft();
            $draft->admin_id = Auth::guard('admin')->id();
            $draft->order_type = $request->input('order_type');
            $draft->form_data = $formData;
            $draft->error_message = $errorMessage;
            $draft->source = 'error';
            $draft->client_name = $request->input('client_name');
            $draft->company_id = $request->input('company_id');
            $draft->save();

            Log::info('Order saved as draft due to error', [
                'draft_id' => $draft->id,
                'admin_id' => $draft->admin_id,
                'error' => $errorMessage
            ]);

            return $draft;
        } catch (\Exception $e) {
            Log::error('Failed to save order as draft', [
                'error' => $e->getMessage()
            ]);
            return null;
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

            // Correctly retrieve and normalize existing images (handle potential double-encoding)
            $existingImages = $order->images;
            if (is_string($existingImages)) {
                $existingImages = json_decode($existingImages, true) ?: [];
            }
            if (is_array($existingImages) && count($existingImages) === 1 && is_string($existingImages[0])) {
                $decoded = json_decode($existingImages[0], true);
                if (is_array($decoded))
                    $existingImages = $decoded;
            }
            $existingImages = is_array($existingImages) ? $existingImages : [];

            // Correctly retrieve and normalize existing PDFs
            $existingPdfs = $order->order_pdfs;
            if (is_string($existingPdfs)) {
                $existingPdfs = json_decode($existingPdfs, true) ?: [];
            }
            if (is_array($existingPdfs) && count($existingPdfs) === 1 && is_string($existingPdfs[0])) {
                $decoded = json_decode($existingPdfs[0], true);
                if (is_array($decoded))
                    $existingPdfs = $decoded;
            }
            $existingPdfs = is_array($existingPdfs) ? $existingPdfs : [];

            // Merge old + new files
            $order->images = array_merge($existingImages, $newImages);
            $order->order_pdfs = array_merge($existingPdfs, $newPdfs);

            // Track if diamond SKU changed
            $oldDiamondSku = $order->diamond_sku;
            $newDiamondSku = $validated['diamond_sku'] ?? '';

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

            // --- Snapshot old values BEFORE assigning new fields (for audit log) ---
            $auditFields = [
                'order_type' => 'Order Type',
                'client_name' => 'Client Name',
                'client_address' => 'Client Address',
                'client_mobile' => 'Client Mobile',
                'client_email' => 'Client Email',
                'client_tax_id' => 'Client Tax ID',
                'client_tax_id_type' => 'Tax ID Type',
                'jewellery_details' => 'Jewellery Details',
                'diamond_details' => 'Diamond Details',
                'diamond_sku' => 'Diamond SKU',
                'product_other' => 'Other Product',
                'special_notes' => 'Special Notes',
                'shipping_company_name' => 'Shipping Company',
                'tracking_number' => 'Tracking Number',
                'tracking_url' => 'Tracking URL',
                'diamond_status' => 'Diamond Status',
                'note' => 'Priority',
                'gross_sell' => 'Gross Sell',
                'dispatch_date' => 'Dispatch Date',
                'company_id' => 'Company',
                'gold_detail_id' => 'Metal Type',
                'ring_size_id' => 'Ring Size',
                'setting_type_id' => 'Setting Type',
                'earring_type_id' => 'Earring Type',
                'melee_diamond_id' => 'Melee Diamond',
                'melee_pieces' => 'Melee Pieces',
                'melee_carat' => 'Melee Carat',
                'melee_price_per_ct' => 'Melee Price/CT',
            ];
            $oldSnapshot = [];
            foreach (array_keys($auditFields) as $field) {
                $oldSnapshot[$field] = $order->getOriginal($field);
            }

            // Update other fields
            $this->assignOrderFields($order, $validated);
            // Track who modified this order (original creator in submitted_by stays unchanged)
            $order->last_modified_by = Auth::guard('admin')->id();

            // --- Compute diff and log audit entry ---
            $oldValues = [];
            $newValues = [];
            // Helper: resolve FK IDs to human-readable names
            $fkResolvers = [
                'company_id' => fn($id) => $id ? (Company::find($id)->name ?? "ID:$id") : null,
                'gold_detail_id' => fn($id) => $id ? (MetalType::find($id)->name ?? "ID:$id") : null,
                'ring_size_id' => fn($id) => $id ? (RingSize::find($id)->name ?? "ID:$id") : null,
                'setting_type_id' => fn($id) => $id ? (SettingType::find($id)->name ?? "ID:$id") : null,
                'earring_type_id' => fn($id) => $id ? (ClosureType::find($id)->name ?? "ID:$id") : null,
                'melee_diamond_id' => fn($id) => $id ? (MeleeDiamond::find($id)->name ?? "ID:$id") : null,
            ];
            foreach ($auditFields as $field => $label) {
                $oldVal = $oldSnapshot[$field];
                $newVal = $order->$field;
                // Normalise for comparison
                if ((string) $oldVal !== (string) $newVal) {
                    // Resolve FK values to readable names
                    if (isset($fkResolvers[$field])) {
                        $oldVal = $fkResolvers[$field]($oldVal);
                        $newVal = $fkResolvers[$field]($newVal);
                    }
                    $oldValues[$label] = $oldVal;
                    $newValues[$label] = $newVal;
                }
            }
            $order->save();

            // --- Melee Stock Change Detection ---
            $oldMeleeId = $oldSnapshot['melee_diamond_id'] ?? null;
            $oldMeleePieces = $oldSnapshot['melee_pieces'] ?? 0;
            $oldMeleeCarat = $oldSnapshot['melee_carat'] ?? 0;
            $newMeleeId = $order->melee_diamond_id;
            $newMeleePieces = $order->melee_pieces ?? 0;
            $newMeleeCarat = $order->melee_carat ?? 0;

            $meleeChanged = ($oldMeleeId != $newMeleeId)
                || ($oldMeleePieces != $newMeleePieces)
                || ($oldMeleeCarat != $newMeleeCarat);

            if ($meleeChanged) {
                // Reverse old melee stock (if there was one)
                if (!empty($oldMeleeId) && $oldMeleePieces > 0) {
                    MeleeTransaction::create([
                        'melee_diamond_id' => $oldMeleeId,
                        'transaction_type' => 'in',
                        'pieces' => abs($oldMeleePieces),
                        'carat_weight' => abs($oldMeleeCarat),
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'created_by' => Auth::guard('admin')->id(),
                        'notes' => 'Stock reversed (order #' . $order->id . ' updated)',
                    ]);
                }

                // Deduct new melee stock (if new one selected)
                if (!empty($newMeleeId) && $newMeleePieces > 0) {
                    MeleeTransaction::create([
                        'melee_diamond_id' => $newMeleeId,
                        'transaction_type' => 'out',
                        'pieces' => abs($newMeleePieces),
                        'carat_weight' => abs($newMeleeCarat),
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'created_by' => Auth::guard('admin')->id(),
                        'notes' => 'Stock used in Order #' . $order->id,
                    ]);
                }
            }

            // Log audit after save succeeds
            if (!empty($oldValues) || !empty($newValues)) {
                AuditLogger::log('updated', $order, Auth::guard('admin')->id(), $oldValues, $newValues);
            }
            // If diamond SKU changed or was newly added, mark the new one as sold
            if (!empty($newDiamondSku) && $newDiamondSku !== $oldDiamondSku) {
                $diamondController = new DiamondController();
                // Get specific price for this diamond from diamond_prices (already in USD)
                $diamondPrices = $validated['diamond_prices'] ?? [];
                $soldPriceUsd = (float) ($diamondPrices[$newDiamondSku] ?? 0);
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

            // --- Notify Super Admins about the update ---
            if (!empty($newValues)) {
                $superAdmins = Admin::where('is_super', true)->get();
                /** @var Admin $updatedBy */
                $updatedBy = Auth::guard('admin')->user();
                if ($superAdmins->isNotEmpty()) {
                    Notification::send($superAdmins, new OrderUpdatedNotification($order, $updatedBy, $oldValues, $newValues));
                }
            }

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
        // Unless they have 'orders.view_team' permission which allows viewing team orders
        if (!$admin->is_super) {
            // Check if admin owns the order OR has view_team permission
            if ($order->submitted_by !== $admin->id && !$admin->hasPermission('orders.view_team')) {
                abort(403, 'You don\'t have permission to view orders submitted by other admins.');
            }

            // Check visibility restriction for shipped orders (10 days limit for normal admins)
            $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];
            if (in_array($order->diamond_status, $shippedStatuses)) {
                // Use startOfDay comparison to be safe with time parts
                if ($order->dispatch_date && \Illuminate\Support\Carbon::parse($order->dispatch_date)->lt(now()->subDays(10)->startOfDay())) {
                    abort(403, 'This shipped order is no longer visible (exceeded 10-day viewing window).');
                }
            }
        }

        // Eager load relationships
        $order->load(['goldDetail', 'ringSize', 'settingType', 'earringDetail', 'company', 'creator', 'lastModifier']);

        // Load edit history for superadmin only
        $editHistory = collect();
        $metalTypes = MetalType::all();
        $ringSizes = RingSize::all();
        $settingTypes = SettingType::all();
        $closureTypes = ClosureType::all();
        $companies = Company::all();

        // Get edit history using model relationship
        $editHistory = $order->editHistory()->with('admin')->get();

        return view('orders.show', compact(
            'order',
            'metalTypes',
            'ringSizes',
            'settingTypes',
            'closureTypes',
            'companies',
            'editHistory'
        ));
    }

    /**
     * Sync tracking data from carrier website
     */
    public function syncTracking(Order $order, ShippingTrackingService $trackingService)
    {
        try {
            $result = $trackingService->syncOrderTracking($order);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json($result);
            }

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->with('error', $result['message']);
        } catch (\Exception $e) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Controller Error: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get basic order info for quick overview.
     */
    public function quickView(Order $order)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->is_super) {
            if ($order->submitted_by !== $admin->id && !$admin->hasPermission('orders.view_team')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $order->load(['creator', 'company', 'meleeDiamond.category']);

        return response()->json([
            'id' => $order->id,
            'client_name' => $order->client_name ?? '-',
            'order_type' => $order->order_type,
            'jewellery_details' => $order->jewellery_details,
            'diamond_details' => $order->diamond_details,
            'diamond_sku' => $order->diamond_sku,
            'melee_details' => $order->melee_diamond_id ? [
                'name' => ($order->meleeDiamond->category->name ?? 'Melee') . ' — ' . str_replace('-', ' ', $order->meleeDiamond->size_label ?? 'N/A'),
                'pieces' => $order->melee_pieces,
                'carat' => $order->melee_carat,
                'value' => number_format((float) ($order->melee_total_value ?? 0), 2)
            ] : null,
            'gross_sell' => number_format((float) ($order->gross_sell ?? 0), 2),
            'status' => $order->diamond_status,
            'created_at' => $order->created_at->format('d M Y'),
            'submitted_by' => $order->creator->name ?? 'Unknown',
            'company' => $order->company->name ?? 'N/A',
            'url' => route('orders.show', $order->id)
        ]);
    }

    /**
     * Remove a single file from an order and Cloudinary.
     */
    public function removeFile(Request $request, Order $order)
    {
        $validated = $request->validate([
            'file_url' => 'required|string',
            'type' => 'required|in:image,pdf'
        ]);

        $fileUrl = $validated['file_url'];
        $type = $validated['type'];
        $field = ($type === 'image') ? 'images' : 'order_pdfs';

        // Retrieve current files and normalize
        $files = $order->$field;
        if (is_string($files)) {
            $files = json_decode($files, true) ?: [];
        }
        if (is_array($files) && count($files) === 1 && is_string($files[0])) {
            $decoded = json_decode($files[0], true);
            if (is_array($decoded))
                $files = $decoded;
        }
        $files = is_array($files) ? $files : [];

        $targetFile = null;
        $remainingFiles = [];

        foreach ($files as $file) {
            $url = is_array($file) ? ($file['url'] ?? '') : $file;
            if ($url === $fileUrl) {
                $targetFile = $file;
            } else {
                $remainingFiles[] = $file;
            }
        }

        if ($targetFile) {
            // Delete from Cloudinary if public_id exists
            if (is_array($targetFile) && isset($targetFile['public_id'])) {
                $resourceType = ($type === 'pdf') ? 'raw' : 'image';
                $this->deleteFromCloudinary($targetFile['public_id'], $resourceType);
            }

            // Update order record
            $order->$field = $remainingFiles;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'File removed successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File not found'
        ], 404);
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
            $imagesRaw = $order->images;
            $images = is_string($imagesRaw) ? json_decode($imagesRaw, true) : (is_array($imagesRaw) ? $imagesRaw : []);
            foreach ($images as $image) {
                if (isset($image['public_id'])) {
                    if ($this->deleteFromCloudinary($image['public_id'], 'image')) {
                        $deletedImages++;
                    }
                }
            }

            // Delete PDFs from Cloudinary
            $pdfsRaw = $order->order_pdfs;
            $pdfs = is_string($pdfsRaw) ? json_decode($pdfsRaw, true) : (is_array($pdfsRaw) ? $pdfsRaw : []);
            foreach ($pdfs as $pdf) {
                if (isset($pdf['public_id'])) {
                    if ($this->deleteFromCloudinary($pdf['public_id'], 'raw')) {
                        $deletedPdfs++;
                    }
                }
            }

            // --- Reverse Melee Stock on Delete ---
            if (!empty($order->melee_diamond_id) && ($order->melee_pieces ?? 0) > 0) {
                MeleeTransaction::create([
                    'melee_diamond_id' => $order->melee_diamond_id,
                    'transaction_type' => 'in',
                    'pieces' => abs($order->melee_pieces),
                    'carat_weight' => abs($order->melee_carat ?? 0),
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'created_by' => Auth::guard('admin')->id(),
                    'notes' => 'Stock returned (Order #' . $order->id . ' deleted)',
                ]);
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
     * Sync tracking for all orders.
     */
    public function syncAllTracking(ShippingTrackingService $trackingService)
    {
        set_time_limit(300); // Increase time limit to 5 minutes

        try {
            $orders = Order::whereNotNull('tracking_number')
                ->orWhereNotNull('tracking_url')
                ->get();

            $count = 0;
            $successValues = 0;
            $failures = 0;

            foreach ($orders as $order) {
                // Skip if no tracking info actually exists (double check)
                if (empty($order->tracking_number) && empty($order->tracking_url)) {
                    continue;
                }

                $result = $trackingService->syncOrderTracking($order);

                if ($result['success']) {
                    $successValues++;
                } else {
                    $failures++;
                }
                $count++;

                // Small delay to be polite to the API
                usleep(200000); // 0.2s
            }

            return redirect()->back()->with('success', "Sync completed. Processed: $count. Success: $successValues. Failed: $failures.");

        } catch (\Exception $e) {
            Log::error("Bulk Sync Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Bulk sync failed: ' . $e->getMessage());
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
            'client_tax_id_type' => 'nullable|in:tax_id,vat_id,ioss_no,uid_vat_no,other',
            'client_email' => 'required|email|max:191',
            'diamond_sku' => 'nullable|string|max:191',
            'diamond_skus' => 'nullable|array', // New: supports multiple diamond SKUs
            'diamond_skus.*' => 'nullable|string|max:191', // Each SKU in the array
            'diamond_status' => 'nullable|string|in:r_order_in_process,r_order_shipped,d_diamond_in_discuss,d_diamond_in_making,d_diamond_completed,d_diamond_in_certificate,d_order_shipped,j_diamond_in_progress,j_diamond_completed,j_diamond_in_discuss,j_cad_in_progress,j_cad_done,j_order_completed,j_order_in_qc,j_qc_done,j_order_shipped,j_order_hold',
            'company_id' => 'required|exists:companies,id',
            'gross_sell' => 'nullable|numeric|min:0',
            'dispatch_date' => 'nullable|date',
            'note' => 'nullable|in:priority,non_priority',
            'special_notes' => 'nullable|string|max:2000',
            'shipping_company_name' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'tracking_url' => 'nullable|url',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,avif,gif,webp|max:10240',
            'order_pdfs.*' => 'nullable|mimes:pdf|max:10240',
            'diamond_prices' => 'nullable|array', // Individual prices for each diamond SKU
            'diamond_prices.*' => 'nullable|numeric|min:0', // Each price must be numeric

            // Melee Fields
            'melee_diamond_id' => 'nullable|exists:melee_diamonds,id',
            'melee_pieces' => 'nullable|integer|min:1',
            'melee_carat' => 'nullable|numeric|min:0',
            'melee_price_per_ct' => 'nullable|numeric|min:0',
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
        // Required fields
        $order->order_type = $validated['order_type'];
        $order->company_id = $validated['company_id'];

        // String fields (empty string is acceptable for VARCHAR/TEXT)
        $order->client_name = $validated['client_name'] ?? '';
        $order->client_address = $validated['client_address'] ?? '';
        $order->client_mobile = $validated['client_mobile'] ?? '';
        $order->client_tax_id = $validated['client_tax_id'] ?? '';
        $order->client_tax_id_type = $validated['client_tax_id_type'] ?? null;
        $order->client_email = $validated['client_email'] ?? '';
        $order->jewellery_details = $validated['jewellery_details'] ?? '';
        $order->diamond_details = $validated['diamond_details'] ?? '';
        $order->diamond_sku = $validated['diamond_sku'] ?? '';

        // Handle multiple diamond SKUs - store as JSON array
        // Also keep single diamond_sku for backward compatibility
        if (!empty($validated['diamond_skus'])) {
            $skus = array_filter(array_unique($validated['diamond_skus']));
            $order->diamond_skus = $skus;
            // Set first SKU as primary for backward compatibility
            if (empty($order->diamond_sku) && !empty($skus)) {
                $order->diamond_sku = $skus[0];
            }
        } elseif (!empty($validated['diamond_sku'])) {
            // Single SKU provided - convert to array for new field
            $order->diamond_skus = [$validated['diamond_sku']];
        }

        // Store individual diamond prices if provided
        if (!empty($validated['diamond_prices'])) {
            $order->diamond_prices = $validated['diamond_prices'];
        }
        $order->product_other = $validated['product_other'] ?? '';
        $order->special_notes = $validated['special_notes'] ?? '';
        $order->shipping_company_name = $validated['shipping_company_name'] ?? '';
        $order->tracking_number = $validated['tracking_number'] ?? '';

        $trackingUrl = $validated['tracking_url'] ?? '';
        if (empty($trackingUrl) && !empty($order->tracking_number) && !empty($order->shipping_company_name)) {
            $trackingService = new ShippingTrackingService();
            $trackingUrl = $trackingService->generateTrackingUrl($order->shipping_company_name, $order->tracking_number);
        }
        $order->tracking_url = $trackingUrl;

        // Integer foreign key fields - use null instead of empty string for MySQL compatibility
        $order->gold_detail_id = !empty($validated['gold_detail_id']) ? $validated['gold_detail_id'] : null;
        $order->ring_size_id = !empty($validated['ring_size_id']) ? $validated['ring_size_id'] : null;
        $order->setting_type_id = !empty($validated['setting_type_id']) ? $validated['setting_type_id'] : null;
        $order->earring_type_id = !empty($validated['earring_type_id']) ? $validated['earring_type_id'] : null;

        // Melee Fields
        $order->melee_diamond_id = !empty($validated['melee_diamond_id']) ? $validated['melee_diamond_id'] : null;
        $order->melee_pieces = !empty($validated['melee_pieces']) ? $validated['melee_pieces'] : null;
        $order->melee_carat = !empty($validated['melee_carat']) ? $validated['melee_carat'] : null;
        $order->melee_price_per_ct = !empty($validated['melee_price_per_ct']) ? $validated['melee_price_per_ct'] : null;

        // Calculate Melee Total Value if context exists
        if ($order->melee_carat && $order->melee_price_per_ct) {
            $order->melee_total_value = $order->melee_carat * $order->melee_price_per_ct;
        } elseif ($order->melee_pieces && $order->melee_price_per_ct) {
            // Fallback if priced per piece (rare but possible logic) - usually per carat
            // For now assuming Price Per Ct as per schema.
            $order->melee_total_value = 0;
        }

        // ENUM fields - use null instead of empty string for MySQL compatibility
        // MySQL ENUM columns reject empty strings, must use null when no value selected
        $order->diamond_status = !empty($validated['diamond_status']) ? $validated['diamond_status'] : null;
        $order->note = !empty($validated['note']) ? $validated['note'] : null;

        // Numeric fields
        $order->gross_sell = $validated['gross_sell'] ?? 0;

        // Date fields - use null instead of empty string for MySQL DATE compatibility
        $order->dispatch_date = !empty($validated['dispatch_date']) ? $validated['dispatch_date'] : null;
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