<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:in,out',
            'category' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cash,upi,bank_transfer,cheque',
            'party_id' => 'nullable|exists:parties,id',
            'paid_to_received_from' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'invoice_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ];
    }
}
