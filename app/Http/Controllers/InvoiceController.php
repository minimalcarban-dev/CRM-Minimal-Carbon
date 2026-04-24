<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Company;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('company');

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Region filter
        if ($region = $request->get('region')) {
            $query->where('invoice_region', $region);
        }

        // Region stats for cards
        $regionStats = Invoice::selectRaw('invoice_region, COUNT(*) as count, SUM(total_invoice_value) as total')
            ->groupBy('invoice_region')
            ->get()
            ->keyBy('invoice_region');

        $invoices = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return view('invoices.index', compact('invoices', 'regionStats'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $parties = Party::orderBy('name')->get();
        return view('invoices.create', compact('companies', 'parties'));
    }

    public function store(\App\Http\Requests\StoreInvoiceRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $invoiceData = $request->only([
                'invoice_no',
                'invoice_region',
                'invoice_date',
                'company_id',
                'invoice_type',
                'place_of_supply',
                'payment_terms',
                'billed_to_id',
                'shipped_to_id',
                'copy_type'
            ]);
            $invoiceData['include_terms_conditions'] = $request->boolean('include_terms_conditions');

            $invoice = Invoice::create($invoiceData);

            // Auto-set status based on invoice type
            $invoice->status = ($request->invoice_type === 'tax') ? 'done' : 'draft';

            $taxable = $this->syncInvoiceItems($invoice, $request->input('items'));
            $this->calculateAndApplyTax($invoice, $request, $taxable);

            DB::commit();

            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice created');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with(['company', 'items', 'billedTo', 'shippedTo'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        $companies = Company::orderBy('name')->get();
        $parties = Party::orderBy('name')->get();
        return view('invoices.edit', compact('invoice', 'companies', 'parties'));
    }

    public function update(\App\Http\Requests\UpdateInvoiceRequest $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $invoiceData = $request->only([
                'invoice_no',
                'invoice_region',
                'invoice_date',
                'company_id',
                'invoice_type',
                'place_of_supply',
                'payment_terms',
                'billed_to_id',
                'shipped_to_id',
                'copy_type',
                'express_shipping'
            ]);
            $invoiceData['include_terms_conditions'] = $request->boolean('include_terms_conditions');

            $invoice->update($invoiceData);

            // Auto-set status based on invoice type
            $invoice->status = ($request->invoice_type === 'tax') ? 'done' : 'draft';

            // remove old items and re-create
            $invoice->items()->delete();
            $taxable = $this->syncInvoiceItems($invoice, $request->input('items'));
            $this->calculateAndApplyTax($invoice, $request, $taxable);

            DB::commit();

            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function pdf($id)
    {
        $invoice = Invoice::with(['company', 'items', 'billedTo', 'shippedTo'])->findOrFail($id);
        return view('invoices.pdf', compact('invoice'));
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        DB::beginTransaction();
        try {
            // Delete related items first (handled by cascade, but being explicit)
            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();
            return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Create invoice line items and return the taxable total.
     */
    private function syncInvoiceItems(Invoice $invoice, array $items): float
    {
        $taxable = 0;

        foreach ($items as $it) {
            $quantity = (float) ($it['quantity'] ?? 0);
            $rate = (float) ($it['rate'] ?? 0);
            $unit = $it['unit'] ?? 'pieces';

            $amount = isset($it['amount']) ? $it['amount'] : ($quantity * $rate);
            $taxable += $amount;

            $invoice->items()->create([
                'description_of_goods' => $it['description_of_goods'] ?? null,
                'hsn_code'             => $it['hsn_code'] ?? null,
                'pieces'               => ($unit === 'pieces') ? $quantity : null,
                'carats'               => ($unit === 'carats') ? $quantity : null,
                'rate'                 => $rate,
                'amount'               => $amount,
            ]);
        }

        return $taxable;
    }

    /**
     * Calculate GST/IGST taxes and update the invoice totals.
     */
    private function calculateAndApplyTax(Invoice $invoice, Request $request, float $taxable): void
    {
        $cgstRate        = (float) $request->input('cgst_rate', 0);
        $sgstRate        = (float) $request->input('sgst_rate', 0);
        $igstRate        = (float) $request->input('igst_rate', 0);
        $expressShipping = (float) $request->input('express_shipping', 0);

        $company       = Company::find($invoice->company_id);
        $billedParty   = $invoice->billed_to_id ? Party::find($invoice->billed_to_id) : null;
        $isForeignParty = $billedParty && $billedParty->is_foreign;

        $igst = 0;
        $cgst = 0;
        $sgst = 0;

        // Skip tax calculation for foreign parties
        if (!$isForeignParty) {
            if ($company && $company->state_code && $company->state_code == $invoice->place_of_supply) {
                $cgst = round($taxable * ($cgstRate / 100), 2);
                $sgst = round($taxable * ($sgstRate / 100), 2);
            } else {
                $igst = round($taxable * ($igstRate / 100), 2);
            }
        }

        $invoice->taxable_amount     = $taxable;
        $invoice->igst_amount        = $igst;
        $invoice->cgst_amount        = $cgst;
        $invoice->sgst_amount        = $sgst;
        $invoice->express_shipping   = $expressShipping;
        $invoice->total_invoice_value = $taxable + $igst + $cgst + $sgst + $expressShipping;
        $invoice->save();
    }
}
