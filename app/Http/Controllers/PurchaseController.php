<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\StoneType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with('admin')->latest('purchase_date');

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

        // Summary stats
        $totalPurchases = Purchase::count();
        $totalAmount = Purchase::sum('total_price');
        $thisMonthAmount = Purchase::whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('total_price');

        return view('purchases.index', compact(
            'purchases',
            'totalPurchases',
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
            'payment_mode' => 'required|in:upi,cash',
            'upi_id' => 'nullable|string|max:255',
            'party_name' => 'required|string|max:255',
            'party_mobile' => 'nullable|string|max:15',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['admin_id'] = Auth::guard('admin')->id();
        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;

        // total_price is calculated automatically in model
        Purchase::create($validated);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase recorded successfully!');
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load('admin');
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
            'payment_mode' => 'required|in:upi,cash',
            'upi_id' => 'nullable|string|max:255',
            'party_name' => 'required|string|max:255',
            'party_mobile' => 'nullable|string|max:15',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;

        $purchase->update($validated);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully!');
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully!');
    }
}
