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
            'type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'metal_type_id' => 'required|exists:metal_types,id',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'ring_size_id' => 'nullable|exists:ring_sizes,id',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'diameter' => 'nullable|numeric|min:0',
            'bale_size' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:3000',
            'image_url' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|mimes:jpeg,png,jpg,webp,avif,gif,heic,heif,mp4,mov,avi,wmv|max:20480',
            'removed_images' => 'nullable|array',
            'removed_images.*' => 'nullable|string',
            'closure_type_id' => 'nullable|exists:closure_types,id',
            'primary_stone_type_id' => 'nullable|exists:stone_types,id',
            'primary_stone_weight' => 'nullable|numeric|min:0',
            'primary_stone_count' => 'nullable|integer|min:0',
            'primary_stone_shape_id' => 'nullable|exists:stone_shapes,id',
            'primary_stone_color_id' => 'nullable|exists:stone_colors,id',
            'primary_stone_clarity_id' => 'nullable|exists:diamond_clarities,id',
            'primary_stone_cut_id' => 'nullable|exists:diamond_cuts,id',
            'side_stones' => 'nullable|array',
            'side_stones.*.stone_type_id' => 'required|exists:stone_types,id',
            'side_stones.*.weight' => 'nullable|numeric|min:0',
            'side_stones.*.count' => 'nullable|integer|min:0',
            'side_stones.*.stone_shape_id' => 'nullable|exists:stone_shapes,id',
            'side_stones.*.stone_color_id' => 'nullable|exists:stone_colors,id',
            'side_stones.*.stone_clarity_id' => 'nullable|exists:diamond_clarities,id',
            'side_stones.*.stone_cut_id' => 'nullable|exists:diamond_cuts,id',
            'certificate_number' => 'nullable|string|max:255',
            'certificate_type' => 'nullable|string|max:100',
            'certificate_url' => 'nullable|string|max:2000',
            'pricing_variants' => 'nullable|array',
            'pricing_variants.*.net_weight_grams' => 'nullable|numeric|min:0',
            'pricing_variants.*.stone_cost' => 'nullable|numeric|min:0',
            'pricing_variants.*.extra_cost' => 'nullable|numeric|min:0',
            'pricing_variants.*.labor_rate_usd_per_gram' => 'nullable|numeric|min:0',
            'pricing_variants.*.commission_percent' => 'nullable|numeric|min:0|max:999',
            'pricing_variants.*.profit_percent' => 'nullable|numeric|min:0|max:999',
            'pricing_variants.*.sales_markup_percent' => 'nullable|numeric|min:0|max:999',
            'pricing_variants.*.is_default_listing' => 'nullable|boolean',
            'default_pricing_variant' => 'nullable|string|max:100',
            'platinum_950_rate_usd_per_gram' => 'nullable|numeric|min:0',
        ];
    }
}
