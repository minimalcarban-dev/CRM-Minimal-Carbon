<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Admin only, already protected by middleware
    }

    public function rules(): array
    {
        return [
            'slip_id' => 'required|string|max:255|unique:packages,slip_id',
            'party_type' => 'required|string|max:50',
            'company_name' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            'purpose_of_handover' => 'required|string|max:500',
            'stock_id' => 'nullable|string|max:100|exists:diamonds,sku',
            'handover_location' => 'required|string|max:255',
            'handover_mode' => 'required|string|max:50',
            'diamond_shape' => 'nullable|string|max:100',
            'diamond_size' => 'nullable|string|max:100',
            'diamond_color' => 'nullable|string|max:50',
            'diamond_clarity' => 'nullable|string|max:50',
            'diamond_carat' => 'nullable|numeric|min:0',
            'person_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|regex:/^[0-9]{10,15}$/',
            'package_description' => 'required|string',
            'package_image' => 'nullable|image|max:5120', // 5MB max
            'issue_date' => 'required|date',
            'issue_time' => 'required',
            'return_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
        ];
    }
}
