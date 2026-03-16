<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJewelleryStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jewelleryStock = $this->route('jewellery_stock');
        $jewelleryStockId = $jewelleryStock instanceof \App\Models\JewelleryStock
            ? $jewelleryStock->id
            : $jewelleryStock;

        return [
            'sku' => ['required', 'string', "unique:jewellery_stocks,sku,{$jewelleryStockId}", 'max:255'],
            'type' => 'required|in:ring,earrings,tennis_bracelet,other',
            'name' => 'required|string|max:255',
            'metal_type_id' => 'required|exists:metal_types,id',
            'ring_size_id' => 'nullable|exists:ring_sizes,id',
            'weight' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'image_url' => 'nullable|string|max:500',
            'image_upload' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
