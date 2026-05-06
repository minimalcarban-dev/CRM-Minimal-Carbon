<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class StoreDiamondRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lot_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('diamonds', 'lot_no')->whereNull('deleted_at')
            ],
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('diamonds', 'sku')->whereNull('deleted_at')
            ],
            'material' => 'nullable|string|max:255',
            'cut' => 'nullable|string|max:255',
            'clarity' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'shape' => 'nullable|string|max:255',
            'measurement' => 'nullable|string|max:255',
            'margin' => 'nullable|numeric|min:0',
            'listing_price' => 'nullable|numeric|min:0',
            'offer_calculation' => 'nullable|numeric|min:0|max:100',
            'actual_listing_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'per_ct' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'sold_out_date' => 'nullable|date',
            'sold_out_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'admin_id' => 'nullable|exists:admins,id',
            'note' => 'nullable|string',
            'diamond_type' => 'nullable|string|max:255',
            'current_location' => 'nullable|string|max:255',
            'multi_img_upload' => 'nullable|array',
            'multi_img_upload.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
