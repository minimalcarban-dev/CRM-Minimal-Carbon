<?php

namespace App\Http\Requests;

class UpdateExpenseRequest extends StoreExpenseRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['remove_invoice_image'] = 'nullable|boolean';
        return $rules;
    }
}
