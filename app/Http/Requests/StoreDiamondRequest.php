<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'stockid' => 'required|integer|unique:diamonds,stockid',
            'sku' => 'required|string|unique:diamonds,sku|max:255',
            'price' => 'required|numeric|min:0',
            'listing_price' => 'nullable|numeric|min:0',
            'cut' => 'nullable|string|max:255',
            'shape' => 'nullable|string|max:255',
            'measurement' => 'nullable|string|max:255',
            'number_of_pics' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'admin_id' => 'nullable|exists:admins,id',
            'note' => 'nullable|string',
            'diamond_type' => 'nullable|string|max:255',
            'multi_img_upload' => 'nullable|array',
            'multi_img_upload.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
