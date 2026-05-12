<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Admin;
use App\Models\ClosureType;
use App\Models\Company;
use App\Models\Diamond;
use App\Models\MetalType;
use App\Models\Order;
use App\Models\Factory;
use App\Models\RingSize;
use App\Models\SettingType;
use App\Models\Client;
use App\Notifications\OrderUpdatedNotification;
use App\Notifications\OrderCancelledNotification;
use App\Services\AuditLogger;
use App\Services\StatusTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\CurrencyService;
use App\Services\MeleeStockService;
use App\Models\OrderDraft;
use App\Models\JewelleryStock;
use App\Notifications\DiamondSoldNotification;
use App\Models\MeleeDiamond;
use App\Models\Channel;
use App\Models\Message;
use App\Models\OrderPayment;
use App\Events\MessageSent;
use App\Services\ShippingTrackingService;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Notifications\OrderDiscussionNotification;
use App\Models\GoldDistribution;


class OrderController extends Controller
{
    private MeleeStockService $meleeStockService;
    private \App\Services\PaymentService $paymentService;
    private \App\Services\CloudinaryUploadService $uploadService;
    private \App\Services\OrderService $orderService;

    public function __construct(
        MeleeStockService $meleeStockService,
        \App\Services\PaymentService $paymentService,
        \App\Services\CloudinaryUploadService $uploadService,
        \App\Services\OrderService $orderService
    ) {
        $this->meleeStockService = $meleeStockService;
        $this->paymentService = $paymentService;
        $this->uploadService = $uploadService;
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders with filters.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        $baseQuery = Order::query()->with(['company', 'creator', 'factoryRelation']);

        if ($request->filled('search')) {
            $search = $request->search;
            $fields = ['client_name', 'id', 'client_email', 'client_address', 'jewellery_details', 'diamond_details'];

            $baseQuery->where(function ($query) use ($search, $fields) {
                foreach ($fields as $field) {
                    $query->orWhere($field, 'like', "%{$search}%");
                }

                $query->orWhereHas('company', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('factory_id')) {
            $baseQuery->where('factory_id', $request->factory_id);
        }

        if ($request->filled('company_id')) {
            $baseQuery->where('company_id', $request->company_id);
        }

        // Count shipped orders (before excluding from base)
        $shippedOrdersCount = (clone $baseQuery)
            ->whereIn('diamond_status', $shippedStatuses)
            ->count();

        // Count tracking statuses for the summary card using the same filtered base query.
        $trackingStatusRows = (clone $baseQuery)
            ->selectRaw('LOWER(tracking_status) as normalized_status, COUNT(*) as total')
            ->whereNotNull('tracking_status')
            ->groupBy(DB::raw('LOWER(tracking_status)'))
            ->pluck('total', 'normalized_status')
            ->toArray();

        $trackingStatusCounts = [
            'in_transit' => (int) ($trackingStatusRows['in transit'] ?? 0),
            'out_for_delivery' => (int) ($trackingStatusRows['out for delivery'] ?? 0),
            'delivered' => (int) ($trackingStatusRows['delivered'] ?? 0),
        ];

        // Count cancelled orders (before excluding)
        $cancelledOrdersCount = (clone $baseQuery)
            ->whereIn('diamond_status', $cancelledStatuses)
            ->count();

        // Count overdue orders (dispatch date in the past, not shipped, not cancelled)
        $overdueOrdersCount = (clone $baseQuery)
            ->whereDate('dispatch_date', '<', now()->startOfDay())
            ->where(function ($q) use ($shippedStatuses, $cancelledStatuses) {
                $q->whereNotIn('diamond_status', array_merge($shippedStatuses, $cancelledStatuses))
                    ->orWhereNull('diamond_status');
            })->count();

        // Count ship-today orders (actionable only)
        $shipTodayCount = (clone $baseQuery)
            ->whereDate('dispatch_date', now()->toDateString())
            ->where(function ($q) use ($shippedStatuses, $cancelledStatuses) {
                $q->whereNotIn('diamond_status', array_merge($shippedStatuses, $cancelledStatuses))
                    ->orWhereNull('diamond_status');
            })->count();

        // Count ship-tomorrow orders (actionable only)
        $shipTomorrowCount = (clone $baseQuery)
            ->whereDate('dispatch_date', now()->addDay()->toDateString())
            ->where(function ($q) use ($shippedStatuses, $cancelledStatuses) {
                $q->whereNotIn('diamond_status', array_merge($shippedStatuses, $cancelledStatuses))
                    ->orWhereNull('diamond_status');
            })->count();

        // Compute totals and breakdowns EXCLUDING shipped and cancelled orders
        $nonShippedQuery = (clone $baseQuery)->where(function ($q) use ($shippedStatuses, $cancelledStatuses) {
            $q->whereNotIn('diamond_status', array_merge($shippedStatuses, $cancelledStatuses))
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

        // Today's Sales Stats
        $todaysSalesQuery = Order::whereDate('created_at', now()->toDateString())
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereNotIn('diamond_status', $cancelledStatuses)->orWhereNull('diamond_status');
            });

        if ($request->filled('company_id')) {
            $todaysSalesQuery->where('company_id', $request->company_id);
        }

        $todaysSales = $todaysSalesQuery->sum('amount_received');
        $todaysOrderCount = $todaysSalesQuery->count();

        // Month Sales Stats
        $monthSalesQuery = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereNotIn('diamond_status', $cancelledStatuses)->orWhereNull('diamond_status');
            });

        if ($request->filled('company_id')) {
            $monthSalesQuery->where('company_id', $request->company_id);
        }

        $monthSales = $monthSalesQuery->sum('amount_received');

        // Get company sales progress for active companies
        $companySalesStats = Company::where('status', 'active')
            ->get()
            ->map(function ($company) {
                return Cache::remember("company.{$company->id}.stats.today", 300, function () use ($company) {
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
            });

        // Now apply optional filters for the listing
        $query = clone $baseQuery;

        $statusQuickViewActive = ($request->filled('shipped') && $request->shipped == '1')
            || ($request->filled('cancelled') && $request->cancelled == '1')
            || $request->filled('tracking_status');

        $sort = trim((string) $request->input('sort', ''));
        if ($sort === '') {
            if ($request->boolean('ship_today')) {
                $sort = 'ship_today';
            } elseif ($request->boolean('ship_tomorrow')) {
                $sort = 'ship_tomorrow';
            }
        }

        $allowedSorts = ['newest_created', 'oldest_created', 'oldest_due', 'newest_due', 'id_asc', 'id_desc', 'ship_today', 'ship_tomorrow'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : null;
        $shipDaySortActive = in_array($sort, ['ship_today', 'ship_tomorrow'], true);

        // If shipped filter is applied, show only shipped orders
        if ($request->filled('shipped') && $request->shipped == '1') {
            $query->whereIn('diamond_status', $shippedStatuses);
        } elseif ($request->filled('cancelled') && $request->cancelled == '1') {
            $query->whereIn('diamond_status', $cancelledStatuses);
        } elseif (!$request->filled('tracking_status')) {
            // Otherwise, hide shipped and cancelled orders from main listing
            $query->where(function ($q) use ($shippedStatuses, $cancelledStatuses) {
                $q->whereNotIn('diamond_status', array_merge($shippedStatuses, $cancelledStatuses))
                    ->orWhereNull('diamond_status');
            });
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('diamond_status')) {
            $query->where('diamond_status', $request->diamond_status);
        }

        if ($request->filled('tracking_status')) {
            $trackingStatusMap = [
                'in_transit' => 'in transit',
                'out_for_delivery' => 'out for delivery',
                'delivered' => 'delivered',
            ];
            $dbStatus = $trackingStatusMap[$request->tracking_status] ?? $request->tracking_status;
            $query->whereRaw('LOWER(tracking_status) = ?', [strtolower($dbStatus)]);
        }

        if ($request->filled('priority_status')) {
            $query->where('note', $request->priority_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Overdue filter
        if ($request->filled('overdue') && $request->overdue == '1' && !$shipDaySortActive) {
            $query->whereDate('dispatch_date', '<', now()->startOfDay())
                ->where(function ($q) use ($shippedStatuses, $cancelledStatuses) {
                    $q->whereNotIn('diamond_status', array_merge($shippedStatuses, $cancelledStatuses))
                        ->orWhereNull('diamond_status');
                });
        }

        // Ship-day quick filters are now selected from the sort dropdown
        if (!$statusQuickViewActive && $sort === 'ship_today') {
            $query->whereDate('dispatch_date', now()->toDateString())
                ->where(function ($q) use ($shippedStatuses, $cancelledStatuses) {
                    $q->whereNotIn('diamond_status', array_merge($shippedStatuses, $cancelledStatuses))
                        ->orWhereNull('diamond_status');
                });
        } elseif (!$statusQuickViewActive && $sort === 'ship_tomorrow') {
            $query->whereDate('dispatch_date', now()->addDay()->toDateString())
                ->where(function ($q) use ($shippedStatuses, $cancelledStatuses) {
                    $q->whereNotIn('diamond_status', array_merge($shippedStatuses, $cancelledStatuses))
                        ->orWhereNull('diamond_status');
                });
        }

        // TEMP: Melee diamond-only listing filter (remove after temporary production use)
        if ($request->boolean('melee_diamond_temp')) {
            $textPatterns = ['%melee diamond%', '%melee-diamond%', '%melee%diamond%'];

            $query->where(function ($meleeQuery) use ($textPatterns) {
                $meleeQuery
                    ->whereNotNull('melee_diamond_id')
                    ->orWhere(function ($entriesQuery) {
                        $entriesQuery->whereNotNull('melee_entries')
                            ->where('melee_entries', '!=', '[]')
                            ->where('melee_entries', '!=', '{}');
                    })
                    ->orWhere(function ($textQuery) use ($textPatterns) {
                        foreach (['special_notes', 'jewellery_details', 'diamond_details'] as $field) {
                            foreach ($textPatterns as $pattern) {
                                $textQuery->orWhereRaw("LOWER(COALESCE($field, '')) LIKE ?", [$pattern]);
                            }
                        }
                    });
            });
        }

        if ($sort === 'ship_today' || $sort === 'ship_tomorrow') {
            // Keep ship-day views focused on latest orders within that day
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        } elseif ($sort === 'newest_created') {
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        } elseif ($sort === 'oldest_created') {
            $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
        } elseif ($sort === 'oldest_due') {
            $query->orderByRaw('CASE WHEN dispatch_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('dispatch_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc');
        } elseif ($sort === 'newest_due') {
            $query->orderByRaw('CASE WHEN dispatch_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('dispatch_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc');
        } elseif ($sort === 'id_asc') {
            $query->orderBy('id', 'asc');
        } elseif ($sort === 'id_desc') {
            $query->orderBy('id', 'desc');
        } elseif ($request->filled('overdue') && $request->overdue == '1') {
            // Preserve current default behavior for overdue view
            $query->orderBy('dispatch_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc');
        } else {
            // Preserve current default behavior for non-overdue views
            $query->latest();
        }

        $orders = $query->paginate(20);
        $factories = Factory::orderBy('name')->get();
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        $statusTransitionService = app(StatusTransitionService::class);
        $statusFlowOptions = [
            'ready_to_ship' => array_filter($statusTransitionService->getValidStatuses('ready_to_ship'), fn($s) => !str_ends_with($s, '_cancelled')),
            'custom_diamond' => array_filter($statusTransitionService->getValidStatuses('custom_diamond'), fn($s) => !str_ends_with($s, '_cancelled')),
            'custom_jewellery' => array_filter($statusTransitionService->getValidStatuses('custom_jewellery'), fn($s) => !str_ends_with($s, '_cancelled')),
        ];

        // Mark all unread order-created notifications as read for this admin
        $admin->unreadNotifications()
            ->where('type', 'App\Notifications\OrderCreatedNotification')
            ->update(['read_at' => now()]);

        return view('orders.index', compact(
            'orders',
            'totalOrders',
            'orderTypeCounts',
            'statusCounts',
            'shippedOrdersCount',
            'cancelledOrdersCount',
            'overdueOrdersCount',
            'shipTodayCount',
            'shipTomorrowCount',
            'trackingStatusCounts',
            'todaysSales',
            'monthSales',
            'todaysOrderCount',
            'companySalesStats',
            'factories',
            'companies',
            'statusFlowOptions'
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

    /**
     * ⚡ OPTIMIZED: Store method with performance improvements
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $validated = $request->validated();
            $allowNegativeMelee = (bool) ($validated['allow_negative_melee'] ?? false);
            $paymentSummary = $this->paymentService->normalizePaymentSummary(
                $validated,
                (float) ($validated['gross_sell'] ?? 0)
            );

            // ✅ CRITICAL: Extract and validate melee entries BEFORE creating order
            $incomingMeleeEntries = $this->orderService->extractValidatedMeleeEntries($validated);
            $meleeEntriesForStock = $incomingMeleeEntries;
            $validationResult = $this->meleeStockService->validateAvailability($incomingMeleeEntries, [
                'allow_negative' => $allowNegativeMelee,
            ]);
            if (!$validationResult['valid']) {
                return $this->orderErrorResponse($request, $validationResult['message']);
            }

            // ✅ CRITICAL: Extract and validate ALL diamond SKUs BEFORE creating order
            $allSkus = $this->orderService->extractValidatedSkus($validated);
            $validatedDiamonds = [];

            foreach ($allSkus as $sku) {
                $skuCheck = $this->orderService->checkOrderSkuAvailability($sku);
                if (!$skuCheck['available']) {
                    return $this->orderErrorResponse($request, $skuCheck['message']);
                }

                if ($skuCheck['type'] === 'diamond' && isset($skuCheck['item'])) {
                    $validatedDiamonds[] = $skuCheck['item'];
                }
            }

            // ⚡ PERFORMANCE: Upload files to Cloudinary BEFORE starting DB transaction
            // This prevents file uploads from blocking database operations
            $images = [];
            $pdfs = [];

            try {
                $images = $this->uploadService->uploadFromRequest($request, 'images', 'orders/images', 10);
                $pdfs = $this->uploadService->uploadFromRequest($request, 'order_pdfs', 'orders/pdfs', 5, true);
            } catch (\Exception $e) {
                Log::error('File upload failed before order creation', [
                    'error' => $e->getMessage()
                ]);
                // Continue without files - they can be added later via edit
            }

            // Start DB transaction AFTER file uploads
            DB::beginTransaction();

            // Create the order
            $order = new Order();
            $this->orderService->assignOrderFields($order, $validated);
            $order->submitted_by = Auth::guard('admin')->id();

            // Auto-create or reuse client
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
                $order->client_id = $client->id;
            }

            // Attach uploaded files
            $order->images = $images;
            $order->order_pdfs = $pdfs;

            // Handle payment logic
            $order->syncPaymentSummary(
                $paymentSummary['amount_received'],
                $paymentSummary['payment_status'],
                $paymentSummary['amount_due']
            );

            $order->save();

            // Record initial payment entry
            if ((float) $paymentSummary['amount_received'] > 0) {
                OrderPayment::create([
                    'order_id' => $order->id,
                    'amount' => $paymentSummary['amount_received'],
                    'payment_method' => null, // Default
                    'reference_number' => null,
                    'notes' => 'Initial payment recorded on creation.',
                    'received_at' => now(),
                    'recorded_by' => Auth::guard('admin')->id(),
                ]);
            }

            $order->refreshPaymentSummaryFromPayments();

            // ⚡ PERFORMANCE: Bulk update diamonds as sold (instead of loop + controller calls)
            if (!empty($validatedDiamonds)) {
                $diamondPrices = $validated['diamond_prices'] ?? [];
                $diamondIds = [];
                $soldDiamonds = [];

                foreach ($validatedDiamonds as $diamond) {
                    $diamondIds[] = $diamond->id;
                    $soldPriceUsd = (float) ($diamondPrices[$diamond->sku] ?? 0);
                    $soldDiamonds[] = [
                        'id' => $diamond->id,
                        'sku' => $diamond->sku,
                        'sold_price' => $soldPriceUsd
                    ];
                }

                // Bulk update instead of individual queries
                foreach ($soldDiamonds as $soldData) {
                    Diamond::where('id', $soldData['id'])->update([
                        'is_sold_out' => 'Sold',
                        'sold_out_date' => now(),
                        'sold_out_price' => $soldData['sold_price'],
                        'updated_at' => now()
                    ]);
                }

                Log::info('Diamonds marked as sold (bulk)', [
                    'count' => count($soldDiamonds),
                    'order_id' => $order->id
                ]);

                // ⚡ PERFORMANCE: Queue diamond sale notifications instead of sending synchronously
                // This prevents blocking the order creation
                dispatch(function () use ($soldDiamonds) {
                    $currentAdmin = Auth::guard('admin')->user();
                    $allAdmins = Admin::where('id', '!=', $currentAdmin->id)->get();

                    if ($allAdmins->isNotEmpty()) {
                        foreach ($soldDiamonds as $soldData) {
                            $diamond = Diamond::find($soldData['id']);
                            if ($diamond) {
                                Notification::send($allAdmins, new DiamondSoldNotification($diamond, $currentAdmin));
                            }
                        }
                    }
                })->afterResponse();
            }

            // ⚡ PERFORMANCE: Use MeleeStockService for atomic stock deduction
            if (!empty($meleeEntriesForStock)) {
                $stockResult = $this->meleeStockService->deductForOrder($order->id, $meleeEntriesForStock, [
                    'allow_negative' => $allowNegativeMelee,
                ]);
                if (!$stockResult['success']) {
                    DB::rollBack();
                    return $this->orderErrorResponse(
                        $request,
                        $stockResult['message'],
                        null,
                        $stockResult['status'] ?? 422
                    );
                }
            }

            // ⚡ GOLD TRACING: Auto-log consumption if gold weight is provided on create
            $newWeight = (float) $order->gold_net_weight;
            if ($newWeight > 0 && $order->factory_id) {
                // ✅ STOCK GUARD: Prevent consuming more gold than factory has
                $factory = Factory::find($order->factory_id);
                if ($factory) {
                    $factoryStock = $factory->current_stock;
                    if ($newWeight > $factoryStock) {
                        DB::rollBack();
                        $errorMsg = "Gold weight ({$newWeight}g) exceeds factory stock ({$factoryStock}g) for {$factory->name}. Please reduce the weight or distribute more gold to the factory first.";
                        return $this->orderErrorResponse($request, $errorMsg);
                    }
                }

                GoldDistribution::create([
                    'distribution_date' => now()->toDateString(),
                    'factory_id' => $order->factory_id,
                    'order_id' => $order->id,
                    'weight_grams' => $newWeight,
                    'type' => GoldDistribution::TYPE_CONSUMED,
                    'purpose' => 'System Auto Consumption for Order #' . $order->id,
                    'notes' => 'Initial weight recorded during order creation.',
                    'admin_id' => Auth::guard('admin')->id(),
                ]);
            }

            DB::commit();

            $meleeStockSummary = $this->meleeStockService->getStockSummary(
                array_column($meleeEntriesForStock, 'melee_diamond_id')
            );

            // ⚡ PERFORMANCE: Queue order creation notification instead of sending synchronously
            dispatch(function () use ($order) {
                $createdBy = Admin::find($order->submitted_by);
                if (!$createdBy)
                    return;

                // Send to all admins who have access to orders (excluding the creator)
                $eligibleAdmins = Admin::where('id', '!=', $createdBy->id)
                    ->where(function ($q) {
                        $q->where('is_super', true)
                            ->orWhereHas('permissions', function ($pq) {
                                $pq->whereIn('slug', ['orders.view', 'orders.view_team']);
                            });
                    })->get();

                if ($eligibleAdmins->isNotEmpty()) {
                    Notification::send($eligibleAdmins, new OrderCreatedNotification($order, $createdBy));
                }
            })->afterResponse();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_type' => $order->order_type,
                'images_count' => count($images),
                'pdfs_count' => count($pdfs),
                'diamond_sku' => $validated['diamond_sku'] ?? null,
                'melee_stock_id' => $order->melee_diamond_id,
                'payment_status' => $order->payment_status,
                'amount_received' => $order->amount_received,
                'amount_due' => $order->amount_due,
                'created_by' => Auth::guard('admin')->id()
            ]);

            $successMsg = 'Order created successfully! ' . count($images) . ' images and ' . count($pdfs) . ' PDFs uploaded.';

            // Order created successfully - clear any auto-save drafts
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
                    'order_id' => $order->id,
                    'payment_summary' => $order->payment_summary,
                    'melee_stock_summary' => $meleeStockSummary,
                ]);
            }

            return redirect()->route('orders.index')->with('success', $successMsg);

        } catch (ValidationException $e) {
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

    public function storePayment(Request $request, Order $order)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->is_super && !$admin->hasPermission('orders.edit')) {
            abort(403, 'You do not have permission to record payments for this order.');
        }

        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        if (in_array($order->diamond_status, $cancelledStatuses, true)) {
            return back()->with('error', 'Cancelled orders cannot receive payments.');
        }

        $paymentSummary = $order->payment_summary;
        if (($paymentSummary['payment_status'] ?? null) === 'full') {
            return back()->with('error', 'This order is already fully paid. Additional payments are not allowed.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:191',
            'notes' => 'nullable|string|max:2000',
            'received_at' => 'nullable|date',
        ]);

        $grossSell = (float) ($order->gross_sell ?? 0);
        $existingReceived = (float) $order->payments()->sum('amount');
        $remainingBalance = round(max($grossSell - $existingReceived, 0), 2);
        $paymentAmount = round((float) $validated['amount'], 2);

        if ($remainingBalance <= 0) {
            return back()->with('error', 'No remaining balance found for this order.');
        }

        if ($paymentAmount > $remainingBalance) {
            return back()
                ->withInput()
                ->with('error', 'Payment amount exceeds the remaining balance of ' . number_format($remainingBalance, 2) . '.');
        }

        $statusLabel = static function (?string $status): string {
            return match ($status) {
                'full' => 'Full Paid',
                'partial' => 'Partial Paid',
                'due' => 'Due',
                default => ucfirst(str_replace('_', ' ', (string) $status)),
            };
        };

        $formatAmount = static fn($value): string => number_format((float) $value, 2, '.', '');

        $oldSummary = [
            'payment_status' => (string) ($paymentSummary['payment_status'] ?? 'due'),
            'amount_received' => round((float) ($paymentSummary['amount_received'] ?? 0), 2),
            'amount_due' => round((float) ($paymentSummary['amount_due'] ?? 0), 2),
        ];

        $recordedPayment = null;
        $newSummary = $oldSummary;

        DB::transaction(function () use ($order, $validated, $paymentAmount, $admin, &$recordedPayment, &$newSummary) {
            $recordedPayment = OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $paymentAmount,
                'payment_method' => $validated['payment_method'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_at' => !empty($validated['received_at']) ? $validated['received_at'] : now(),
                'recorded_by' => $admin->id,
            ]);

            $newSummary = $order->refreshPaymentSummaryFromPayments();
        });

        $oldValues = [];
        $newValues = [];

        if ($oldSummary['payment_status'] !== (string) ($newSummary['payment_status'] ?? '')) {
            $oldValues['Payment Status'] = $statusLabel($oldSummary['payment_status']);
            $newValues['Payment Status'] = $statusLabel((string) ($newSummary['payment_status'] ?? ''));
        }

        if ((float) $oldSummary['amount_received'] !== (float) ($newSummary['amount_received'] ?? 0)) {
            $oldValues['Amount Received'] = $formatAmount($oldSummary['amount_received']);
            $newValues['Amount Received'] = $formatAmount($newSummary['amount_received'] ?? 0);
        }

        if ((float) $oldSummary['amount_due'] !== (float) ($newSummary['amount_due'] ?? 0)) {
            $oldValues['Amount Due'] = $formatAmount($oldSummary['amount_due']);
            $newValues['Amount Due'] = $formatAmount($newSummary['amount_due'] ?? 0);
        }

        if ($recordedPayment) {
            $paymentMethod = $recordedPayment->payment_method
                ? ucfirst(str_replace('_', ' ', $recordedPayment->payment_method))
                : 'N/A';
            $entrySummary = '$ ' . $formatAmount($recordedPayment->amount) . ' via ' . $paymentMethod;

            $oldValues['Payment Entry'] = 'N/A';
            $newValues['Payment Entry'] = $entrySummary;

            if (!empty($recordedPayment->reference_number)) {
                $oldValues['Reference Number'] = 'N/A';
                $newValues['Reference Number'] = (string) $recordedPayment->reference_number;
            }

            if (!empty($recordedPayment->notes)) {
                $oldValues['Payment Notes'] = 'N/A';
                $newValues['Payment Notes'] = (string) $recordedPayment->notes;
            }
        }

        if (!empty($newValues)) {
            AuditLogger::log('updated', $order, $admin->id, $oldValues, $newValues);
        }

        return redirect()->route('orders.show', $order->id)->with('success', 'Payment recorded successfully.');
    }

    /**
     * Save order data as draft when an error occurs.
     */
    /**
     * Unified error responder for store/update operations.
     */

    private function safeHttpStatus(mixed $status, int $default = 422): int
    {
        $allowed = [400, 401, 403, 404, 409, 422, 429, 500, 503];
        return in_array((int) $status, $allowed, true) ? (int) $status : $default;
    }

    private function applyMeleeStockDiff(
        Request $request,
        Order $order,
        array $oldMeleeEntries,
        array $newMeleeEntries,
        bool $allowNegativeMelee
    ): ?JsonResponse {
        // [FIX-10] Use net-quantity-based diffing instead of entry-by-entry matching.
        // The old approach returned ALL old entries and re-deducted ALL new entries,
        // causing duplicate transaction records for unchanged entries.
        try {
            $diffResult = $this->meleeStockService->adjustForOrderDiff(
                $order->id,
                $oldMeleeEntries,
                $newMeleeEntries,
                ['allow_negative' => $allowNegativeMelee]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->orderErrorResponse($request, 'Exception applying stock diff: ' . $e->getMessage(), null, 500);
        }

        if (!$diffResult['success']) {
            DB::rollBack();
            return $this->orderErrorResponse(
                $request,
                'Failed to apply stock diff: ' . $diffResult['message'],
                null,
                $this->safeHttpStatus($diffResult['status'] ?? 422)
            );
        }

        return null; // Success — no error response
    }


    private function orderErrorResponse(Request $request, string $message, ?OrderDraft $draft = null, int $status = 422)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'draft_id' => $draft?->id
            ], $status);
        }

        if ($draft) {
            return redirect()->route('orders.drafts.resume', $draft->id)
                ->with('error', $message);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }
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
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];

        // ── Cancelled orders: non-super admins can only update notes ──────────
        if (in_array($order->diamond_status, $cancelledStatuses) && !Auth::guard('admin')->user()->is_super) {
            $validated = $request->validated();
            $order->update(['special_notes' => $validated['special_notes'] ?? null]);
            $message = 'Order note updated successfully (Order is cancelled).';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('orders.show', $order->id),
                    'order_id' => $order->id,
                    'melee_stock_summary' => [],
                ]);
            }

            return redirect()->route('orders.show', $order->id)->with('success', $message);
        }

        try {
            $validated = $request->validated();
            $allowNegativeMelee = (bool) ($validated['allow_negative_melee'] ?? false);

            // ── Melee stock availability validation ───────────────────────────
            $incomingMeleeEntries = $this->orderService->extractValidatedMeleeEntries($validated);
            $this->orderService->validateMeleeStockAvailability($incomingMeleeEntries, $order, $allowNegativeMelee);

            // ── Validate only NEWLY added diamond SKUs ────────────────────────
            $submittedSkus = $this->orderService->extractValidatedSkus($validated);
            $existingOrderSkus = $this->orderService->extractOrderSkus($order);
            $newlyAddedSkus = array_values(array_diff($submittedSkus, $existingOrderSkus));
            $newlyAddedDiamonds = [];

            foreach ($newlyAddedSkus as $sku) {
                $skuCheck = $this->orderService->checkOrderSkuAvailability($sku);
                if (!$skuCheck['available']) {
                    return $this->orderErrorResponse($request, $skuCheck['message']);
                }
                if (($skuCheck['type'] ?? null) === 'diamond' && isset($skuCheck['item'])) {
                    $newlyAddedDiamonds[] = $skuCheck['item'];
                }
            }

            // ── File uploads BEFORE transaction (performance) ─────────────────
            $newImages = $this->uploadService->uploadFromRequest($request, 'images', 'orders/images', 10);
            $newPdfs = $this->uploadService->uploadFromRequest($request, 'order_pdfs', 'orders/pdfs', 5, true);

            // ─────────────────────────────────────────────────────────────────
            DB::beginTransaction();
            // ─────────────────────────────────────────────────────────────────

            // ── Normalise existing files ──────────────────────────────────────
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

            $order->images = array_merge($existingImages, $newImages);
            $order->order_pdfs = array_merge($existingPdfs, $newPdfs);

            // Track for logging
            $oldDiamondSku = $order->diamond_sku;
            $newDiamondSku = $validated['diamond_sku'] ?? '';

            // ── Snapshot old values BEFORE mutation (for audit log) ───────────
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
                'payment_status' => 'Payment Status',
                'amount_received' => 'Amount Received',
                'amount_due' => 'Amount Due',
                'dispatch_date' => 'Dispatch Date',
                'company_id' => 'Company',
                'factory_id' => 'Factory',
                'gold_detail_id' => 'Metal Type',
                'ring_size_id' => 'Ring Size',
                'setting_type_id' => 'Setting Type',
                'earring_type_id' => 'Earring Type',
                'melee_diamond_id' => 'Melee Diamond',
                'melee_pieces' => 'Melee Pieces',
                'melee_carat' => 'Melee Carat',
                'melee_price_per_ct' => 'Melee Price/CT',
                'melee_entries' => 'Melee Entries',
                'gold_net_weight' => 'Gold Net Weight',
            ];

            // getOriginal() must be called BEFORE assignOrderFields() mutates the model
            $oldSnapshot = [];
            foreach (array_keys($auditFields) as $field) {
                $oldSnapshot[$field] = $order->getOriginal($field);
            }

            // ── Apply updated fields to model ─────────────────────────────────
            $this->orderService->assignOrderFields($order, $validated);
            $order->last_modified_by = Auth::guard('admin')->id();

            // ── Payment logic ─────────────────────────────────────────────────
            $paymentSummary = $this->paymentService->normalizePaymentSummary($validated, (float) ($order->gross_sell ?? 0));
            $existingRecordedTotal = (float) $order->payments()->sum('amount');
            $hasPayments = $order->payments()->exists();

            if ($hasPayments) {
                $desiredReceived = (float) $paymentSummary['amount_received'];
                $delta = round($desiredReceived - $existingRecordedTotal, 2);

                if ($delta < -0.01) {
                    throw ValidationException::withMessages([
                        'amount_received' => 'Amount received cannot be lower than the payments already recorded for this order.',
                    ]);
                }

                if ($delta > 0.01) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'amount' => $delta,
                        'payment_method' => null,
                        'reference_number' => null,
                        'notes' => 'Payment added during order edit.',
                        'received_at' => now(),
                        'recorded_by' => Auth::guard('admin')->id(),
                    ]);
                }

                $order->refreshPaymentSummaryFromPayments();
            } else {
                $order->syncPaymentSummary(
                    $paymentSummary['amount_received'],
                    $paymentSummary['payment_status'],
                    $paymentSummary['amount_due']
                );

                if ($paymentSummary['amount_received'] > 0) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'amount' => $paymentSummary['amount_received'],
                        'payment_method' => null,
                        'reference_number' => null,
                        'notes' => 'Initial payment recorded during order edit.',
                        'received_at' => now(),
                        'recorded_by' => Auth::guard('admin')->id(),
                    ]);

                    $order->refreshPaymentSummaryFromPayments();
                }
            }

            // ── Build audit diff ──────────────────────────────────────────────
            $oldValues = [];
            $newValues = [];

            // Pre-load FK names (prevents N+1 in loop below)
            $fkData = [
                'company_id' => Company::whereIn('id', array_unique(array_filter([$oldSnapshot['company_id'] ?? null, $order->company_id])))->pluck('name', 'id')->toArray(),
                'factory_id' => Factory::whereIn('id', array_unique(array_filter([$oldSnapshot['factory_id'] ?? null, $order->factory_id])))->pluck('name', 'id')->toArray(),
                'gold_detail_id' => MetalType::whereIn('id', array_unique(array_filter([$oldSnapshot['gold_detail_id'] ?? null, $order->gold_detail_id])))->pluck('name', 'id')->toArray(),
                'ring_size_id' => RingSize::whereIn('id', array_unique(array_filter([$oldSnapshot['ring_size_id'] ?? null, $order->ring_size_id])))->pluck('name', 'id')->toArray(),
                'setting_type_id' => SettingType::whereIn('id', array_unique(array_filter([$oldSnapshot['setting_type_id'] ?? null, $order->setting_type_id])))->pluck('name', 'id')->toArray(),
                'earring_type_id' => ClosureType::whereIn('id', array_unique(array_filter([$oldSnapshot['earring_type_id'] ?? null, $order->earring_type_id])))->pluck('name', 'id')->toArray(),
                'melee_diamond_id' => MeleeDiamond::with('category')
                    ->whereIn('id', array_unique(array_filter([$oldSnapshot['melee_diamond_id'] ?? null, $order->melee_diamond_id])))
                    ->get()
                    ->mapWithKeys(fn($item) => [
                        $item->id => ($item->category->name ?? 'Melee') . ' — ' . str_replace('-', ' ', $item->size_label ?? 'N/A')
                    ])->toArray(),
            ];

            $fkResolvers = [
                'company_id' => fn($id) => $id ? ($fkData['company_id'][$id] ?? "ID:$id") : null,
                'factory_id' => fn($id) => $id ? ($fkData['factory_id'][$id] ?? "ID:$id") : null,
                'gold_detail_id' => fn($id) => $id ? ($fkData['gold_detail_id'][$id] ?? "ID:$id") : null,
                'ring_size_id' => fn($id) => $id ? ($fkData['ring_size_id'][$id] ?? "ID:$id") : null,
                'setting_type_id' => fn($id) => $id ? ($fkData['setting_type_id'][$id] ?? "ID:$id") : null,
                'earring_type_id' => fn($id) => $id ? ($fkData['earring_type_id'][$id] ?? "ID:$id") : null,
                'melee_diamond_id' => fn($id) => $id ? ($fkData['melee_diamond_id'][$id] ?? "ID:$id") : null,
            ];

            foreach ($auditFields as $field => $label) {
                $oldVal = $oldSnapshot[$field];
                $newVal = $order->$field;
                $oldCmp = is_array($oldVal) ? json_encode($oldVal) : (string) $oldVal;
                $newCmp = is_array($newVal) ? json_encode($newVal) : (string) $newVal;

                if ($oldCmp !== $newCmp) {
                    if (isset($fkResolvers[$field])) {
                        $oldVal = $fkResolvers[$field]($oldVal);
                        $newVal = $fkResolvers[$field]($newVal);
                    }
                    $oldValues[$label] = $oldVal;
                    $newValues[$label] = $newVal;
                }
            }

            // ── Save order ────────────────────────────────────────────────────
            $order->save();

            // ─────────────────────────────────────────────────────────────────
            // [FIX-7 + FIX-8 + FIX-9]
            // Diamond status re-sync based on cancellation state change
            // ─────────────────────────────────────────────────────────────────
            $oldStatus = (string) ($oldSnapshot['diamond_status'] ?? '');
            $newStatus = (string) ($order->diamond_status ?? '');

            $wasAlreadyCancelled = in_array($oldStatus, $cancelledStatuses, true);
            $isNowCancelled = in_array($newStatus, $cancelledStatuses, true);

            // [FIX-7] Order was cancelled before, now it's active again (un-cancelled)
            $wasJustUncancelled = $wasAlreadyCancelled && !$isNowCancelled;

            // [FIX-8] Order was active before, now it's being cancelled via status field in update()
            $wasJustCancelled = !$wasAlreadyCancelled && $isNowCancelled;

            if ($wasJustUncancelled) {
                // Collect all diamond SKUs on this order
                $skusToSell = [];
                if (!empty($order->diamond_skus)) {
                    $skusToSell = $order->diamond_skus;
                } elseif (!empty($order->diamond_sku)) {
                    $skusToSell = [$order->diamond_sku];
                }

                foreach ($skusToSell as $sku) {
                    $diamond = Diamond::where('sku', $sku)->first();
                    if (!$diamond)
                        continue;

                    // [FIX-9] Race condition guard:
                    // Diamond might have been assigned to another order
                    // while this order was in cancelled state
                    $alreadySoldElsewhere = Order::where('diamond_sku', $sku)
                        ->where('id', '!=', $order->id)
                        ->whereNotIn('diamond_status', $cancelledStatuses)
                        ->exists();

                    if ($alreadySoldElsewhere) {
                        DB::rollBack();
                        return $this->orderErrorResponse(
                            $request,
                            "Cannot un-cancel: Diamond SKU [{$sku}] was assigned to another order while this order was cancelled. Please remove it and choose a different diamond.",
                            null,
                            409 // Conflict
                        );
                    }

                    // Safe to mark as sold again
                    $diamond->update([
                        'is_sold_out' => 'Sold',
                        'sold_out_date' => now(),
                        'sold_out_price' => $diamond->sold_out_price ?? 0,
                    ]);
                }
            }

            if ($wasJustCancelled) {
                // Status changed to cancelled directly via update() (not via cancel() route)
                // Free up the diamonds
                $skusToFree = [];
                if (!empty($order->diamond_skus)) {
                    $skusToFree = $order->diamond_skus;
                } elseif (!empty($order->diamond_sku)) {
                    $skusToFree = [$order->diamond_sku];
                }

                foreach ($skusToFree as $sku) {
                    $diamond = Diamond::where('sku', $sku)->first();
                    if ($diamond && $diamond->is_sold_out === 'Sold') {
                        $diamond->update([
                            'is_sold_out' => 'In Stock',
                            'sold_out_date' => null,
                            'sold_out_price' => null,
                        ]);
                    }
                }
            }
            // ── End Diamond Re-sync ───────────────────────────────────────────

            // ── Melee stock diff ──────────────────────────────────────────────
            $oldMeleeEntries = $this->orderService->extractSnapshotMeleeEntries($oldSnapshot);
            $newMeleeEntries = $this->orderService->normalizeStoredMeleeEntries(
                $order->melee_entries,
                $order->melee_diamond_id,
                $order->melee_pieces,
                $order->melee_carat,
                $order->melee_price_per_ct
            );

            $updatedMeleeDiamondIds = array_values(array_unique(array_merge(
                array_column($oldMeleeEntries, 'melee_diamond_id'),
                array_column($newMeleeEntries, 'melee_diamond_id')
            )));

            // [FIX-2] json_encode used as change FLAG only;
            // actual per-entry diff handled inside applyMeleeStockDiff()
            $meleeChanged = json_encode($oldMeleeEntries) !== json_encode($newMeleeEntries);

            if ($meleeChanged) {
                $stockError = $this->applyMeleeStockDiff(
                    $request,
                    $order,
                    $oldMeleeEntries,
                    $newMeleeEntries,
                    $allowNegativeMelee
                );

                if ($stockError !== null) {
                    return $stockError; // DB already rolled back inside helper
                }
            } else {
                $updatedMeleeDiamondIds = array_values(array_unique(array_column($newMeleeEntries, 'melee_diamond_id')));
            }

            // ── Audit log (after save succeeds) ───────────────────────────────
            if (!empty($oldValues) || !empty($newValues)) {
                AuditLogger::log('updated', $order, Auth::guard('admin')->id(), $oldValues, $newValues);
            }

            // ── Bulk update newly added diamonds ──────────────────────────────
            if (!empty($newlyAddedDiamonds)) {
                $diamondPrices = $validated['diamond_prices'] ?? [];
                foreach ($newlyAddedDiamonds as $diamond) {
                    $soldPriceUsd = (float) ($diamondPrices[$diamond->sku] ?? 0);
                    Diamond::where('id', $diamond->id)->update([
                        'is_sold_out' => 'Sold',
                        'sold_out_date' => now(),
                        'sold_out_price' => $soldPriceUsd,
                        'updated_at' => now(),
                    ]);
                }
            }

            // ── Gold tracing ──────────────────────────────────────────────────
            $oldWeight = (float) ($oldSnapshot['gold_net_weight'] ?? 0);
            $newWeight = (float) $order->gold_net_weight;

            if ($oldWeight !== $newWeight && $order->factory_id) {
                $difference = $newWeight - $oldWeight;

                if ($difference > 0) {
                    $factory = Factory::find($order->factory_id);
                    if ($factory) {
                        $factoryStock = $factory->current_stock;
                        if ($difference > $factoryStock) {
                            DB::rollBack();
                            $errorMsg = "Gold weight increase ({$difference}g) exceeds factory stock ({$factoryStock}g) for {$factory->name}. "
                                . "Available: {$factoryStock}g, max total weight: " . round($oldWeight + $factoryStock, 3) . "g.";
                            return $this->orderErrorResponse($request, $errorMsg);
                        }
                    }
                }

                if ($difference != 0) {
                    GoldDistribution::updateOrCreate(
                        ['order_id' => $order->id, 'type' => GoldDistribution::TYPE_CONSUMED],
                        [
                            'distribution_date' => now()->toDateString(),
                            'factory_id' => $order->factory_id,
                            'weight_grams' => $newWeight,
                            'purpose' => 'System Auto Consumption for Order #' . $order->id,
                            'notes' => 'Weight updated: ' . $oldWeight . 'g => ' . $newWeight . 'g',
                            'admin_id' => Auth::guard('admin')->id(),
                        ]
                    );
                }
            }

            // ─────────────────────────────────────────────────────────────────
            DB::commit();
            // ─────────────────────────────────────────────────────────────────

            $meleeStockSummary = $this->meleeStockService->getStockSummary($updatedMeleeDiamondIds ?? []);

            Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'new_images' => count($newImages),
                'new_pdfs' => count($newPdfs),
                'old_diamond_sku' => $oldDiamondSku,
                'new_diamond_sku' => $newDiamondSku,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'was_just_uncancelled' => $wasJustUncancelled,
                'was_just_cancelled' => $wasJustCancelled,
                'melee_diff_applied' => $meleeChanged,
                'updated_by' => Auth::guard('admin')->id(),
            ]);

            // ── Queue update notifications ─────────────────────────────────────
            if (!empty($newValues)) {
                dispatch(function () use ($order, $oldValues, $newValues) {
                    $updatedBy = Admin::find($order->last_modified_by);
                    if (!$updatedBy)
                        return;

                    $adminsToNotify = Admin::where('id', '!=', $updatedBy->id)
                        ->where(function ($q) {
                            $q->where('is_super', true)
                                ->orWhereHas('permissions', function ($pq) {
                                    $pq->whereIn('slug', ['orders.view', 'orders.view_team']);
                                });
                        })->get();

                    if ($adminsToNotify->isNotEmpty()) {
                        Notification::send($adminsToNotify, new OrderUpdatedNotification($order, $updatedBy, $oldValues, $newValues));
                    }
                })->afterResponse();
            }

            $successMessage = 'Order updated successfully! Added ' . count($newImages) . ' new images and ' . count($newPdfs) . ' new PDFs.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'redirect' => route('orders.index'),
                    'order_id' => $order->id,
                    'payment_summary' => $order->payment_summary,
                    'melee_stock_summary' => $meleeStockSummary,
                ]);
            }

            return redirect()->route('orders.index')->with('success', $successMessage);

        } catch (ValidationException $e) {
            if (DB::transactionLevel() > 0)
                DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0)
                DB::rollBack();
            Log::error('Order update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => Auth::guard('admin')->id(),
            ]);
            return $this->orderErrorResponse($request, 'Failed to update order: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Show the Order details.
     */
    public function show(Order $order)
    {
        $admin = Auth::guard('admin')->user();

        $this->enforceOrderViewAccess($order, $admin);

        $order->load(['goldDetail', 'ringSize', 'settingType', 'earringDetail', 'company', 'creator', 'lastModifier', 'payments.recordedBy']);

        $editHistory = collect();
        $metalTypes = Cache::remember('metal_types', 3600, fn() => MetalType::all());
        $ringSizes = Cache::remember('ring_sizes', 3600, fn() => RingSize::all());
        $settingTypes = Cache::remember('setting_types', 3600, fn() => SettingType::all());
        $closureTypes = Cache::remember('closure_types', 3600, fn() => ClosureType::all());
        $companies = Cache::remember('companies', 1800, fn() => Company::all());

        $editHistory = $order->editHistory()->with('admin')->get();
        $discussionChannel = $this->getOrCreateOrderDiscussionChannel();
        $discussionRootMessage = $this->getOrderDiscussionRootMessage($order, $discussionChannel);
        $canPostDiscussion = $admin->is_super || $admin->hasPermission('orders.edit');
        $canManagePayments = $admin->is_super || $admin->hasPermission('orders.edit');
        $discussionMessages = collect();

        if ($discussionRootMessage) {
            $discussionRootMessage->load([
                'sender:id,name',
                'replies' => fn($q) => $q->with('sender:id,name')->latest()->limit(50),
            ]);

            $discussionMessages->push($discussionRootMessage);
        }

        // Mark unread order notifications for this specific order as read
        $admin->unreadNotifications()
            ->where(function ($q) use ($order) {
                $q->where(function ($sub) use ($order) {
                    $sub->where('type', 'App\Notifications\OrderCreatedNotification')
                        ->where('data->order_id', $order->id);
                })->orWhere(function ($sub) use ($order) {
                    $sub->where('type', 'App\Notifications\OrderDiscussionNotification')
                        ->where('data->order_id', $order->id);
                });
            })
            ->update(['read_at' => now()]);

        return view('orders.show', compact(
            'order',
            'metalTypes',
            'ringSizes',
            'settingTypes',
            'closureTypes',
            'companies',
            'editHistory',
            'discussionChannel',
            'discussionMessages',
            'canPostDiscussion',
            'discussionRootMessage',
            'canManagePayments'
        ));
    }

    /**
     * Post a new order discussion parent message.
     */
    public function postDiscussionMessage(Request $request, Order $order)
    {
        $admin = Auth::guard('admin')->user();
        $this->enforceOrderViewAccess($order, $admin);

        if (!$admin->is_super && !$admin->hasPermission('orders.edit')) {
            abort(403, 'You do not have permission to post order discussion updates.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $channel = $this->getOrCreateOrderDiscussionChannel();
        $parentMessage = $this->getOrCreateOrderDiscussionRootMessage($order, $channel, $admin);

        $reply = Message::create([
            'channel_id' => $channel->id,
            'sender_id' => $admin->id,
            'reply_to_id' => $parentMessage->id,
            'type' => 'text',
            'body' => trim($validated['body']),
            'metadata' => [
                'order_id' => $order->id,
                'is_order_thread_reply' => true,
            ],
        ]);

        $parentMessage->increment('thread_count');
        $reply->markAsRead($admin);
        broadcast(new MessageSent($reply->load('sender')))->toOthers();

        // Notify eligible admins about the new discussion message
        $this->notifyOrderDiscussion($order, $admin, $validated['body']);

        return redirect()
            ->to(route('orders.show', $order->id) . '#discussion-message-' . $parentMessage->id)
            ->with('success', 'Order discussion update posted.');
    }

    /**
     * Post a threaded reply for an existing order discussion message.
     */
    public function postDiscussionReply(Request $request, Order $order, Message $message)
    {
        $admin = Auth::guard('admin')->user();
        $this->enforceOrderViewAccess($order, $admin);

        if (!$admin->is_super && !$admin->hasPermission('orders.edit')) {
            abort(403, 'You do not have permission to post order discussion replies.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $channel = $this->getOrCreateOrderDiscussionChannel();
        $rootMessage = $this->getOrCreateOrderDiscussionRootMessage($order, $channel, $admin);

        if ($rootMessage->id !== ($message->reply_to_id ? $message->reply_to_id : $message->id)) {
            abort(404, 'Discussion thread not found for this order.');
        }

        $reply = Message::create([
            'channel_id' => $channel->id,
            'sender_id' => $admin->id,
            'reply_to_id' => $rootMessage->id,
            'type' => 'text',
            'body' => trim($validated['body']),
            'metadata' => [
                'order_id' => $order->id,
                'is_order_thread_reply' => true,
            ],
        ]);

        $rootMessage->increment('thread_count');
        $reply->markAsRead($admin);
        broadcast(new MessageSent($reply->load('sender')))->toOthers();

        // Notify eligible admins about the new discussion reply
        $this->notifyOrderDiscussion($order, $admin, $validated['body']);

        return redirect()
            ->to(route('orders.show', $order->id) . '#discussion-message-' . $rootMessage->id)
            ->with('success', 'Thread reply posted.');
    }

    private function enforceOrderViewAccess(Order $order, Admin $admin): void
    {
        if (!$admin->is_super) {
            if ($order->submitted_by !== $admin->id && !$admin->hasPermission('orders.view_team')) {
                abort(403, 'You don\'t have permission to view orders submitted by other admins.');
            }

            $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];
            if (
                in_array($order->diamond_status, $shippedStatuses, true)
                && $order->dispatch_date
                && \Illuminate\Support\Carbon::parse($order->dispatch_date)->lt(now()->subDays(10)->startOfDay())
            ) {
                abort(403, 'This shipped order is no longer visible (exceeded 10-day viewing window).');
            }
        }
    }

    private function getOrCreateOrderDiscussionChannel(): Channel
    {
        $existing = Channel::query()
            ->where('type', 'group')
            ->where('settings->kind', 'order_discussion_global')
            ->first();

        if ($existing) {
            $this->syncOrderDiscussionMembers($existing);
            return $existing;
        }

        // Enforce policy: channel owner/creator should be a super admin.
        // If request comes from non-super admin, we still create using a super admin identity.
        $creator = Admin::query()->where('is_super', true)->orderBy('id')->first();
        if (!$creator) {
            $creator = Auth::guard('admin')->user();
        }

        if (!$creator) {
            abort(500, 'Unable to initialize Order Discussion channel owner.');
        }
        $channel = Channel::create([
            'name' => 'Order Discussion',
            'type' => 'group',
            'description' => 'Global internal order discussion channel (thread per order)',
            'settings' => [
                'kind' => 'order_discussion_global',
            ],
            'created_by' => $creator->id,
        ]);

        $this->syncOrderDiscussionMembers($channel);
        return $channel;
    }

    private function getOrderDiscussionRootMessage(Order $order, Channel $channel): ?Message
    {
        return Message::query()
            ->where('channel_id', $channel->id)
            ->whereNull('reply_to_id')
            ->where('metadata->kind', 'order_root')
            ->where('metadata->order_id', $order->id)
            ->first();
    }

    private function getOrCreateOrderDiscussionRootMessage(Order $order, Channel $channel, Admin $actor): Message
    {
        $existing = $this->getOrderDiscussionRootMessage($order, $channel);

        if ($existing) {
            return $existing;
        }

        $clientName = (string) ($order->display_client_name ?? $order->client_name ?? 'N/A');
        $status = (string) ($order->diamond_status ?? 'N/A');
        $statusPayload = $this->formatOrderDiscussionStatusPayload($order->diamond_status);
        $createdOn = optional($order->created_at)->format('d M Y, h:i A');

        return Message::create([
            'channel_id' => $channel->id,
            'sender_id' => $actor->id,
            'type' => 'text',
            'body' => "Order #{$order->id} | Client: {$clientName} | Created: {$createdOn} | Status: {$status}",
            'metadata' => [
                'kind' => 'order_root',
                'order_id' => $order->id,
                'order_number' => (string) $order->id,
                'client_name' => $clientName,
                'order_created_at' => optional($order->created_at)->toIso8601String(),
                'order_status' => $status,
                'order_status_key' => $statusPayload['status_key'],
                'order_status_label' => $statusPayload['status_label'],
                'order_status_color' => $statusPayload['status_color'],
                'order_url' => route('orders.show', $order->id),
                'shipping_company_name' => $order->shipping_company_name,
                'tracking_number' => $order->tracking_number,
                'tracking_status' => $order->tracking_status,
                'dispatch_date' => optional($order->dispatch_date)->toIso8601String(),
            ],
            'thread_count' => 0,
        ]);
    }

    private function formatOrderDiscussionStatusPayload(?string $status): array
    {
        $status = trim((string) $status);

        $map = [
            'r_order_in_process' => ['status_label' => 'In Process', 'status_color' => 'info'],
            'r_order_shipped' => ['status_label' => 'Shipped', 'status_color' => 'success'],
            'r_order_cancelled' => ['status_label' => 'Cancelled', 'status_color' => 'danger'],
            'd_diamond_in_discuss' => ['status_label' => 'In Discuss', 'status_color' => 'info'],
            'd_diamond_in_making' => ['status_label' => 'In Making', 'status_color' => 'warning'],
            'd_diamond_completed' => ['status_label' => 'Completed', 'status_color' => 'success'],
            'd_diamond_in_certificate' => ['status_label' => 'In Certificate', 'status_color' => 'purple'],
            'd_order_shipped' => ['status_label' => 'Shipped', 'status_color' => 'dark'],
            'd_order_cancelled' => ['status_label' => 'Cancelled', 'status_color' => 'danger'],
            'j_diamond_in_progress' => ['status_label' => 'In Progress', 'status_color' => 'info'],
            'j_diamond_completed' => ['status_label' => 'Completed', 'status_color' => 'success'],
            'j_diamond_in_discuss' => ['status_label' => 'In Discuss', 'status_color' => 'cyan'],
            'j_cad_in_progress' => ['status_label' => 'CAD In Progress', 'status_color' => 'warning'],
            'j_cad_done' => ['status_label' => 'CAD Done', 'status_color' => 'purple'],
            'j_order_completed' => ['status_label' => 'Completed', 'status_color' => 'success'],
            'j_order_in_qc' => ['status_label' => 'In QC', 'status_color' => 'warning'],
            'j_qc_done' => ['status_label' => 'QC Done', 'status_color' => 'success'],
            'j_order_shipped' => ['status_label' => 'Shipped', 'status_color' => 'dark'],
            'j_order_hold' => ['status_label' => 'On Hold', 'status_color' => 'danger'],
            'j_order_cancelled' => ['status_label' => 'Cancelled', 'status_color' => 'danger'],
        ];

        if ($status === '') {
            return [
                'status_key' => 'unknown',
                'status_label' => 'Unknown',
                'status_color' => 'secondary',
            ];
        }

        if (isset($map[$status])) {
            return array_merge(['status_key' => $status], $map[$status]);
        }

        return [
            'status_key' => $status,
            'status_label' => \Illuminate\Support\Str::headline(str_replace(['_', '-'], ' ', $status)),
            'status_color' => 'secondary',
        ];
    }

    private function syncOrderDiscussionMembers(Channel $channel): void
    {
        $eligibleAdmins = Admin::where('is_super', true)
            ->orWhereHas('permissions', fn($q) => $q->where('slug', 'chat.access'))
            ->get();

        $membersPayload = [];
        foreach ($eligibleAdmins as $candidate) {
            $membersPayload[$candidate->id] = [
                'role' => $candidate->id === (int) $channel->created_by ? 'owner' : 'member',
                'settings' => null,
            ];
        }

        if (!isset($membersPayload[$channel->created_by])) {
            $membersPayload[$channel->created_by] = [
                'role' => 'owner',
                'settings' => null,
            ];
        }

        $channel->users()->syncWithoutDetaching($membersPayload);
    }

    /**
     * Sync tracking data from carrier website
     */
    public function syncTracking(Order $order, ShippingTrackingService $trackingService)
    {
        try {
            $result = $trackingService->syncOrderTracking($order);

            if (request()->ajax() || request()->expectsJson()) {
                if ($result['success']) {
                    $order->refresh();
                    $result['order'] = $this->buildOrderShippingPayload($order);
                }

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
                    'message' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
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
            'payment_status' => $order->payment_status,
            'payment_status_label' => $order->payment_status_label,
            'amount_received' => number_format((float) $order->amount_received_total, 2),
            'amount_due' => number_format((float) $order->amount_due_total, 2),
            'remaining_balance' => number_format((float) $order->remaining_balance, 2),
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
            if (is_array($targetFile) && isset($targetFile['public_id'])) {
                $resourceType = ($type === 'pdf') ? 'raw' : 'image';
                $this->uploadService->delete($targetFile['public_id'], $resourceType);
            }

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

        // [FIX-7] Collect file metadata BEFORE the transaction but do NOT delete yet.
        // Cloudinary deletions happen only AFTER DB commit succeeds.
        $imagesToDelete = [];
        $pdfsToDelete = [];

        $imagesRaw = $order->images;
        $images = is_string($imagesRaw) ? json_decode($imagesRaw, true) : (is_array($imagesRaw) ? $imagesRaw : []);
        foreach ($images as $image) {
            if (isset($image['public_id'])) {
                $imagesToDelete[] = $image['public_id'];
            }
        }

        $pdfsRaw = $order->order_pdfs;
        $pdfs = is_string($pdfsRaw) ? json_decode($pdfsRaw, true) : (is_array($pdfsRaw) ? $pdfsRaw : []);
        foreach ($pdfs as $pdf) {
            if (isset($pdf['public_id'])) {
                $pdfsToDelete[] = $pdf['public_id'];
            }
        }

        try {
            DB::beginTransaction();

            $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
            if (!in_array($order->diamond_status, $cancelledStatuses)) {

                // Reverse melee stock
                $meleeEntries = $this->orderService->extractStoredMeleeEntries($order);
                if (!empty($meleeEntries)) {
                    // [FIX-5] try/catch inside the transaction
                    try {
                        $returnResult = $this->meleeStockService->returnForOrder($order->id, $meleeEntries);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return back()->with('error', 'Exception returning melee stock before delete: ' . $e->getMessage());
                    }

                    if (!$returnResult['success']) {
                        DB::rollBack();
                        return back()->with('error', 'Failed to return melee stock before delete: ' . $returnResult['message']);
                    }
                }

                // Restore Diamond SKUs
                $skusToRestore = [];
                if (!empty($order->diamond_skus)) {
                    $skusToRestore = $order->diamond_skus;
                } elseif (!empty($order->diamond_sku)) {
                    $skusToRestore = [$order->diamond_sku];
                }

                if (!empty($skusToRestore)) {
                    foreach ($skusToRestore as $sku) {
                        $diamond = Diamond::where('sku', $sku)->first();
                        if ($diamond && $diamond->is_sold_out === 'Sold') {
                            $diamond->update([
                                'is_sold_out' => 'In Stock',   // [FIX-6] was missing
                                'sold_out_date' => null,
                                'sold_out_price' => null,
                            ]);
                        }
                    }
                }
            }

            $order->delete();
            DB::commit();

            // [FIX-7] Delete Cloudinary files AFTER successful DB commit.
            // If these fail, the order is already deleted from DB, so just log — no rollback possible.
            $deletedImages = 0;
            $deletedPdfs = 0;

            foreach ($imagesToDelete as $publicId) {
                try {
                    $this->uploadService->delete($publicId, 'image');
                    $deletedImages++;
                } catch (\Exception $e) {
                    Log::warning('Failed to delete order image from Cloudinary after order delete', [
                        'order_id' => $orderId,
                        'public_id' => $publicId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            foreach ($pdfsToDelete as $publicId) {
                try {
                    $this->uploadService->delete($publicId, 'raw');
                    $deletedPdfs++;
                } catch (\Exception $e) {
                    Log::warning('Failed to delete order PDF from Cloudinary after order delete', [
                        'order_id' => $orderId,
                        'public_id' => $publicId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Order deleted successfully', [
                'order_id' => $orderId,
                'deleted_images' => $deletedImages,
                'deleted_pdfs' => $deletedPdfs,
                'deleted_by' => Auth::guard('admin')->id(),
            ]);

            return redirect()->route('orders.index')
                ->with('success', 'Order and all associated files deleted successfully.');

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0)
                DB::rollBack();
            Log::error('Order deletion failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to delete order: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an order and reverse associated stock (diamonds & melee).
     */
    public function cancel(Request $request, Order $order)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->is_super && $order->submitted_by !== $admin->id) {
            abort(403, 'You don\'t have permission to cancel this order.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ]);

        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        if (in_array($order->diamond_status, $cancelledStatuses)) {
            return back()->with('info', 'This order is already cancelled.');
        }

        try {
            DB::beginTransaction();

            $oldValues = ['diamond_status' => $order->diamond_status];

            $order->diamond_status = match ($order->order_type) {
                'custom_jewellery' => 'j_order_cancelled',
                'custom_diamond' => 'd_order_cancelled',
                default => 'r_order_cancelled',
            };

            $order->cancel_reason = $validated['cancel_reason'];
            $order->cancelled_at = \Carbon\Carbon::now();
            $order->cancelled_by = Auth::guard('admin')->id();

            $newValues = [
                'diamond_status' => $order->diamond_status,
                'cancel_reason' => $order->cancel_reason,
            ];

            $order->save();

            // Reverse melee diamond stock
            $meleeEntries = $this->orderService->extractStoredMeleeEntries($order);
            if (!empty($meleeEntries)) {
                // [FIX-5] try/catch so an exception still triggers rollback
                try {
                    $returnResult = $this->meleeStockService->returnForOrder($order->id, $meleeEntries);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->with('error', 'Exception returning melee stock: ' . $e->getMessage());
                }

                if (!$returnResult['success']) {
                    DB::rollBack();
                    return back()->with('error', 'Failed to return melee stock: ' . $returnResult['message']);
                }
            }

            // Restore Diamond SKUs to 'In Stock'
            $skusToRestore = [];
            if (!empty($order->diamond_skus)) {
                $skusToRestore = $order->diamond_skus;
            } elseif (!empty($order->diamond_sku)) {
                $skusToRestore = [$order->diamond_sku];
            }

            if (!empty($skusToRestore)) {
                foreach ($skusToRestore as $sku) {
                    $diamond = Diamond::where('sku', $sku)->first();
                    if ($diamond && $diamond->is_sold_out === 'Sold') {
                        $diamond->update([
                            'is_sold_out' => 'In Stock',   // [FIX-4] was missing
                            'sold_out_date' => null,
                            'sold_out_price' => null,
                        ]);
                    }
                }
            }

            AuditLogger::log('updated', $order, Auth::guard('admin')->id(), $oldValues, $newValues);

            DB::commit();

            dispatch(function () use ($order) {
                $superAdmins = Admin::where('is_super', true)->get();
                $updatedBy = Admin::find($order->cancelled_by);

                if ($superAdmins->isNotEmpty() && $updatedBy) {
                    Notification::send($superAdmins, new OrderCancelledNotification($order, $updatedBy));
                }
            })->afterResponse();

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order cancelled successfully. Stocks have been reversed.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    /**
     * Sync tracking for all orders.
     */
    public function syncAllTracking(ShippingTrackingService $trackingService)
    {
        set_time_limit(300);
        Log::info("Bulk Sync Initiated");

        try {
            $orders = Order::where(function ($query) {
                $query->whereNotNull('tracking_number')
                    ->orWhereNotNull('tracking_url');
            })
                ->whereNotIn('diamond_status', ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'])
                ->get();

            Log::info("Bulk Sync: Found " . $orders->count() . " orders.");

            $count = 0;
            $successValues = 0;
            $failures = 0;

            foreach ($orders as $order) {
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

                usleep(200000); // 0.2s delay
            }

            return redirect()->back()->with('success', "Sync completed. Processed: $count. Success: $successValues. Failed: $failures.");

        } catch (\Exception $e) {
            Log::error("Bulk Sync Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Bulk sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Combined SKU checker for order forms (supports diamond + jewellery).
     */
    public function checkStockSku(Request $request)
    {
        $sku = strtoupper(trim($request->input('sku', '')));

        if (empty($sku)) {
            return response()->json([
                'available' => false,
                'message' => 'SKU is required'
            ], 400);
        }

        $result = $this->orderService->checkOrderSkuAvailability($sku);
        $itemPayload = $result['details'] ?? $result['item'] ?? null;
        $payload = [
            'available' => $result['available'],
            'message' => $result['message'],
            'type' => $result['type'] ?? null,
            'item' => $itemPayload,
        ];

        // Backward compatibility
        if (($result['type'] ?? null) === 'diamond') {
            $payload['diamond'] = $itemPayload;
        }

        return response()->json($payload, $result['available'] ? 200 : 422);
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
        $factories = Factory::where('is_active', true)->orderBy('name')->get();
        $metalTypes = Cache::remember('metal_types_all', 3600, fn() => MetalType::all());
        $ringSizes = Cache::remember('ring_sizes_all', 3600, fn() => RingSize::all());
        $settingTypes = Cache::remember('setting_types_all', 3600, fn() => SettingType::all());
        $closureTypes = Cache::remember('closure_types_all', 3600, fn() => ClosureType::all());

        return view($view, compact(
            'order',
            'companies',
            'factories',
            'metalTypes',
            'ringSizes',
            'settingTypes',
            'closureTypes'
        ))->render();
    }

    /**
     * Get unread new-order notification count for the sidebar badge.
     */
    public function unreadOrderCount()
    {
        $admin = Auth::guard('admin')->user();

        $count = $admin->unreadNotifications()
            ->where('type', 'App\Notifications\OrderCreatedNotification')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Send OrderDiscussionNotification to eligible admins (excluding the sender).
     */
    private function notifyOrderDiscussion(Order $order, Admin $sender, string $messageBody): void
    {
        dispatch(function () use ($order, $sender, $messageBody) {
            $eligibleAdmins = Admin::where('id', '!=', $sender->id)
                ->where(function ($q) {
                    $q->where('is_super', true)
                        ->orWhereHas('permissions', function ($pq) {
                            $pq->whereIn('slug', ['orders.view', 'orders.view_team']);
                        });
                })->get();

            if ($eligibleAdmins->isNotEmpty()) {
                Notification::send(
                    $eligibleAdmins,
                    new OrderDiscussionNotification($order, $sender, $messageBody)
                );
            }
        })->afterResponse();
    }

    /**
     * Update order status via explicit status selection.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->is_super && !$admin->hasPermission('orders.edit')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update order status.'
            ], 403);
        }

        $this->enforceOrderViewAccess($order, $admin);

        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        try {
            $statusTransitionService = new StatusTransitionService();
            $currentStatus = (string) ($order->diamond_status ?? '');
            $orderType = $order->order_type;
            $newStatus = trim((string) $validated['status']);
            $validStatuses = $statusTransitionService->getValidStatuses($orderType);

            if (!in_array($newStatus, $validStatuses, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status selected for this order type.'
                ], 422);
            }

            if ($newStatus === $currentStatus) {
                $statusUi = $this->buildOrderStatusUiPayload($order->diamond_status);

                return response()->json([
                    'success' => true,
                    'message' => 'Status already selected.',
                    'status' => $order->diamond_status,
                    'status_label' => $statusUi['status_label'],
                    'status_color' => $statusUi['status_color'],
                    'status_icon' => $statusUi['status_icon'],
                ]);
            }

            $cancelledStatuses = $this->getCancelledStatuses();
            $wasCancelled = in_array($currentStatus, $cancelledStatuses, true);
            $isCancelled = in_array($newStatus, $cancelledStatuses, true);
            $skus = $this->extractOrderSkusForStock($order);

            if ($wasCancelled && !$isCancelled) {
                foreach ($skus as $sku) {
                    $alreadySoldElsewhere = Order::where(function ($query) use ($sku) {
                        $query->where('diamond_sku', $sku);

                        if ($this->supportsJsonContains()) {
                            $query->orWhereJsonContains('diamond_skus', $sku);
                        }
                    })
                        ->where('id', '!=', $order->id)
                        ->whereNotIn('diamond_status', $cancelledStatuses)
                        ->exists();

                    if ($alreadySoldElsewhere) {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot move this cancelled order back to an active status because diamond SKU [{$sku}] is already assigned to another active order.",
                        ], 409);
                    }
                }
            }

            DB::transaction(function () use ($order, $newStatus, $admin, $currentStatus, $wasCancelled, $isCancelled, $skus) {
                $order->diamond_status = $newStatus;
                $order->last_modified_by = $admin->id;
                $order->save();

                if (!$wasCancelled && $isCancelled) {
                    foreach ($skus as $sku) {
                        $diamond = Diamond::where('sku', $sku)->first();
                        if ($diamond && $diamond->is_sold_out === 'Sold') {
                            $diamond->update([
                                'is_sold_out' => 'In Stock',
                                'sold_out_date' => null,
                                'sold_out_price' => null,
                            ]);
                        }
                    }
                }

                if ($wasCancelled && !$isCancelled) {
                    foreach ($skus as $sku) {
                        $diamond = Diamond::where('sku', $sku)->first();
                        if (!$diamond) {
                            continue;
                        }

                        $diamond->update([
                            'is_sold_out' => 'Sold',
                            'sold_out_date' => now(),
                            'sold_out_price' => $diamond->sold_out_price ?? 0,
                        ]);
                    }
                }

                AuditLogger::log('updated', $order, $admin->id, [
                    'Diamond Status' => $this->formatStatusLabel($currentStatus),
                ], [
                    'Diamond Status' => $this->formatStatusLabel($newStatus),
                ]);
            });

            // ── Queue update notifications ─────────────────────────────────────
            dispatch(function () use ($order, $currentStatus, $newStatus, $admin) {
                $adminsToNotify = Admin::where('id', '!=', $admin->id)
                    ->where(function ($q) {
                        $q->where('is_super', true)
                            ->orWhereHas('permissions', function ($pq) {
                                $pq->whereIn('slug', ['orders.view', 'orders.view_team']);
                            });
                    })->get();

                if ($adminsToNotify->isNotEmpty()) {
                    Notification::send($adminsToNotify, new OrderUpdatedNotification(
                        $order,
                        $admin,
                        ['Diamond Status' => $this->formatStatusLabel($currentStatus)],
                        ['Diamond Status' => $this->formatStatusLabel($newStatus)]
                    ));
                }
            })->afterResponse();

            $statusUi = $this->buildOrderStatusUiPayload($newStatus);

            Log::info('Order status updated via dropdown', [
                'order_id' => $order->id,
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
                'updated_by' => $admin->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'status' => $newStatus,
                'status_label' => $statusUi['status_label'],
                'status_color' => $statusUi['status_color'],
                'status_icon' => $statusUi['status_icon'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update shipping details from the orders index modal.
     */
    public function updateShipping(Request $request, Order $order)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin->is_super && !$admin->hasPermission('orders.edit')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update shipping.'
            ], 403);
        }

        $this->enforceOrderViewAccess($order, $admin);

        $validated = $request->validate([
            'shipping_company_name' => 'nullable|string|max:191',
            'tracking_number' => 'nullable|string|max:191',
            'tracking_url' => 'nullable|url|max:1000',
            'dispatch_date' => 'nullable|date',
        ]);

        $auditLabels = [
            'shipping_company_name' => 'Shipping Company',
            'tracking_number' => 'Tracking Number',
            'tracking_url' => 'Tracking URL',
            'dispatch_date' => 'Dispatch Date',
        ];

        try {
            $oldSnapshot = [];
            foreach (array_keys($auditLabels) as $field) {
                $oldSnapshot[$field] = $order->getOriginal($field);
            }

            $this->orderService->assignShippingFields($order, $validated);

            $oldValues = [];
            $newValues = [];

            foreach ($auditLabels as $field => $label) {
                $oldValue = $oldSnapshot[$field];
                $newValue = $order->{$field};

                $oldCompare = $field === 'dispatch_date'
                    ? (string) optional($oldValue ? \Illuminate\Support\Carbon::parse($oldValue) : null)->toDateString()
                    : (string) $oldValue;
                $newCompare = $field === 'dispatch_date'
                    ? (string) optional($newValue ? \Illuminate\Support\Carbon::parse($newValue) : null)->toDateString()
                    : (string) $newValue;

                if ($oldCompare !== $newCompare) {
                    $oldValues[$label] = $field === 'dispatch_date'
                        ? ($oldValue ? \Illuminate\Support\Carbon::parse($oldValue)->format('d M Y') : null)
                        : $oldValue;
                    $newValues[$label] = $field === 'dispatch_date'
                        ? ($newValue ? \Illuminate\Support\Carbon::parse($newValue)->format('d M Y') : null)
                        : $newValue;
                }
            }

            if (!empty($oldValues) || !empty($newValues)) {
                $order->last_modified_by = $admin->id;
                $order->save();

                AuditLogger::log('updated', $order, $admin->id, $oldValues, $newValues);
            } else {
                $order->refresh();
            }

            return response()->json([
                'success' => true,
                'message' => !empty($newValues) ? 'Shipping updated successfully.' : 'No shipping changes detected.',
                'order' => $this->buildOrderShippingPayload($order->fresh()),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update shipping details', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getCancelledStatuses(): array
    {
        return ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
    }

    private function extractOrderSkusForStock(Order $order): array
    {
        if (!empty($order->diamond_skus) && is_array($order->diamond_skus)) {
            return array_values(array_filter($order->diamond_skus));
        }

        if (!empty($order->diamond_sku)) {
            return [$order->diamond_sku];
        }

        return [];
    }

    private function formatStatusLabel(?string $status): string
    {
        $status = trim((string) $status);

        if ($status === '') {
            return 'N/A';
        }

        return (string) ($this->buildOrderStatusUiPayload($status)['status_label'] ?? 'N/A');
    }

    private function buildOrderStatusUiPayload(?string $status): array
    {
        $payload = $this->formatOrderDiscussionStatusPayload($status);
        $iconMap = [
            'r_order_in_process' => 'bi-arrow-repeat',
            'r_order_shipped' => 'bi-truck',
            'r_order_cancelled' => 'bi-x-circle',
            'd_diamond_in_discuss' => 'bi-chat-dots',
            'd_diamond_in_making' => 'bi-tools',
            'd_diamond_completed' => 'bi-gem',
            'd_diamond_in_certificate' => 'bi-file-earmark-text',
            'd_order_shipped' => 'bi-truck',
            'd_order_cancelled' => 'bi-x-circle',
            'j_diamond_in_progress' => 'bi-gem',
            'j_diamond_completed' => 'bi-check-circle',
            'j_diamond_in_discuss' => 'bi-chat-dots',
            'j_cad_in_progress' => 'bi-pencil-square',
            'j_cad_done' => 'bi-file-check',
            'j_order_completed' => 'bi-award',
            'j_order_in_qc' => 'bi-search',
            'j_qc_done' => 'bi-check-all',
            'j_order_shipped' => 'bi-truck',
            'j_order_hold' => 'bi-pause-circle',
            'j_order_cancelled' => 'bi-x-circle',
        ];

        return [
            'status_key' => $payload['status_key'],
            'status_label' => $payload['status_label'],
            'status_color' => $payload['status_color'],
            'status_icon' => $iconMap[$payload['status_key']] ?? 'bi-circle',
        ];
    }

    private function buildOrderShippingPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'shipping_company_name' => $order->shipping_company_name,
            'tracking_number' => $order->tracking_number,
            'tracking_url' => $order->tracking_url,
            'tracking_status' => $order->tracking_status,
            'tracking_history' => $order->tracking_history ?? [],
            'dispatch_date' => optional($order->dispatch_date)->format('Y-m-d'),
            'dispatch_date_label' => optional($order->dispatch_date)->format('d M Y'),
            'has_shipping_details' => filled($order->shipping_company_name) || filled($order->tracking_number) || filled($order->tracking_url) || filled($order->dispatch_date),
        ];
    }

    private function supportsJsonContains(): bool
    {
        $driver = DB::connection()->getDriverName();

        return in_array($driver, ['mysql', 'pgsql', 'sqlite'], true);
    }
}
