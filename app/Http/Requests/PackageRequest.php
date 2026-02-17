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
