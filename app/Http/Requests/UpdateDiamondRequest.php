<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiamondRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $diamondId = $this->route('diamond')?->id ?? null;

        return [
            'lot_no' => ['required', 'string', "unique:diamonds,lot_no,{$diamondId}", 'max:255'],
            'sku' => ['required', 'string', "unique:diamonds,sku,{$diamondId}", 'max:255'],
            'material' => 'nullable|string|max:255',
            'cut' => 'nullable|string|max:255',
            'clarity' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'shape' => 'nullable|string|max:255',
            'measurement' => 'nullable|string|max:255',
            'margin' => 'required|numeric|min:0',
            'listing_price' => 'nullable|numeric|min:0',
            'offer_calculation' => 'nullable|numeric|min:0|max:100',
            'actual_listing_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'per_ct' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'sold_out_date' => 'nullable|date',
            'is_sold_out' => 'nullable|string|in:IN Stock,Sold',
            'duration_days' => 'nullable|integer|min:0',
            'duration_price' => 'nullable|numeric|min:0',
            'sold_out_price' => 'nullable|numeric|min:0',
            'profit' => 'nullable|numeric',
            'sold_out_month' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'admin_id' => 'nullable|exists:admins,id',
            'note' => 'nullable|string',
            'diamond_type' => 'nullable|string|max:255',
            'multi_img_upload' => 'nullable|array',
            'multi_img_upload.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
