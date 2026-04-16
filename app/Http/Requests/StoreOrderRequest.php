<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'order_type' => 'required|in:ready_to_ship,custom_diamond,custom_jewellery',
            'client_name' => 'required|string|max:191',
            'client_address' => 'required|string',
            'client_mobile' => 'nullable|string|max:40',
            'client_tax_id' => 'nullable|string|max:100',
            'client_tax_id_type' => 'nullable|in:tax_id,vat_id,ioss_no,uid_vat_no,other',
            'client_email' => 'required|email|max:191',
            'diamond_sku' => 'nullable|string|max:191',
            'diamond_skus' => 'nullable|array',
            'diamond_skus.*' => 'nullable|string|max:191',
            'diamond_status' => 'nullable|string|in:r_order_in_process,r_order_shipped,d_diamond_in_discuss,d_diamond_in_making,d_diamond_completed,d_diamond_in_certificate,d_order_shipped,j_diamond_in_progress,j_diamond_completed,j_diamond_in_discuss,j_cad_in_progress,j_cad_done,j_order_completed,j_order_in_qc,j_qc_done,j_order_shipped,j_order_hold',
            'company_id' => 'required|exists:companies,id',
            'factory_id' => 'nullable|exists:factories,id',
            'gross_sell' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:full,partial,due,custom',
            'amount_received' => 'nullable|numeric|min:0',
            'amount_due' => 'nullable|numeric|min:0',
            'gold_net_weight' => 'nullable|numeric|min:0',
            'dispatch_date' => 'nullable|date',
            'note' => 'nullable|in:priority,non_priority',
            'special_notes' => 'nullable|string|max:2000',
            'shipping_company_name' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'tracking_url' => 'nullable|url',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,avif,gif,webp,heic,heif|max:10240',
            'order_pdfs.*' => 'nullable|mimes:pdf|max:10240',
            'diamond_prices' => 'nullable|array',
            'diamond_prices.*' => 'nullable|numeric|min:0',

            // Melee Fields
            'melee_entries_json' => 'nullable|string',
            'melee_entries' => 'nullable|array',
            'melee_entries.*.melee_diamond_id' => 'required|exists:melee_diamonds,id',
            'melee_entries.*.pieces' => 'required|integer|min:1',
            'melee_entries.*.avg_carat_per_piece' => 'nullable|numeric|min:0',
            'melee_entries.*.price_per_ct' => 'nullable|numeric|min:0',
            'allow_negative_melee' => 'nullable|boolean',

            'melee_diamond_id' => 'nullable|exists:melee_diamonds,id',
            'melee_pieces' => 'nullable|integer|min:1',
            'melee_carat' => 'nullable|numeric|min:0',
            'melee_price_per_ct' => 'nullable|numeric|min:0',
        ];

        switch ($this->order_type) {
            case 'ready_to_ship':
                $rules += [
                    'jewellery_details' => 'nullable|string',
                    'diamond_details' => 'nullable|string',
                    'product_other' => 'nullable|string|max:191',
                    'gold_detail_id' => 'nullable|exists:metal_types,id',
                    'ring_size_id' => 'nullable|exists:ring_sizes,id',
                    'setting_type_id' => 'nullable|exists:setting_types,id',
                    'earring_type_id' => 'nullable|exists:closure_types,id',
                ];
                break;

            case 'custom_diamond':
                $rules += [
                    'diamond_details' => 'required|string',
                ];
                break;

            case 'custom_jewellery':
                $rules += [
                    'jewellery_details' => 'required|string',
                    'diamond_details' => 'nullable|string',
                    'product_other' => 'nullable|string|max:191',
                    'gold_detail_id' => 'nullable|exists:metal_types,id',
                    'ring_size_id' => 'nullable|exists:ring_sizes,id',
                    'setting_type_id' => 'nullable|exists:setting_types,id',
                    'earring_type_id' => 'nullable|exists:closure_types,id',
                ];
                break;
        }

        return $rules;
    }
}
