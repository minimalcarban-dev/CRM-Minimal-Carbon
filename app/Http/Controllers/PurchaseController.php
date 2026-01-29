<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Expense;
use App\Models\StoneType;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

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

        // Load only Diamond & Gemstone category parties
        $parties = Party::byCategory(Party::CATEGORY_DIAMOND_GEMSTONE)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email', 'gst_no', 'address']);

        return view('purchases.create', compact('stoneTypes', 'parties'));
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
            'upi_id' => 'nullable|required_if:payment_mode,upi|string|max:255',
            'bank_account_name' => 'nullable|required_if:payment_mode,bank_transfer|string|max:255',
            'bank_name' => 'nullable|required_if:payment_mode,bank_transfer|string|max:255',
            'bank_account_number' => 'nullable|required_if:payment_mode,bank_transfer|string|max:50',
            'bank_ifsc' => 'nullable|required_if:payment_mode,bank_transfer|string|max:20',
            'party_id' => [
                'nullable',
                Rule::exists('parties', 'id')->where(fn ($q) => $q->where('category', Party::CATEGORY_DIAMOND_GEMSTONE)),
            ],
            'party_name' => 'required|string|max:255',
            'party_mobile' => 'nullable|string|max:15',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'invoice_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $validated['admin_id'] = Auth::guard('admin')->id();
        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;

        // If a party is selected, trust the DB record (prevents spoofed names/phones)
        if (!empty($validated['party_id'])) {
            $party = Party::query()
                ->byCategory(Party::CATEGORY_DIAMOND_GEMSTONE)
                ->find($validated['party_id']);

            if ($party) {
                $validated['party_name'] = $party->name;
                $validated['party_mobile'] = $party->phone;
            }
        }

        // Handle invoice image upload to Cloudinary
        if ($request->hasFile('invoice_image')) {
            try {
                $uploadedFile = $request->file('invoice_image');
                $result = Cloudinary::upload($uploadedFile->getRealPath(), [
                    'folder' => 'invoices/purchases',
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
                Log::error('Cloudinary upload failed for purchase: ' . $e->getMessage());
            }
        }

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

        // Load only Diamond & Gemstone category parties
        $parties = Party::byCategory(Party::CATEGORY_DIAMOND_GEMSTONE)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email', 'gst_no', 'address']);

        return view('purchases.edit', compact('purchase', 'stoneTypes', 'parties'));
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
            'upi_id' => 'nullable|required_if:payment_mode,upi|string|max:255',
            'bank_account_name' => 'nullable|required_if:payment_mode,bank_transfer|string|max:255',
            'bank_name' => 'nullable|required_if:payment_mode,bank_transfer|string|max:255',
            'bank_account_number' => 'nullable|required_if:payment_mode,bank_transfer|string|max:50',
            'bank_ifsc' => 'nullable|required_if:payment_mode,bank_transfer|string|max:20',
            'party_id' => [
                'nullable',
                Rule::exists('parties', 'id')->where(fn ($q) => $q->where('category', Party::CATEGORY_DIAMOND_GEMSTONE)),
            ],
            'party_name' => 'required|string|max:255',
            'party_mobile' => 'nullable|string|max:15',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'invoice_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'remove_invoice_image' => 'nullable|boolean',
        ]);

        $validated['discount_percent'] = $validated['discount_percent'] ?? 0;

        // If a party is selected, trust the DB record (prevents spoofed names/phones)
        if (!empty($validated['party_id'])) {
            $party = Party::query()
                ->byCategory(Party::CATEGORY_DIAMOND_GEMSTONE)
                ->find($validated['party_id']);

            if ($party) {
                $validated['party_name'] = $party->name;
                $validated['party_mobile'] = $party->phone;
            }
        }

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
                    'folder' => 'invoices/purchases',
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

        // If an expense already exists for this purchase (e.g., retry/double-submit), link it.
        $existing = Expense::query()->where('purchase_id', $purchase->id)->first();
        if ($existing) {
            $purchase->update(['expense_id' => $existing->id]);
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
