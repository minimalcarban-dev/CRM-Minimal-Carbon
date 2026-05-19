<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating an existing melee diamond (shape/size/last-transaction).
 *
 * Extracted from MeleeDiamondController::update() — Sprint 3.
 *
 * Authorization: delegates to MeleeDiamondPolicy::update()
 */
class UpdateMeleeRequest extends FormRequest
{
    /**
     * Determine if the admin is authorized to make this request.
     */
    public function authorize(): bool
    {
        $meleeDiamond = \App\Models\MeleeDiamond::findOrFail($this->route('id'));

        return $this->user()->can('update', $meleeDiamond);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shape'       => ['required', 'string', 'max:100'],
            'size'        => ['required', 'string', 'max:20', 'regex:/^[0-9.*x\s]+$/i'],
            'last_pieces' => ['nullable', 'integer', 'min:1'],
            'last_carats' => ['nullable', 'numeric', 'min:0'],
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
            'size.regex'         => 'Size may only contain numbers, dots, asterisks, and "x".',
            'last_pieces.min'    => 'Last pieces must be at least 1.',
            'last_carats.min'    => 'Carat weight cannot be negative.',
        ];
    }
}
