<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_no' => 'required|string|unique:invoices,invoice_no',
            'invoice_date' => 'required|date',
            'company_id' => 'required|exists:companies,id',
            'invoice_type' => 'required|in:proforma,tax',
            'copy_type' => 'nullable|in:original,duplicate,triplicate',
            'place_of_supply' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'include_terms_conditions' => 'nullable|boolean',
            'billed_to_id' => 'nullable|exists:parties,id',
            'shipped_to_id' => 'nullable|exists:parties,id',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.unit' => 'nullable|in:pieces,carats',
            'items.*.rate' => 'nullable|numeric',
            'items.*.amount' => 'nullable|numeric',
        ];
    }
}
