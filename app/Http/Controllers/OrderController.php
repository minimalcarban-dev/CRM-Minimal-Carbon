<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
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
    private $cloudinary;
    private MeleeStockService $meleeStockService;

    public function __construct(MeleeStockService $meleeStockService)
    {
        $this->meleeStockService = $meleeStockService;

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

        $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        $baseQuery = Order::query()->with(['company', 'creator', 'factoryRelation']);

        // Super admin sees all orders, regular admin sees only their submitted orders
        // Unless they have 'orders.view_team' permission which allows viewing team orders
        if (!$admin->is_super) {
            // Check if admin has view_team permission
            if (!$admin->hasPermission('orders.view_team')) {
                $baseQuery->where('submitted_by', $admin->id);
            }
        }

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

        // Count In Transit orders based on tracking_status
        $inTransitCount = (clone $baseQuery)
            ->where('tracking_status', 'In Transit')
            ->count();

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
        
        $todaysSales = $todaysSalesQuery->get()->sum('amount_received_total');
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

        $monthSales = $monthSalesQuery->get()->sum('amount_received_total');

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

        $statusQuickViewActive = ($request->filled('shipped') && $request->shipped == '1')
            || ($request->filled('in_transit') && $request->in_transit == '1')
            || ($request->filled('cancelled') && $request->cancelled == '1');

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
        } elseif ($request->filled('in_transit') && $request->in_transit == '1') {
            $query->where('tracking_status', 'In Transit');
        } elseif ($request->filled('cancelled') && $request->cancelled == '1') {
            $query->whereIn('diamond_status', $cancelledStatuses);
        } else {
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
            'inTransitCount',
            'todaysSales',
            'monthSales',
            'todaysOrderCount',
            'companySalesStats',
            'factories',
            'companies'
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
    public function store(Request $request)
    {
        try {
            $validated = $this->validateOrder($request);
            $allowNegativeMelee = (bool) ($validated['allow_negative_melee'] ?? false);
            $paymentSummary = $this->normalizePaymentSummary(
                $validated,
                (float) ($validated['gross_sell'] ?? 0)
            );

            // ✅ CRITICAL: Extract and validate melee entries BEFORE creating order
            $incomingMeleeEntries = $this->extractValidatedMeleeEntries($validated);
            $meleeEntriesForStock = $incomingMeleeEntries;
            $validationResult = $this->meleeStockService->validateAvailability($incomingMeleeEntries, [
                'allow_negative' => $allowNegativeMelee,
            ]);
            if (!$validationResult['valid']) {
                return $this->orderErrorResponse($request, $validationResult['message']);
            }

            // ✅ CRITICAL: Extract and validate ALL diamond SKUs BEFORE creating order
            $allSkus = $this->extractValidatedSkus($validated);
            $validatedDiamonds = [];

            foreach ($allSkus as $sku) {
                $skuCheck = $this->checkOrderSkuAvailability($sku);
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
                $images = $this->uploadToCloudinary($request, 'images', 'orders/images', 10);
                $pdfs = $this->uploadToCloudinary($request, 'order_pdfs', 'orders/pdfs', 5, true);
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
            $this->assignOrderFields($order, $validated);
            $order->submitted_by = Auth::guard('admin')->id();
            $order->payment_status = $paymentSummary['payment_status'];
            $order->amount_received = $paymentSummary['amount_received'];
            $order->amount_due = $paymentSummary['amount_due'];

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

            // Attach uploaded files
            $order->images = $images;
            $order->order_pdfs = $pdfs;

            $order->save();

            if ($paymentSummary['amount_received'] > 0) {
                OrderPayment::create([
                    'order_id' => $order->id,
                    'amount' => $paymentSummary['amount_received'],
                    'payment_method' => null,
                    'reference_number' => null,
                    'notes' => 'Initial payment captured during order creation.',
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
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];

        // If order is cancelled, only allow updating 'special_notes' UNLESS user is a super admin
        if (in_array($order->diamond_status, $cancelledStatuses) && !Auth::guard('admin')->user()->is_super) {
            $validated = $request->validate([
                'special_notes' => 'nullable|string|max:2000',
            ]);
            $order->update([
                'special_notes' => $validated['special_notes'] ?? null
            ]);
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
            $validated = $this->validateOrder($request);
            $allowNegativeMelee = (bool) ($validated['allow_negative_melee'] ?? false);

            // ✅ CRITICAL: Validate melee stock with reservation logic
            $incomingMeleeEntries = $this->extractValidatedMeleeEntries($validated);
            $this->validateMeleeStockAvailability($incomingMeleeEntries, $order, $allowNegativeMelee);

            // ✅ CRITICAL: Validate only NEWLY ADDED SKUs (existing ones are already reserved)
            $submittedSkus = $this->extractValidatedSkus($validated);
            $existingOrderSkus = $this->extractOrderSkus($order);
            $newlyAddedSkus = array_values(array_diff($submittedSkus, $existingOrderSkus));
            $newlyAddedDiamonds = [];

            foreach ($newlyAddedSkus as $sku) {
                $skuCheck = $this->checkOrderSkuAvailability($sku);
                if (!$skuCheck['available']) {
                    return $this->orderErrorResponse($request, $skuCheck['message']);
                }

                if (($skuCheck['type'] ?? null) === 'diamond' && isset($skuCheck['item'])) {
                    $newlyAddedDiamonds[] = $skuCheck['item'];
                }
            }

            // ⚡ PERFORMANCE: Upload files BEFORE transaction
            $newImages = $this->uploadToCloudinary($request, 'images', 'orders/images', 10);
            $newPdfs = $this->uploadToCloudinary($request, 'order_pdfs', 'orders/pdfs', 5, true);

            DB::beginTransaction();

            // Normalize existing files
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

            // Merge old + new files
            $order->images = array_merge($existingImages, $newImages);
            $order->order_pdfs = array_merge($existingPdfs, $newPdfs);

            // Track primary SKU change for logs
            $oldDiamondSku = $order->diamond_sku;
            $newDiamondSku = $validated['diamond_sku'] ?? '';

            // Snapshot old values for audit log
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
            $oldSnapshot = [];
            foreach (array_keys($auditFields) as $field) {
                $oldSnapshot[$field] = $order->getOriginal($field);
            }

            // Update fields
            $this->assignOrderFields($order, $validated);
            $order->last_modified_by = Auth::guard('admin')->id();
            $paymentSummary = $this->normalizePaymentSummary($validated, (float) ($order->gross_sell ?? 0));
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

            // Compute diff and log audit entry
            $oldValues = [];
            $newValues = [];
            $fkResolvers = [
                'company_id' => fn($id) => $id ? (Company::find($id)->name ?? "ID:$id") : null,
                'factory_id' => fn($id) => $id ? (Factory::find($id)->name ?? "ID:$id") : null,
                'gold_detail_id' => fn($id) => $id ? (MetalType::find($id)->name ?? "ID:$id") : null,
                'ring_size_id' => fn($id) => $id ? (RingSize::find($id)->name ?? "ID:$id") : null,
                'setting_type_id' => fn($id) => $id ? (SettingType::find($id)->name ?? "ID:$id") : null,
                'earring_type_id' => fn($id) => $id ? (ClosureType::find($id)->name ?? "ID:$id") : null,
                'melee_diamond_id' => fn($id) => $id ? (MeleeDiamond::find($id)->name ?? "ID:$id") : null,
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
            $order->save();

            // Melee Stock Change Detection using service
            $oldMeleeEntries = $this->extractSnapshotMeleeEntries($oldSnapshot);
            $newMeleeEntries = $this->normalizeStoredMeleeEntries(
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

            $meleeChanged = json_encode($oldMeleeEntries) !== json_encode($newMeleeEntries);

            if ($meleeChanged) {
                // Reverse old stock
                if (!empty($oldMeleeEntries)) {
                    $returnResult = $this->meleeStockService->returnForOrder($order->id, $oldMeleeEntries);
                    if (!$returnResult['success']) {
                        DB::rollBack();
                        return $this->orderErrorResponse(
                            $request,
                            'Failed to return old stock: ' . $returnResult['message'],
                            $returnResult['status'] ?? 422
                        );
                    }
                }

                // Deduct new stock
                if (!empty($newMeleeEntries)) {
                    $deductResult = $this->meleeStockService->deductForOrder($order->id, $newMeleeEntries, [
                        'allow_negative' => $allowNegativeMelee,
                    ]);
                    if (!$deductResult['success']) {
                        DB::rollBack();
                        return $this->orderErrorResponse(
                            $request,
                            'Failed to deduct new stock: ' . $deductResult['message'],
                            $deductResult['status'] ?? 422
                        );
                    }
                }
            } else {
                $updatedMeleeDiamondIds = array_values(array_unique(array_column($newMeleeEntries, 'melee_diamond_id')));
            }

            // Log audit after save succeeds
            if (!empty($oldValues) || !empty($newValues)) {
                AuditLogger::log('updated', $order, Auth::guard('admin')->id(), $oldValues, $newValues);
            }

            // ⚡ PERFORMANCE: Bulk update newly added diamonds
            if (!empty($newlyAddedDiamonds)) {
                $diamondPrices = $validated['diamond_prices'] ?? [];

                foreach ($newlyAddedDiamonds as $diamond) {
                    $soldPriceUsd = (float) ($diamondPrices[$diamond->sku] ?? 0);
                    Diamond::where('id', $diamond->id)->update([
                        'is_sold_out' => 'Sold',
                        'sold_out_date' => now(),
                        'sold_out_price' => $soldPriceUsd,
                        'updated_at' => now()
                    ]);
                }
            }

            // ⚡ GOLD TRACING: Auto-log consumption if gold weight changes during update
            $oldWeight = (float) ($oldSnapshot['gold_net_weight'] ?? 0);
            $newWeight = (float) $order->gold_net_weight;
            if ($oldWeight !== $newWeight && $order->factory_id) {
                $difference = $newWeight - $oldWeight;
                if ($difference != 0) {
                    // ✅ STOCK GUARD: Prevent consuming more gold than factory has
                    if ($difference > 0) {
                        $factory = Factory::find($order->factory_id);
                        if ($factory) {
                            $factoryStock = $factory->current_stock;
                            if ($difference > $factoryStock) {
                                DB::rollBack();
                                $errorMsg = "Gold weight increase ({$difference}g) exceeds factory stock ({$factoryStock}g) for {$factory->name}. Available: {$factoryStock}g, max total weight: " . round($oldWeight + $factoryStock, 3) . "g.";
                                return $this->orderErrorResponse($request, $errorMsg);
                            }
                        }
                    }

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

            DB::commit();
            $meleeStockSummary = $this->meleeStockService->getStockSummary($updatedMeleeDiamondIds ?? []);

            Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'new_images' => count($newImages),
                'new_pdfs' => count($newPdfs),
                'old_diamond_sku' => $oldDiamondSku,
                'new_diamond_sku' => $newDiamondSku,
                'updated_by' => Auth::guard('admin')->id()
            ]);

            // ⚡ PERFORMANCE: Queue update notifications
            if (!empty($newValues)) {
                dispatch(function () use ($order, $oldValues, $newValues) {
                    $updatedBy = Admin::find($order->last_modified_by);
                    if (!$updatedBy)
                        return;

                    // Notify all admins who have access to orders (excluding the updater)
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
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw $e;
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Order update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::guard('admin')->id()
            ]);
            return $this->orderErrorResponse($request, 'Failed to update order: ' . $e->getMessage(), 500);
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
        $metalTypes = MetalType::all();
        $ringSizes = RingSize::all();
        $settingTypes = SettingType::all();
        $closureTypes = ClosureType::all();
        $companies = Company::all();

        $editHistory = $order->editHistory()->with('admin')->get();
        $discussionChannel = $this->getOrCreateOrderDiscussionChannel();
        $discussionRootMessage = $this->getOrCreateOrderDiscussionRootMessage($order, $discussionChannel, $admin);
        $canPostDiscussion = $admin->is_super || $admin->hasPermission('orders.edit');
        $canManagePayments = $admin->is_super || $admin->hasPermission('orders.edit');
        $discussionSearch = trim((string) request()->query('discussion_search', ''));
        $discussionRootMessage->load([
            'sender:id,name',
            'replies' => fn($q) => $q->with('sender:id,name')->latest()->limit(50),
        ]);

        if ($discussionSearch !== '') {
            $filteredReplies = $discussionRootMessage->replies->filter(function (Message $reply) use ($discussionSearch) {
                $replyBody = mb_strtolower((string) ($reply->body ?? ''));
                $replySender = mb_strtolower((string) ($reply->sender->name ?? ''));
                $needle = mb_strtolower($discussionSearch);
                return str_contains($replyBody, $needle) || str_contains($replySender, $needle);
            })->values();

            $discussionRootMessage->setRelation('replies', $filteredReplies);
        }

        $discussionMessages = collect([$discussionRootMessage]);

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
            'discussionSearch',
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

    private function getOrCreateOrderDiscussionRootMessage(Order $order, Channel $channel, Admin $actor): Message
    {
        $existing = Message::query()
            ->where('channel_id', $channel->id)
            ->whereNull('reply_to_id')
            ->where('metadata->kind', 'order_root')
            ->where('metadata->order_id', $order->id)
            ->first();

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
        $eligibleAdmins = Admin::query()->get()->filter(function (Admin $candidate) {
            if ($candidate->is_super) {
                return true;
            }

            return $candidate->hasPermission('chat.access');
        });

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
                $this->deleteFromCloudinary($targetFile['public_id'], $resourceType);
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

            DB::beginTransaction();

            // Reverse Stock on Delete (if not already cancelled)
            $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
            if (!in_array($order->diamond_status, $cancelledStatuses)) {
                $meleeEntries = $this->extractStoredMeleeEntries($order);
                if (!empty($meleeEntries)) {
                    $returnResult = $this->meleeStockService->returnForOrder($order->id, $meleeEntries);
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
                                'sold_out_date' => null,
                                'sold_out_price' => null,
                            ]);
                        }
                    }
                }
            }

            $order->delete();
            DB::commit();

            Log::info('Order deleted successfully', [
                'order_id' => $orderId,
                'deleted_images' => $deletedImages,
                'deleted_pdfs' => $deletedPdfs,
                'deleted_by' => Auth::guard('admin')->id()
            ]);

            return redirect()->route('orders.index')->with('success', 'Order and all associated files deleted successfully from Cloudinary.');

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
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

            // Assign proper cancelled status based on type
            if ($order->order_type === 'custom_jewellery') {
                $order->diamond_status = 'j_order_cancelled';
            } elseif ($order->order_type === 'custom_diamond') {
                $order->diamond_status = 'd_order_cancelled';
            } else {
                $order->diamond_status = 'r_order_cancelled';
            }

            $order->cancel_reason = $validated['cancel_reason'];
            $order->cancelled_at = \Carbon\Carbon::now();
            $order->cancelled_by = Auth::guard('admin')->id();

            $newValues = [
                'diamond_status' => $order->diamond_status,
                'cancel_reason' => $order->cancel_reason,
            ];

            $order->save();

            // Reverse melee diamond stock
            $meleeEntries = $this->extractStoredMeleeEntries($order);
            if (!empty($meleeEntries)) {
                $returnResult = $this->meleeStockService->returnForOrder($order->id, $meleeEntries);
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
                            'sold_out_date' => null,
                            'sold_out_price' => null,
                        ]);
                    }
                }
            }

            AuditLogger::log('updated', $order, Auth::guard('admin')->id(), $oldValues, $newValues);

            DB::commit();

            // ⚡ PERFORMANCE: Queue cancellation notifications
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
                'error' => $e->getMessage()
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

        $result = $this->checkOrderSkuAvailability($sku);
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
     * ✅ CRITICAL HELPER: Check order SKU availability across diamonds and jewellery stock.
     */
    private function checkOrderSkuAvailability(string $sku): array
    {
        $sku = strtoupper(trim($sku));

        if ($sku === '') {
            return [
                'available' => false,
                'type' => null,
                'item' => null,
                'details' => null,
                'message' => 'SKU is required',
            ];
        }

        $diamond = Diamond::where('sku', $sku)->first();
        if ($diamond) {
            if ($diamond->is_sold_out === 'Sold') {
                return [
                    'available' => false,
                    'type' => 'diamond',
                    'item' => $diamond,
                    'details' => [
                        'sku' => $diamond->sku,
                        'display_details' => trim(($diamond->weight ?? '?') . 'ct ' . ($diamond->shape ?? '')),
                        'carat' => $diamond->weight,
                        'shape' => $diamond->shape,
                        'clarity' => $diamond->clarity,
                        'color' => $diamond->color,
                        'listing_price' => $diamond->listing_price,
                        'stock_status' => 'sold',
                    ],
                    'message' => 'Diamond "' . $sku . '" is already sold. Please remove it and select a different SKU.',
                ];
            }

            return [
                'available' => true,
                'type' => 'diamond',
                'item' => $diamond,
                'message' => 'Diamond SKU available',
                'details' => [
                    'sku' => $diamond->sku,
                    'display_details' => trim(($diamond->weight ?? '?') . 'ct ' . ($diamond->shape ?? '')),
                    'carat' => $diamond->weight,
                    'shape' => $diamond->shape,
                    'clarity' => $diamond->clarity,
                    'color' => $diamond->color,
                    'listing_price' => $diamond->listing_price,
                ],
            ];
        }

        $jewellery = JewelleryStock::where('sku', $sku)->first();
        if ($jewellery) {
            if ((int) $jewellery->quantity <= 0 || $jewellery->status === 'out_of_stock') {
                return [
                    'available' => false,
                    'type' => 'jewellery',
                    'item' => $jewellery,
                    'details' => [
                        'sku' => $jewellery->sku,
                        'display_details' => trim(($jewellery->name ?? 'Jewellery') . ' ' . (!empty($jewellery->type) ? '(' . $jewellery->type . ')' : '')),
                        'name' => $jewellery->name,
                        'type' => $jewellery->type,
                        'quantity' => (int) $jewellery->quantity,
                        'selling_price' => $jewellery->selling_price,
                        'stock_status' => 'out_of_stock',
                    ],
                    'message' => 'Jewellery SKU "' . $sku . '" is out of stock.',
                ];
            }

            return [
                'available' => true,
                'type' => 'jewellery',
                'item' => $jewellery,
                'message' => 'Jewellery SKU available',
                'details' => [
                    'sku' => $jewellery->sku,
                    'display_details' => trim(($jewellery->name ?? 'Jewellery') . ' ' . (!empty($jewellery->type) ? '(' . $jewellery->type . ')' : '')),
                    'name' => $jewellery->name,
                    'type' => $jewellery->type,
                    'quantity' => (int) $jewellery->quantity,
                    'selling_price' => $jewellery->selling_price,
                ],
            ];
        }

        return [
            'available' => false,
            'type' => null,
            'item' => null,
            'details' => null,
            'message' => 'SKU "' . $sku . '" not found in diamond or jewellery stock.',
        ];
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
            'diamond_skus' => 'nullable|array',
            'diamond_skus.*' => 'nullable|string|max:191',
            'diamond_status' => 'nullable|string|in:r_order_in_process,r_order_shipped,d_diamond_in_discuss,d_diamond_in_making,d_diamond_completed,d_diamond_in_certificate,d_order_shipped,j_diamond_in_progress,j_diamond_completed,j_diamond_in_discuss,j_cad_in_progress,j_cad_done,j_order_completed,j_order_in_qc,j_qc_done,j_order_shipped,j_order_hold',
            'company_id' => 'required|exists:companies,id',
            'factory_id' => 'nullable|exists:factories,id',
            'gross_sell' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:full,partial,due,custom',
            'amount_received' => 'nullable|numeric|min:0',
            'amount_due' => 'nullable|numeric|min:0',
            'gold_net_weight' => 'nullable|numeric|min:0',
            'dispatch_date' => 'nullable|date',
            'note' => 'nullable|in:priority,non_priority',
            'special_notes' => 'nullable|string|max:2000',
            'shipping_company_name' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'tracking_url' => 'nullable|url',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,avif,gif,webp|max:10240',
            'order_pdfs.*' => 'nullable|mimes:pdf|max:10240',
            'diamond_prices' => 'nullable|array',
            'diamond_prices.*' => 'nullable|numeric|min:0',

            // Melee Fields
            'melee_entries_json' => 'nullable|string',
            'melee_entries' => 'nullable|array',
            'melee_entries.*.melee_diamond_id' => 'required|exists:melee_diamonds,id',
            'melee_entries.*.pieces' => 'required|integer|min:1',
            'melee_entries.*.avg_carat_per_piece' => 'nullable|numeric|min:0',
            'melee_entries.*.price_per_ct' => 'nullable|numeric|min:0',
            'allow_negative_melee' => 'nullable|boolean',

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
     * ✅ CRITICAL HELPER: Extract normalized SKU list from validated payload.
     */
    private function extractValidatedSkus(array $validated): array
    {
        $rawSkus = [];

        if (!empty($validated['diamond_skus']) && is_array($validated['diamond_skus'])) {
            $rawSkus = array_merge($rawSkus, $validated['diamond_skus']);
        }

        if (!empty($validated['diamond_sku'])) {
            $rawSkus[] = $validated['diamond_sku'];
        }

        $normalized = array_map(
            static fn($sku) => strtoupper(trim((string) $sku)),
            $rawSkus
        );

        return array_values(array_unique(array_filter($normalized)));
    }

    /**
     * ✅ CRITICAL HELPER: Extract normalized SKU list from existing order.
     */
    private function extractOrderSkus(Order $order): array
    {
        $rawSkus = [];

        if (!empty($order->diamond_skus) && is_array($order->diamond_skus)) {
            $rawSkus = array_merge($rawSkus, $order->diamond_skus);
        }

        if (!empty($order->diamond_sku)) {
            $rawSkus[] = $order->diamond_sku;
        }

        $normalized = array_map(
            static fn($sku) => strtoupper(trim((string) $sku)),
            $rawSkus
        );

        return array_values(array_unique(array_filter($normalized)));
    }

    /**
     * ✅ CRITICAL HELPER: Normalize incoming melee payload.
     */
    private function extractValidatedMeleeEntries(array $validated): array
    {
        $entries = [];

        if (!empty($validated['melee_entries']) && is_array($validated['melee_entries'])) {
            $entries = $validated['melee_entries'];
        } elseif (!empty($validated['melee_entries_json'])) {
            $decoded = json_decode($validated['melee_entries_json'], true);
            if (is_array($decoded)) {
                $entries = $decoded;
            }
        } elseif (!empty($validated['melee_diamond_id']) && !empty($validated['melee_pieces'])) {
            $entries = [
                [
                    'melee_diamond_id' => $validated['melee_diamond_id'],
                    'pieces' => $validated['melee_pieces'],
                ]
            ];
        }

        return array_values(array_filter($entries, function ($entry) {
            return !empty($entry['melee_diamond_id']) && (int) ($entry['pieces'] ?? 0) > 0;
        }));
    }

    /**
     * Normalize melee entries stored on an order or snapshot, with legacy fallback support.
     *
     * @param mixed $entries
     * @return array<int, array<string, mixed>>
     */
    private function normalizeStoredMeleeEntries(
        $entries,
        $fallbackDiamondId = null,
        $fallbackPieces = null,
        $fallbackCarat = null,
        $fallbackPricePerCt = null
    ): array {
        if (is_string($entries)) {
            $decoded = json_decode($entries, true);
            $entries = is_array($decoded) ? $decoded : [];
        }

        if (empty($entries) && !empty($fallbackDiamondId) && (int) $fallbackPieces > 0) {
            $entries = [
                [
                    'melee_diamond_id' => (int) $fallbackDiamondId,
                    'pieces' => (int) $fallbackPieces,
                    'avg_carat_per_piece' => (int) $fallbackPieces > 0
                        ? round((float) $fallbackCarat / (int) $fallbackPieces, 5)
                        : 0,
                    'price_per_ct' => (float) ($fallbackPricePerCt ?? 0),
                ]
            ];
        }

        return array_values(array_filter(array_map(function ($entry) {
            return [
                'melee_diamond_id' => (int) ($entry['melee_diamond_id'] ?? 0),
                'pieces' => (int) ($entry['pieces'] ?? 0),
                'avg_carat_per_piece' => round((float) ($entry['avg_carat_per_piece'] ?? 0), 5),
                'price_per_ct' => (float) ($entry['price_per_ct'] ?? 0),
            ];
        }, (array) $entries), function ($entry) {
            return $entry['melee_diamond_id'] > 0 && $entry['pieces'] > 0;
        }));
    }

    /**
     * Normalize melee entries directly from the persisted order record.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractStoredMeleeEntries(Order $order): array
    {
        return $this->normalizeStoredMeleeEntries(
            $order->melee_entries,
            $order->melee_diamond_id,
            $order->melee_pieces,
            $order->melee_carat,
            $order->melee_price_per_ct
        );
    }

    /**
     * Normalize melee entries from an old audit snapshot.
     *
     * @param array<string, mixed> $snapshot
     * @return array<int, array<string, mixed>>
     */
    private function extractSnapshotMeleeEntries(array $snapshot): array
    {
        return $this->normalizeStoredMeleeEntries(
            $snapshot['melee_entries'] ?? [],
            $snapshot['melee_diamond_id'] ?? null,
            $snapshot['melee_pieces'] ?? null,
            $snapshot['melee_carat'] ?? null,
            $snapshot['melee_price_per_ct'] ?? null
        );
    }

    /**
     * ✅ CRITICAL HELPER: Validate requested melee pieces against live stock.
     */
    private function validateMeleeStockAvailability(
        array $incomingEntries,
        ?Order $existingOrder = null,
        bool $allowNegative = false
    ): void {
        if (empty($incomingEntries)) {
            return;
        }

        $requestedByMeleeId = [];
        $entryIndexesByMeleeId = [];

        foreach ($incomingEntries as $index => $entry) {
            $meleeId = (int) ($entry['melee_diamond_id'] ?? 0);
            $pieces = (int) ($entry['pieces'] ?? 0);

            if ($meleeId <= 0 || $pieces <= 0) {
                continue;
            }

            $requestedByMeleeId[$meleeId] = ($requestedByMeleeId[$meleeId] ?? 0) + $pieces;
            $entryIndexesByMeleeId[$meleeId][] = $index;
        }

        if (empty($requestedByMeleeId)) {
            return;
        }

        $reservedByMeleeId = $this->getReservedMeleePiecesFromOrder($existingOrder);
        $diamonds = MeleeDiamond::with('category')
            ->whereIn('id', array_keys($requestedByMeleeId))
            ->get()
            ->keyBy('id');

        $errors = [];

        foreach ($requestedByMeleeId as $meleeId => $requestedPieces) {
            $diamond = $diamonds->get($meleeId);
            $affectedIndexes = $entryIndexesByMeleeId[$meleeId] ?? [];

            if (!$diamond) {
                foreach ($affectedIndexes as $index) {
                    $errors["melee_entries.$index.melee_diamond_id"] = 'Selected melee stock lot was not found.';
                }
                continue;
            }

            $allowedPieces = (int) $diamond->available_pieces + (int) ($reservedByMeleeId[$meleeId] ?? 0);

            if (!$allowNegative && $requestedPieces > $allowedPieces) {
                $label = $this->buildMeleeStockLabel($diamond);
                $message = "{$label} has only {$allowedPieces} pieces available in stock.";

                foreach ($affectedIndexes as $index) {
                    $errors["melee_entries.$index.pieces"] = $message;
                }

                $errors['melee_entries_json'] = $message;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * ✅ CRITICAL HELPER: Get existing order reservation.
     */
    private function getReservedMeleePiecesFromOrder(?Order $order): array
    {
        if (!$order) {
            return [];
        }

        $entries = $this->extractStoredMeleeEntries($order);

        $reserved = [];
        foreach ($entries as $entry) {
            $meleeId = (int) ($entry['melee_diamond_id'] ?? 0);
            $pieces = (int) ($entry['pieces'] ?? 0);

            if ($meleeId <= 0 || $pieces <= 0) {
                continue;
            }

            $reserved[$meleeId] = ($reserved[$meleeId] ?? 0) + $pieces;
        }

        return $reserved;
    }

    private function orderErrorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()->withInput()->with('error', $message);
    }

    /**
     * ✅ CRITICAL HELPER: Build melee stock label.
     */
    private function buildMeleeStockLabel(MeleeDiamond $diamond): string
    {
        $category = optional($diamond->category)->name ?? 'Melee';
        $shape = trim((string) $diamond->shape);
        $size = trim((string) $diamond->size_label);

        return trim("{$category} - {$shape} {$size}");
    }

    /**
     * Assign common validated fields to Order model.
     */
    private function assignOrderFields(Order $order, array $validated): void
    {
        $order->order_type = $validated['order_type'];
        $order->company_id = $validated['company_id'];
        $order->factory_id = !empty($validated['factory_id']) ? $validated['factory_id'] : null;

        $order->client_name = $validated['client_name'] ?? '';
        $order->client_address = $validated['client_address'] ?? '';
        $order->client_mobile = $validated['client_mobile'] ?? '';
        $order->client_tax_id = $validated['client_tax_id'] ?? '';
        $order->client_tax_id_type = $validated['client_tax_id_type'] ?? null;
        $order->client_email = $validated['client_email'] ?? '';
        $order->jewellery_details = $validated['jewellery_details'] ?? '';
        $order->diamond_details = $validated['diamond_details'] ?? '';
        $order->diamond_sku = $validated['diamond_sku'] ?? '';

        // Handle multiple diamond SKUs
        if (!empty($validated['diamond_skus'])) {
            $skus = array_filter(array_unique($validated['diamond_skus']));
            $order->diamond_skus = $skus;
            if (empty($order->diamond_sku) && !empty($skus)) {
                $order->diamond_sku = $skus[0];
            }
        } elseif (!empty($validated['diamond_sku'])) {
            $order->diamond_skus = [$validated['diamond_sku']];
        }

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

        $order->gold_detail_id = !empty($validated['gold_detail_id']) ? $validated['gold_detail_id'] : null;
        $order->ring_size_id = !empty($validated['ring_size_id']) ? $validated['ring_size_id'] : null;
        $order->setting_type_id = !empty($validated['setting_type_id']) ? $validated['setting_type_id'] : null;
        $order->earring_type_id = !empty($validated['earring_type_id']) ? $validated['earring_type_id'] : null;

        // Melee Fields
        $meleeEntries = [];
        if (!empty($validated['melee_entries'])) {
            $meleeEntries = $validated['melee_entries'];
        } elseif (!empty($validated['melee_entries_json'])) {
            $decoded = json_decode($validated['melee_entries_json'], true);
            if (is_array($decoded)) {
                $meleeEntries = $decoded;
            }
        }

        $order->melee_entries = !empty($meleeEntries) ? $meleeEntries : null;

        if (!empty($meleeEntries)) {
            $first = $meleeEntries[0];
            $totalPieces = array_sum(array_column($meleeEntries, 'pieces'));
            $totalCarat = 0;
            foreach ($meleeEntries as $entry) {
                $totalCarat += ($entry['pieces'] ?? 0) * ($entry['avg_carat_per_piece'] ?? 0);
            }
            $order->melee_diamond_id = $first['melee_diamond_id'] ?? null;
            $order->melee_pieces = $totalPieces;
            $order->melee_carat = round($totalCarat, 3);
            $order->melee_price_per_ct = $first['price_per_ct'] ?? null;
        } else {
            $order->melee_diamond_id = !empty($validated['melee_diamond_id']) ? $validated['melee_diamond_id'] : null;
            $order->melee_pieces = !empty($validated['melee_pieces']) ? $validated['melee_pieces'] : null;
            $order->melee_carat = !empty($validated['melee_carat']) ? $validated['melee_carat'] : null;
            $order->melee_price_per_ct = !empty($validated['melee_price_per_ct']) ? $validated['melee_price_per_ct'] : null;
        }

        if ($order->melee_carat && $order->melee_price_per_ct) {
            $order->melee_total_value = $order->melee_carat * $order->melee_price_per_ct;
        } else {
            $order->melee_total_value = 0;
        }

        $order->diamond_status = !empty($validated['diamond_status']) ? $validated['diamond_status'] : null;
        $order->note = !empty($validated['note']) ? $validated['note'] : null;

        $order->gross_sell = $validated['gross_sell'] ?? 0;

        $admin = Auth::guard('admin')->user();
        if (array_key_exists('gold_net_weight', $validated)) {
            if ($admin->is_super || $admin->hasPermission('orders.add_gold_weight')) {
                $order->gold_net_weight = $validated['gold_net_weight'];
            }
        }

        $order->dispatch_date = !empty($validated['dispatch_date']) ? $validated['dispatch_date'] : null;
    }

    /**
     * Normalize payment fields into a consistent summary.
     */
    private function normalizePaymentSummary(array $validated, float $grossSell): array
    {
        $status = $validated['payment_status'] ?? null;
        $received = array_key_exists('amount_received', $validated) && $validated['amount_received'] !== ''
            ? round((float) $validated['amount_received'], 2)
            : null;
        $due = array_key_exists('amount_due', $validated) && $validated['amount_due'] !== ''
            ? round((float) $validated['amount_due'], 2)
            : null;

        if ($grossSell <= 0) {
            return [
                'payment_status' => 'full',
                'amount_received' => 0.0,
                'amount_due' => 0.0,
            ];
        }

        if ($status === null) {
            if ($received === null && $due === null) {
                $status = 'full';
            } elseif (($received ?? 0) <= 0 && ($due ?? 0) > 0) {
                $status = 'due';
            } elseif (($received ?? 0) > 0 && ($received ?? 0) < $grossSell) {
                $status = 'partial';
            } else {
                $status = 'full';
            }
        }

        if ($status === 'full') {
            $received = $grossSell;
            $due = 0.0;
        } elseif ($status === 'due') {
            $received = 0.0;
            $due = $due !== null ? max(0, $due) : $grossSell;
        } elseif ($status === 'custom') {
            if ($received === null) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Amount received is required when payment status is custom.',
                ]);
            }

            $received = max(0, min($received, $grossSell));
            $due = round(max($grossSell - $received, 0), 2);
            $status = $received <= 0
                ? 'due'
                : ($due > 0 ? 'partial' : 'full');
        } else {
            if ($received === null) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Amount received is required when payment status is partial.',
                ]);
            }

            if ($received <= 0 || $received >= $grossSell) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Partial payment must be greater than 0 and less than the gross sell amount.',
                ]);
            }

            $received = max(0, min($received, $grossSell));
            $computedDue = round(max($grossSell - $received, 0), 2);
            if ($due !== null && abs($due - $computedDue) > 0.01) {
                throw ValidationException::withMessages([
                    'amount_due' => 'Amount due must equal gross sell minus amount received.',
                ]);
            }
            $due = $computedDue;
        }

        return [
            'payment_status' => $status,
            'amount_received' => round((float) $received, 2),
            'amount_due' => round((float) $due, 2),
        ];
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
                if (!$file->isValid()) {
                    Log::error("Invalid file upload: {$file->getClientOriginalName()}");
                    continue;
                }

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $timestamp = time();
                $uniqueId = uniqid();

                $publicId = "{$folder}/{$timestamp}_{$uniqueId}";

                $uploadOptions = [
                    'public_id' => $publicId,
                    'folder' => $folder,
                ];

                Log::info("Uploading to Cloudinary", [
                    'file' => $file->getClientOriginalName(),
                    'type' => $isPdf ? 'PDF' : 'Image',
                    'size' => $file->getSize()
                ]);

                $uploadApi = $this->cloudinary->uploadApi();

                if ($isPdf) {
                    $uploadOptions['resource_type'] = 'raw';
                    $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);
                } else {
                    $uploadOptions['transformation'] = [
                        'quality' => 'auto:good',
                        'fetch_format' => 'auto'
                    ];
                    $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);
                }

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
}
