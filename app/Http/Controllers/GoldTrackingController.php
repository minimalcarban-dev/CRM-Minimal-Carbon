<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\GoldPurchase;
use App\Models\GoldDistribution;
use App\Models\GoldRateSnapshot;
use App\Models\Expense;
use App\Models\Party;
use App\Services\AuditLogger;
use App\Services\GoldRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GoldTrackingController extends Controller
{
    private const OUTLIER_MIN_FACTOR = 0.70;
    private const OUTLIER_MAX_FACTOR = 1.30;
    private const ABSOLUTE_MIN_RATE = 1000.00;
    private const ABSOLUTE_MAX_RATE = 20000.00;

    public function __construct(
        protected GoldRateService $goldRateService
    ) {
    }

    /**
     * Display the main gold tracking dashboard.
     */
    public function index(Request $request)
    {
        // Build unified query for transactions (purchases + distributions)
        $purchasesQuery = GoldPurchase::with('admin')
            ->select(
                'id',
                'purchase_date as date',
                DB::raw("'purchase' as transaction_type"),
                'weight_grams',
                'supplier_name as from_to',
                'total_amount as amount',
                'status',
                'admin_id',
                'created_at'
            );

        $distributionsQuery = GoldDistribution::with(['admin', 'factory'])
            ->select(
                'id',
                'distribution_date as date',
                DB::raw("CONCAT('distribution_', type) as transaction_type"),
                'weight_grams',
                DB::raw("factory_id as from_to"),
                DB::raw("NULL as amount"),
                DB::raw("'completed' as status"),
                'admin_id',
                'created_at'
            );

        // Apply filters
        if ($request->filled('from_date')) {
            $purchasesQuery->whereDate('purchase_date', '>=', $request->from_date);
            $distributionsQuery->whereDate('distribution_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $purchasesQuery->whereDate('purchase_date', '<=', $request->to_date);
            $distributionsQuery->whereDate('distribution_date', '<=', $request->to_date);
        }
        if ($request->filled('type')) {
            if ($request->type === 'purchase') {
                $distributionsQuery->whereRaw('1 = 0'); // Exclude distributions
            } elseif ($request->type === 'distribute') {
                $purchasesQuery->whereRaw('1 = 0'); // Exclude purchases
                $distributionsQuery->where('type', 'out');
            } elseif ($request->type === 'return') {
                $purchasesQuery->whereRaw('1 = 0');
                $distributionsQuery->where('type', 'return');
            } elseif ($request->type === 'consumed') {
                $purchasesQuery->whereRaw('1 = 0');
                $distributionsQuery->where('type', 'consumed');
            }
        }
        if ($request->filled('factory_id')) {
            $purchasesQuery->whereRaw('1 = 0');
            $distributionsQuery->where('factory_id', $request->factory_id);
        }

        // Get purchases for table (we'll handle separately for proper relations)
        $purchases = GoldPurchase::with('admin')
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('purchase_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('purchase_date', '<=', $request->to_date))
            ->when($request->type === 'distribute' || $request->type === 'return', fn($q) => $q->whereRaw('1 = 0'))
            ->latest('purchase_date')
            ->get();

        $distributions = GoldDistribution::with(['admin', 'factory', 'order'])
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('distribution_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('distribution_date', '<=', $request->to_date))
            ->when($request->type === 'purchase', fn($q) => $q->whereRaw('1 = 0'))
            ->when($request->type === 'distribute', fn($q) => $q->where('type', 'out'))
            ->when($request->type === 'return', fn($q) => $q->where('type', 'return'))
            ->when($request->type === 'consumed', fn($q) => $q->where('type', 'consumed'))
            ->when($request->filled('factory_id'), fn($q) => $q->where('factory_id', $request->factory_id))
            ->latest('distribution_date')
            ->get();

        // Merge and sort by date (purchases first, then distributions by created_at)
        $transactions = collect();
        foreach ($purchases as $p) {
            $transactions->push([
                'id' => $p->id,
                'date' => $p->purchase_date,
                'type' => 'purchase',
                'weight' => $p->weight_grams,
                'from_to' => $p->supplier_name,
                'amount' => $p->total_amount,
                'status' => $p->status,
                'admin' => $p->admin,
                'model' => $p,
                'created_at' => $p->created_at,
                'sort_priority' => 0, // Purchases show first within same date
            ]);
        }
        foreach ($distributions as $d) {
            $type = $d->type === 'out' ? 'distribute' : ($d->type === 'consumed' ? 'consumed' : 'return');
            $transactions->push([
                'id' => $d->id,
                'date' => $d->distribution_date,
                'type' => $type,
                'weight' => $d->weight_grams,
                'order_id' => $d->order_id,
                'from_to' => $d->factory->name ?? 'Unknown',
                'amount' => null,
                'status' => 'completed',
                'admin' => $d->admin,
                'model' => $d,
                'created_at' => $d->created_at,
                'sort_priority' => 1, // Distributions show after purchases
            ]);
        }
        
        // Sort: by date descending, then by priority (purchases first), then by created_at descending
        $transactions = $transactions->sort(function ($a, $b) {
            // First compare dates (descending)
            $aTime = $a['date'] instanceof \Carbon\Carbon ? $a['date']->timestamp : (is_string($a['date']) ? strtotime($a['date']) : 0);
            $bTime = $b['date'] instanceof \Carbon\Carbon ? $b['date']->timestamp : (is_string($b['date']) ? strtotime($b['date']) : 0);
            $dateCompare = $bTime - $aTime;
            
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            // Same date: purchases first (priority 0 before 1)
            if ($a['sort_priority'] !== $b['sort_priority']) {
                return $a['sort_priority'] - $b['sort_priority'];
            }
            // Same type: sort by created_at descending
            $aCreatedAt = $a['created_at'] instanceof \Carbon\Carbon ? $a['created_at']->timestamp : (is_string($a['created_at']) ? strtotime($a['created_at']) : 0);
            $bCreatedAt = $b['created_at'] instanceof \Carbon\Carbon ? $b['created_at']->timestamp : (is_string($b['created_at']) ? strtotime($b['created_at']) : 0);
            return $bCreatedAt - $aCreatedAt;
        })->values();

        // Stats
        $ownerStock = GoldDistribution::getAvailableOwnerStock();
        $inFactories = GoldDistribution::getTotalInFactories();
        $historicalSpend = (float) GoldPurchase::completed()->sum('total_amount');
        $thisMonth = GoldPurchase::getThisMonthPurchases();
        $liveRateResponse = $this->goldRateService->getRateForDate(now()->toDateString());
        $liveRatePerGram = (float) ($liveRateResponse['rate_inr_per_gram'] ?? 0);

        if ($liveRatePerGram <= 0) {
            $latestSnapshot = GoldRateSnapshot::query()->latest('rate_date')->first();
            if ($latestSnapshot) {
                $liveRatePerGram = (float) $latestSnapshot->inr_per_gram;
            }
        }

        $totalGold = $ownerStock + $inFactories;
        $totalValue = round($totalGold * $liveRatePerGram, 2);

        // Factories for filter and factory cards
        $factories = Factory::active()->orderBy('name')->get();
        foreach ($factories as $factory) {
            $factory->gold_stock = $factory->current_stock;
        }

        return view('gold-tracking.index', compact(
            'transactions',
            'ownerStock',
            'inFactories',
            'totalValue',
            'historicalSpend',
            'thisMonth',
            'factories',
            'liveRateResponse'
        ));
    }

    /**
     * Get INR gold rate for selected date.
     */
    public function rate(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $payload = $this->goldRateService->getRateForDate($validated['date']);

        $status = 200;
        if (($payload['success'] ?? false) === false) {
            $status = 422;
        }

        return response()->json($payload, $status);
    }

    /**
     * Show form for creating a new gold purchase.
     */
    public function createPurchase()
    {
        // Load only Gold Metal category parties as suppliers
        $suppliers = Party::byCategory(Party::CATEGORY_GOLD_METAL)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email', 'gst_no', 'address']);

        return view('gold-tracking.purchase-create', compact('suppliers'));
    }

    /**
     * Store a newly created gold purchase.
     */
    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'weight_grams' => 'required|numeric|min:0.001',
            'rate_per_gram' => 'required|numeric|min:0',
            'confirm_outlier_rate' => 'nullable|boolean',
            'party_id' => 'nullable|exists:parties,id',
            'supplier_name' => 'required|string|max:255',
            'supplier_mobile' => 'nullable|string|max:20',
            'invoice_number' => 'nullable|string|max:255',
            'payment_mode' => 'nullable|in:cash,bank_transfer',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'invoice_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $this->ensureOutlierRateConfirmation(
            $validated['purchase_date'],
            (float) $validated['rate_per_gram'],
            (bool) ($validated['confirm_outlier_rate'] ?? false)
        );
        unset($validated['confirm_outlier_rate']);

        $validated['admin_id'] = Auth::guard('admin')->id();

        // Handle invoice image upload to Cloudinary
        if ($request->hasFile('invoice_image')) {
            try {
                $uploadedFile = $request->file('invoice_image');
                $result = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'invoices/gold-purchases',
                    'resource_type' => 'auto',
                ]);

                $validated['invoice_image'] = [
                    'url' => $result->getSecurePath(),
                    'public_id' => $result->getPublicId(),
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'format' => $uploadedFile->getClientOriginalExtension(),
                    'size' => $uploadedFile->getSize(),
                    'resource_type' => $result->getFileType(),  
                    'uploaded_at' => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                // Log error but continue without image
                Log::error('Cloudinary upload failed for gold purchase: ' . $e->getMessage());
            }
        }

        // Determine status based on payment_mode
        if (empty($validated['payment_mode'])) {
            $validated['status'] = GoldPurchase::STATUS_PENDING;
            $message = 'Gold purchase saved as Pending. Complete payment details later.';
        } else {
            $validated['status'] = GoldPurchase::STATUS_COMPLETED;
            $message = 'Gold purchase recorded successfully!';
        }

        // Use transaction for completed purchases (creates expense too)
        $purchase = DB::transaction(function () use ($validated) {
            $purchase = GoldPurchase::create($validated);

            // If completed, create expense entry
            if ($purchase->isCompleted()) {
                $this->createExpenseFromGoldPurchase($purchase);
            }

            return $purchase;
        });

        return redirect()->route('gold-tracking.index')
            ->with('success', $message);
    }

    /**
     * Show a specific gold purchase.
     */
    public function showPurchase(GoldPurchase $purchase)
    {
        $purchase->load('admin', 'expense');
        return view('gold-tracking.purchase-show', compact('purchase'));
    }

    /**
     * Show form for editing a gold purchase.
     */
    public function editPurchase(GoldPurchase $purchase)
    {
        // Load only Gold Metal category parties as suppliers
        $suppliers = Party::byCategory(Party::CATEGORY_GOLD_METAL)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email', 'gst_no', 'address']);

        return view('gold-tracking.purchase-edit', compact('purchase', 'suppliers'));
    }

    /**
     * Update a gold purchase.
     */
    public function updatePurchase(Request $request, GoldPurchase $purchase)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'weight_grams' => 'required|numeric|min:0.001',
            'rate_per_gram' => 'required|numeric|min:0',
            'confirm_outlier_rate' => 'nullable|boolean',
            'party_id' => 'nullable|exists:parties,id',
            'supplier_name' => 'required|string|max:255',
            'supplier_mobile' => 'nullable|string|max:20',
            'invoice_number' => 'nullable|string|max:255',
            'payment_mode' => 'nullable|in:cash,bank_transfer',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'invoice_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'remove_invoice_image' => 'nullable|boolean',
        ]);

        $this->ensureOutlierRateConfirmation(
            $validated['purchase_date'],
            (float) $validated['rate_per_gram'],
            (bool) ($validated['confirm_outlier_rate'] ?? false)
        );
        unset($validated['confirm_outlier_rate']);

        // Handle invoice image
        if ($request->input('remove_invoice_image') && $purchase->invoice_image_public_id) {
            try {
                Cloudinary::destroy($purchase->invoice_image_public_id);
            } catch (\Exception $e) {
                Log::error('Cloudinary delete failed: ' . $e->getMessage());
            }
            $validated['invoice_image'] = null;
        } elseif ($request->hasFile('invoice_image')) {
            // Delete old image if exists
            if ($purchase->invoice_image_public_id) {
                try {
                    Cloudinary::destroy($purchase->invoice_image_public_id);
                } catch (\Exception $e) {
                    Log::error('Cloudinary delete failed: ' . $e->getMessage());
                }
            }

            try {
                $uploadedFile = $request->file('invoice_image');
                $result = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'invoices/gold-purchases',
                    'resource_type' => 'auto',
                ]);

                $validated['invoice_image'] = [
                    'url' => $result->getSecurePath(),
                    'public_id' => $result->getPublicId(),
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'format' => $uploadedFile->getClientOriginalExtension(),
                    'size' => $uploadedFile->getSize(),
                    'resource_type' => $result->getFileType(),
                    'uploaded_at' => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                Log::error('Cloudinary upload failed: ' . $e->getMessage());
                unset($validated['invoice_image']);
            }
        } else {
            unset($validated['invoice_image']);
        }
        unset($validated['remove_invoice_image']);

        $wasPending = $purchase->isPending();
        $hasPaymentNow = !empty($validated['payment_mode']);

        DB::transaction(function () use ($purchase, $validated, $wasPending, $hasPaymentNow) {
            // If was pending and now has payment mode, complete it
            if ($wasPending && $hasPaymentNow) {
                $validated['status'] = GoldPurchase::STATUS_COMPLETED;
            }

            $purchase->update($validated);

            // Create expense if just completed (pending -> completed)
            if ($wasPending && $hasPaymentNow && $purchase->isCompleted()) {
                $this->createExpenseFromGoldPurchase($purchase);
            }
            // Sync existing expense if already completed and has linked expense
            elseif (!$wasPending && $purchase->isCompleted() && $purchase->expense_id) {
                $this->syncExpenseWithGoldPurchase($purchase);
            }
        });

        $message = $purchase->isCompleted()
            ? 'Gold purchase updated successfully!'
            : 'Gold purchase updated. Add payment details to complete.';

        return redirect()->route('gold-tracking.index')
            ->with('success', $message);
    }

    /**
     * Complete a pending gold purchase with payment details.
     */
    public function completePurchase(Request $request, GoldPurchase $purchase)
    {
        if (!$purchase->isPending()) {
            return redirect()->route('gold-tracking.index')
                ->with('error', 'This purchase is already completed.');
        }

        $validated = $request->validate([
            'payment_mode' => 'required|in:cash,bank_transfer',
            'bank_account_name' => 'nullable|required_if:payment_mode,bank_transfer|string|max:255',
            'bank_name' => 'nullable|required_if:payment_mode,bank_transfer|string|max:255',
            'bank_account_number' => 'nullable|required_if:payment_mode,bank_transfer|string|max:50',
            'bank_ifsc' => 'nullable|required_if:payment_mode,bank_transfer|string|max:20',
        ]);

        DB::transaction(function () use ($purchase, $validated) {
            $purchase->update([
                'status' => GoldPurchase::STATUS_COMPLETED,
                'payment_mode' => $validated['payment_mode'],
                'bank_account_name' => $validated['bank_account_name'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_ifsc' => $validated['bank_ifsc'] ?? null,
            ]);

            $this->createExpenseFromGoldPurchase($purchase);
        });

        return redirect()->route('gold-tracking.index')
            ->with('success', 'Gold purchase completed successfully! Expense entry created.');
    }

    /**
     * Delete a gold purchase.
     */
    public function destroyPurchase(GoldPurchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // Delete linked expense if exists
            if ($purchase->expense_id) {
                Expense::where('id', $purchase->expense_id)->delete();
            }

            $purchase->delete();
        });

        return redirect()->route('gold-tracking.index')
            ->with('success', 'Gold purchase deleted successfully!');
    }

    /**
     * Show form for distributing gold to a factory.
     */
    public function distribute()
    {
        $availableStock = GoldDistribution::getAvailableOwnerStock();
        $factories = Factory::active()->orderBy('name')->get();

        // Add current stock to each factory
        foreach ($factories as $factory) {
            $factory->gold_stock = $factory->current_stock;
        }

        return view('gold-tracking.distribute', compact('availableStock', 'factories'));
    }

    /**
     * Store a new gold distribution to factory.
     */
    public function storeDistribution(Request $request)
    {
        $availableStock = GoldDistribution::getAvailableOwnerStock();

        $validated = $request->validate([
            'distribution_date' => 'required|date',
            'factory_id' => 'required|exists:factories,id',
            'weight_grams' => ['required', 'numeric', 'min:0.001', "max:$availableStock"],
            'purpose' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'weight_grams.max' => "Cannot distribute more than available stock ({$availableStock} gm).",
        ]);

        $validated['type'] = GoldDistribution::TYPE_OUT;
        $validated['admin_id'] = Auth::guard('admin')->id();

        GoldDistribution::create($validated);

        $factory = Factory::find($validated['factory_id']);

        return redirect()->route('gold-tracking.index')
            ->with('success', "Gold ({$validated['weight_grams']} gm) distributed to {$factory->name} successfully!");
    }

    /**
     * Show form for returning gold from a factory.
     */
    public function returnGold()
    {
        $factories = Factory::active()->orderBy('name')->get();

        // Only show factories with stock
        $factoriesWithStock = $factories->filter(function ($factory) {
            $factory->gold_stock = $factory->current_stock;
            return $factory->gold_stock > 0;
        });

        return view('gold-tracking.return', compact('factoriesWithStock'));
    }

    /**
     * Store a gold return from factory.
     */
    public function storeReturn(Request $request)
    {
        $factory = Factory::findOrFail($request->factory_id);
        $factoryStock = $factory->current_stock;

        $validated = $request->validate([
            'distribution_date' => 'required|date',
            'factory_id' => 'required|exists:factories,id',
            'weight_grams' => ['required', 'numeric', 'min:0.001', "max:$factoryStock"],
            'purpose' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'weight_grams.max' => "Cannot return more than factory has ({$factoryStock} gm).",
        ]);

        $validated['type'] = GoldDistribution::TYPE_RETURN;
        $validated['admin_id'] = Auth::guard('admin')->id();

        GoldDistribution::create($validated);

        return redirect()->route('gold-tracking.index')
            ->with('success', "Gold ({$validated['weight_grams']} gm) returned from {$factory->name} successfully!");
    }

    /**
     * Show suspicious gold purchase rates for manual review.
     */
    public function suspiciousRates()
    {
        $todayRatePayload = $this->goldRateService->getRateForDate(now()->toDateString());
        $todayRate = (float) ($todayRatePayload['rate_inr_per_gram'] ?? 0);

        $snapshotsByDate = GoldRateSnapshot::query()
            ->pluck('inr_per_gram', 'rate_date')
            ->map(fn($rate) => (float) $rate);

        $items = GoldPurchase::query()
            ->completed()
            ->with('admin')
            ->latest('purchase_date')
            ->get()
            ->map(function (GoldPurchase $purchase) use ($snapshotsByDate, $todayRate) {
                $purchaseDate = $purchase->purchase_date?->toDateString();
                $snapshotRate = $purchaseDate ? ($snapshotsByDate[$purchaseDate] ?? null) : null;
                $referenceRate = $snapshotRate ?? ($todayRate > 0 ? $todayRate : null);
                $referenceSource = $snapshotRate ? 'date_snapshot' : ($todayRate > 0 ? 'today_reference' : null);

                $reason = [];
                $deviationPercent = null;

                $currentRate = (float) $purchase->rate_per_gram;
                if ($currentRate < self::ABSOLUTE_MIN_RATE || $currentRate > self::ABSOLUTE_MAX_RATE) {
                    $reason[] = sprintf('Outside absolute range ₹%.0f–₹%.0f/g', self::ABSOLUTE_MIN_RATE, self::ABSOLUTE_MAX_RATE);
                }

                if ($referenceRate) {
                    $deviationPercent = round((($currentRate - $referenceRate) / $referenceRate) * 100, 2);
                    if ($this->goldRateService->isOutlierRate($currentRate, $referenceRate, self::OUTLIER_MIN_FACTOR, self::OUTLIER_MAX_FACTOR)) {
                        $reason[] = sprintf('Deviation from reference rate (%.2f%%)', $deviationPercent);
                    }
                }

                if (empty($reason)) {
                    return null;
                }

                return [
                    'purchase' => $purchase,
                    'reference_rate' => $referenceRate,
                    'reference_source' => $referenceSource,
                    'deviation_percent' => $deviationPercent,
                    'reason' => implode(' | ', $reason),
                ];
            })
            ->filter()
            ->values();

        return view('gold-tracking.suspicious-rates', [
            'suspiciousItems' => $items,
            'todayRatePayload' => $todayRatePayload,
            'outlierMinFactor' => self::OUTLIER_MIN_FACTOR,
            'outlierMaxFactor' => self::OUTLIER_MAX_FACTOR,
        ]);
    }

    /**
     * Manually correct suspicious purchase rate with audit trail.
     */
    public function correctSuspiciousRate(Request $request, GoldPurchase $purchase)
    {
        $validated = $request->validate([
            'new_rate_per_gram' => 'required|numeric|min:0.01',
            'correction_note' => 'required|string|min:5|max:500',
        ]);

        $adminId = Auth::guard('admin')->id();

        DB::transaction(function () use ($purchase, $validated, $adminId) {
            $oldValues = [
                'rate_per_gram' => (float) $purchase->rate_per_gram,
                'total_amount' => (float) $purchase->total_amount,
            ];

            $purchase->rate_per_gram = (float) $validated['new_rate_per_gram'];
            $purchase->save();
            $purchase->refresh();

            if ($purchase->isCompleted() && $purchase->expense_id) {
                $this->syncExpenseWithGoldPurchase($purchase);
            }

            $newValues = [
                'rate_per_gram' => (float) $purchase->rate_per_gram,
                'total_amount' => (float) $purchase->total_amount,
                'correction_note' => $validated['correction_note'],
            ];

            AuditLogger::log(
                'gold_purchase_rate_corrected',
                $purchase,
                $adminId,
                $oldValues,
                $newValues
            );
        });

        return redirect()
            ->route('gold-tracking.suspicious-rates')
            ->with('success', 'Purchase rate corrected and audit log recorded.');
    }

    /**
     * Create an expense entry from a completed gold purchase.
     */
    protected function createExpenseFromGoldPurchase(GoldPurchase $purchase): void
    {
        // Don't create duplicate expense
        if ($purchase->expense_id) {
            return;
        }

        $expense = Expense::create([
            'date' => $purchase->purchase_date,
            'title' => "Gold Purchase - {$purchase->weight_grams}g (24K)",
            'amount' => $purchase->total_amount,
            'transaction_type' => 'out',
            'category' => 'gold_purchase',
            'payment_method' => $purchase->payment_mode,
            'paid_to_received_from' => $purchase->supplier_name,
            'reference_number' => $purchase->invoice_number,
            'notes' => "Auto-created from Gold Purchase #{$purchase->id}. " . ($purchase->notes ?? ''),
            'admin_id' => $purchase->admin_id,
            'gold_purchase_id' => $purchase->id,
        ]);

        // Link expense back to purchase
        $purchase->update(['expense_id' => $expense->id]);
    }

    /**
     * Sync expense record with gold purchase data when purchase is updated.
     */
    protected function syncExpenseWithGoldPurchase(GoldPurchase $purchase): void
    {
        if (!$purchase->expense_id) {
            return;
        }

        $expense = Expense::find($purchase->expense_id);
        if (!$expense) {
            return;
        }

        // Only sync if this expense was created from this purchase
        if ($expense->gold_purchase_id !== $purchase->id) {
            return;
        }

        $expense->update([
            'date' => $purchase->purchase_date,
            'title' => "Gold Purchase - {$purchase->weight_grams}g (24K)",
            'amount' => $purchase->total_amount,
            'payment_method' => $purchase->payment_mode,
            'paid_to_received_from' => $purchase->supplier_name,
            'reference_number' => $purchase->invoice_number,
            'notes' => "Auto-updated from Gold Purchase #{$purchase->id}. " . ($purchase->notes ?? ''),
        ]);
    }

    /**
     * Return factory gold stock as JSON (for order form real-time validation).
     */
    public function factoryStock(Factory $factory)
    {
        return response()->json([
            'factory_id' => $factory->id,
            'factory_name' => $factory->name,
            'current_stock' => round($factory->current_stock, 3),
        ]);
    }

    protected function ensureOutlierRateConfirmation(string $purchaseDate, float $enteredRate, bool $confirmed): void
    {
        $ratePayload = $this->goldRateService->getRateForDate($purchaseDate);
        if (!(bool) ($ratePayload['is_available'] ?? false)) {
            return;
        }

        $expectedRate = (float) ($ratePayload['rate_inr_per_gram'] ?? 0);
        if ($expectedRate <= 0) {
            return;
        }

        $isOutlier = $this->goldRateService->isOutlierRate(
            $enteredRate,
            $expectedRate,
            self::OUTLIER_MIN_FACTOR,
            self::OUTLIER_MAX_FACTOR
        );

        if ($isOutlier && !$confirmed) {
            throw ValidationException::withMessages([
                'rate_per_gram' => sprintf(
                    'Entered rate ₹%.2f/g is far from expected rate ₹%.2f/g for %s. Confirm to continue.',
                    $enteredRate,
                    $expectedRate,
                    $purchaseDate
                ),
                'confirm_outlier_rate' => 'Please confirm outlier rate before saving.',
            ]);
        }
    }
}
