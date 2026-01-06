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

        $invoices = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $parties = Party::orderBy('name')->get();
        return view('invoices.create', compact('companies', 'parties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_no' => 'required|string|unique:invoices,invoice_no',
            'invoice_date' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'invoice_type' => 'required|in:proforma,tax',
            'copy_type' => 'nullable|in:original,duplicate,triplicate',
            'place_of_supply' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'billed_to_id' => 'nullable|exists:parties,id',
            'shipped_to_id' => 'nullable|exists:parties,id',
            'items' => 'required|array|min:1',
            'items.*.carats' => 'nullable|numeric',
            'items.*.rate' => 'nullable|numeric',
            'items.*.amount' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create($request->only([
                'invoice_no',
                'invoice_date',
                'company_id',
                'invoice_type',
                'place_of_supply',
                'payment_terms',
                'billed_to_id',
                'shipped_to_id',
                'copy_type'
            ]));

            // Auto-set status based on invoice type
            $invoice->status = ($request->invoice_type === 'tax') ? 'done' : 'draft';

            $taxable = 0;
            foreach ($request->input('items') as $it) {
                $amount = isset($it['amount']) ? $it['amount'] : ((float) ($it['carats'] ?? 0) * (float) ($it['rate'] ?? 0));
                $taxable += $amount;

                $invoice->items()->create([
                    'description_of_goods' => $it['description_of_goods'] ?? null,
                    'hsn_code' => $it['hsn_code'] ?? null,
                    'pieces' => $it['pieces'] ?? null,
                    'carats' => $it['carats'] ?? null,
                    'rate' => $it['rate'] ?? null,
                    'amount' => $amount,
                ]);
            }

            // Simple tax calculation - rates can be passed in request or default to 0
            $cgst_rate = (float) $request->input('cgst_rate', 0);
            $sgst_rate = (float) $request->input('sgst_rate', 0);
            $igst_rate = (float) $request->input('igst_rate', 0);

            $company = Company::find($invoice->company_id);
            $igst = 0;
            $cgst = 0;
            $sgst = 0;
            if ($company && $company->state_code && $company->state_code == $invoice->place_of_supply) {
                $cgst = round($taxable * ($cgst_rate / 100), 2);
                $sgst = round($taxable * ($sgst_rate / 100), 2);
            } else {
                $igst = round($taxable * ($igst_rate / 100), 2);
            }

            $invoice->taxable_amount = $taxable;
            $invoice->igst_amount = $igst;
            $invoice->cgst_amount = $cgst;
            $invoice->sgst_amount = $sgst;
            $invoice->total_invoice_value = $taxable + $igst + $cgst + $sgst;
            $invoice->save();

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

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'invoice_no' => 'required|string|unique:invoices,invoice_no,' . $invoice->id,
            'invoice_date' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'invoice_type' => 'required|in:proforma,tax',
            'copy_type' => 'nullable|in:original,duplicate,triplicate',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $invoice->update($request->only([
                'invoice_no',
                'invoice_date',
                'company_id',
                'invoice_type',
                'place_of_supply',
                'payment_terms',
                'billed_to_id',
                'shipped_to_id',
                'copy_type'
            ]));

            // Auto-set status based on invoice type
            $invoice->status = ($request->invoice_type === 'tax') ? 'done' : 'draft';

            // remove old items and re-create
            $invoice->items()->delete();
            $taxable = 0;
            foreach ($request->input('items') as $it) {
                $amount = isset($it['amount']) ? $it['amount'] : ((float) ($it['carats'] ?? 0) * (float) ($it['rate'] ?? 0));
                $taxable += $amount;

                $invoice->items()->create([
                    'description_of_goods' => $it['description_of_goods'] ?? null,
                    'hsn_code' => $it['hsn_code'] ?? null,
                    'pieces' => $it['pieces'] ?? null,
                    'carats' => $it['carats'] ?? null,
                    'rate' => $it['rate'] ?? null,
                    'amount' => $amount,
                ]);
            }

            $cgst_rate = (float) $request->input('cgst_rate', 0);
            $sgst_rate = (float) $request->input('sgst_rate', 0);
            $igst_rate = (float) $request->input('igst_rate', 0);

            $company = Company::find($invoice->company_id);
            $igst = 0;
            $cgst = 0;
            $sgst = 0;
            if ($company && $company->state_code && $company->state_code == $invoice->place_of_supply) {
                $cgst = round($taxable * ($cgst_rate / 100), 2);
                $sgst = round($taxable * ($sgst_rate / 100), 2);
            } else {
                $igst = round($taxable * ($igst_rate / 100), 2);
            }

            $invoice->taxable_amount = $taxable;
            $invoice->igst_amount = $igst;
            $invoice->cgst_amount = $cgst;
            $invoice->sgst_amount = $sgst;
            $invoice->total_invoice_value = $taxable + $igst + $cgst + $sgst;
            $invoice->save();

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
}
