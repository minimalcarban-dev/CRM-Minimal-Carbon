<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for adding a new melee shape/size to a category.
 *
 * Extracted from MeleeDiamondController::addShape() — Sprint 3.
 *
 * Authorization: delegates to MeleeDiamondPolicy::create()
 */
class StoreMeleeRequest extends FormRequest
{
    /**
     * Determine if the admin is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\MeleeDiamond::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:melee_categories,id'],
            'shape'       => ['required', 'string', 'max:100'],
            'size'        => ['required', 'string', 'max:20', 'regex:/^[0-9.*x\s]+$/i'],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category does not exist.',
            'size.regex'         => 'Size may only contain numbers, dots, asterisks, and "x".',
        ];
    }
}
