<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Expense;
use App\Models\StoneType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with('admin')->latest('purchase_date');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('purchase_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('purchase_date', '<=', $request->to_date);
        }

        // Filter by diamond type
        if ($request->filled('diamond_type')) {
            $query->where('diamond_type', 'like', '%' . $request->diamond_type . '%');
        }

        // Filter by party name
        if ($request->filled('party_name')) {
            $query->where('party_name', 'like', '%' . $request->party_name . '%');
        }

        // Filter by payment mode
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        $purchases = $query->paginate(15)->withQueryString();

        // Summary stats (only count completed purchases for amount stats)
        $totalPurchases = Purchase::count();
        $pendingPurchases = Purchase::pending()->count();
        $totalAmount = Purchase::completed()->sum('total_price');
        $thisMonthAmount = Purchase::completed()
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('total_price');

        return view('purchases.index', compact(
            'purchases',
            'totalPurchases',
            'pendingPurchases',
            'totalAmount',
            'thisMonthAmount'
        ));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create()
    {
        $stoneTypes = StoneType::orderBy('name')->pluck('name', 'name');
        return view('purchases.create', compact('stoneTypes'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'diamond_type' => 'required|string|max:255',
            'per_ct_price' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0.01',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'payment_mode' => 'nullable|in:upi,cash,bank_transfer',
            'upi_id' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'party_name' => 'required|string|max:255',
            'party_mobile' => 'nullable|string|max:15',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['admin_id'] = Auth::guard('admin')->id();
        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;

        // Determine status based on payment_mode
        if (empty($validated['payment_mode'])) {
            $validated['status'] = Purchase::STATUS_PENDING;
            $message = 'Purchase saved as Pending. Complete payment details later.';
        } else {
            $validated['status'] = Purchase::STATUS_COMPLETED;
            $message = 'Purchase recorded successfully!';
        }

        // Use transaction for completed purchases (creates expense too)
        $purchase = DB::transaction(function () use ($validated) {
            $purchase = Purchase::create($validated);

            // If completed, create expense entry
            if ($purchase->isCompleted()) {
                $this->createExpenseFromPurchase($purchase);
            }

            return $purchase;
        });

        return redirect()->route('purchases.index')
            ->with('success', $message);
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load('admin', 'expense');
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit(Purchase $purchase)
    {
        $stoneTypes = StoneType::orderBy('name')->pluck('name', 'name');
        return view('purchases.edit', compact('purchase', 'stoneTypes'));
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'diamond_type' => 'required|string|max:255',
            'per_ct_price' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0.01',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'payment_mode' => 'nullable|in:upi,cash,bank_transfer',
            'upi_id' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'party_name' => 'required|string|max:255',
            'party_mobile' => 'nullable|string|max:15',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;

        $wasPending = $purchase->isPending();
        $hasPaymentNow = !empty($validated['payment_mode']);

        // Use transaction for status changes
        DB::transaction(function () use ($purchase, $validated, $wasPending, $hasPaymentNow) {
            // If was pending and now has payment mode, complete it
            if ($wasPending && $hasPaymentNow) {
                $validated['status'] = Purchase::STATUS_COMPLETED;
            }

            $purchase->update($validated);

            // Create expense if just completed (pending -> completed)
            if ($wasPending && $hasPaymentNow && $purchase->isCompleted()) {
                $this->createExpenseFromPurchase($purchase);
            }
            // Sync existing expense if already completed and has linked expense
            elseif (!$wasPending && $purchase->isCompleted() && $purchase->expense_id) {
                $this->syncExpenseWithPurchase($purchase);
            }
        });

        $message = $purchase->isCompleted()
            ? 'Purchase updated successfully!'
            : 'Purchase updated. Add payment details to complete.';

        return redirect()->route('purchases.index')
            ->with('success', $message);
    }

    /**
     * Complete a pending purchase with payment details.
     */
    public function complete(Request $request, Purchase $purchase)
    {
        // Only pending purchases can be completed
        if (!$purchase->isPending()) {
            return redirect()->route('purchases.index')
                ->with('error', 'This purchase is already completed.');
        }

        $validated = $request->validate([
            'payment_mode' => 'required|in:upi,cash,bank_transfer',
            'upi_id' => 'nullable|required_if:payment_mode,upi|string|max:255',
            'bank_account_name' => 'nullable|required_if:payment_mode,bank_transfer|string|max:255',
            'bank_name' => 'nullable|required_if:payment_mode,bank_transfer|string|max:255',
            'bank_account_number' => 'nullable|required_if:payment_mode,bank_transfer|string|max:50',
            'bank_ifsc' => 'nullable|required_if:payment_mode,bank_transfer|string|max:20',
        ]);

        // Use transaction
        DB::transaction(function () use ($purchase, $validated) {
            $purchase->update([
                'status' => Purchase::STATUS_COMPLETED,
                'payment_mode' => $validated['payment_mode'],
                'upi_id' => $validated['upi_id'] ?? null,
                'bank_account_name' => $validated['bank_account_name'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_ifsc' => $validated['bank_ifsc'] ?? null,
            ]);

            $this->createExpenseFromPurchase($purchase);
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase completed successfully! Expense entry created.');
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // Also delete linked expense if exists
            if ($purchase->expense_id) {
                Expense::where('id', $purchase->expense_id)->delete();
            }

            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully!');
    }

    /**
     * Sync expense record with purchase data when purchase is updated.
     * Only updates if the expense was auto-created from this purchase.
     */
    protected function syncExpenseWithPurchase(Purchase $purchase): void
    {
        if (!$purchase->expense_id) {
            return;
        }

        $expense = Expense::find($purchase->expense_id);
        if (!$expense) {
            return;
        }

        // Only sync if this expense was created from this purchase
        if ($expense->purchase_id !== $purchase->id) {
            return;
        }

        $expense->update([
            'date' => $purchase->purchase_date,
            'title' => "Diamond Purchase - {$purchase->diamond_type}",
            'amount' => $purchase->total_price,
            'payment_method' => $purchase->payment_mode,
            'paid_to_received_from' => $purchase->party_name,
            'reference_number' => $purchase->invoice_number,
            'notes' => "Auto-updated from Purchase #{$purchase->id}. " . ($purchase->notes ?? ''),
        ]);
    }

    /**
     * Create an expense entry from a completed purchase.
     */
    protected function createExpenseFromPurchase(Purchase $purchase): void
    {
        // Don't create duplicate expense
        if ($purchase->expense_id) {
            return;
        }

        $expense = Expense::create([
            'date' => $purchase->purchase_date,
            'title' => "Diamond Purchase - {$purchase->diamond_type}",
            'amount' => $purchase->total_price,
            'transaction_type' => 'out',
            'category' => 'weight_diamond',
            'payment_method' => $purchase->payment_mode,
            'paid_to_received_from' => $purchase->party_name,
            'reference_number' => $purchase->invoice_number,
            'notes' => "Auto-created from Purchase #{$purchase->id}. " . ($purchase->notes ?? ''),
            'admin_id' => $purchase->admin_id,
            'purchase_id' => $purchase->id,
        ]);

        // Link expense back to purchase
        $purchase->update(['expense_id' => $expense->id]);
    }
}

