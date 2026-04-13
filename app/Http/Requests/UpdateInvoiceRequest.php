<?php

namespace App\Http\Requests;

class UpdateInvoiceRequest extends StoreInvoiceRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $invoiceId = $this->route('invoice');

        $rules['invoice_no'] = 'required|string|unique:invoices,invoice_no,' . $invoiceId;

        return $rules;
    }
}
