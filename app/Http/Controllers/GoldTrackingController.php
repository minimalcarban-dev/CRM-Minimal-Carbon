<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use App\Models\GoldPurchase;
use App\Models\GoldDistribution;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoldTrackingController extends Controller
{
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

        $distributions = GoldDistribution::with(['admin', 'factory'])
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('distribution_date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('distribution_date', '<=', $request->to_date))
            ->when($request->type === 'purchase', fn($q) => $q->whereRaw('1 = 0'))
            ->when($request->type === 'distribute', fn($q) => $q->where('type', 'out'))
            ->when($request->type === 'return', fn($q) => $q->where('type', 'return'))
            ->when($request->filled('factory_id'), fn($q) => $q->where('factory_id', $request->factory_id))
            ->latest('distribution_date')
            ->get();

        // Merge and sort by date
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
            ]);
        }
        foreach ($distributions as $d) {
            $transactions->push([
                'id' => $d->id,
                'date' => $d->distribution_date,
                'type' => $d->type === 'out' ? 'distribute' : 'return',
                'weight' => $d->weight_grams,
                'from_to' => $d->factory->name ?? 'Unknown',
                'amount' => null,
                'status' => 'completed',
                'admin' => $d->admin,
                'model' => $d,
            ]);
        }
        $transactions = $transactions->sortByDesc('date')->values();

        // Stats
        $ownerStock = GoldDistribution::getAvailableOwnerStock();
        $inFactories = GoldDistribution::getTotalInFactories();
        $totalPurchased = GoldPurchase::getTotalPurchasedStock();
        $totalValue = GoldPurchase::completed()->sum('total_amount');
        $thisMonth = GoldPurchase::getThisMonthPurchases();

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
            'thisMonth',
            'factories'
        ));
    }

    /**
     * Show form for creating a new gold purchase.
     */
    public function createPurchase()
    {
        return view('gold-tracking.purchase-create');
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
            'supplier_name' => 'required|string|max:255',
            'supplier_mobile' => 'nullable|string|max:20',
            'invoice_number' => 'nullable|string|max:255',
            'payment_mode' => 'nullable|in:cash,bank_transfer',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $validated['admin_id'] = Auth::guard('admin')->id();

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
        return view('gold-tracking.purchase-edit', compact('purchase'));
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
            'supplier_name' => 'required|string|max:255',
            'supplier_mobile' => 'nullable|string|max:20',
            'invoice_number' => 'nullable|string|max:255',
            'payment_mode' => 'nullable|in:cash,bank_transfer',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

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
}
