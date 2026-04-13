<?php

namespace App\Http\Requests;

class UpdateOrderRequest extends StoreOrderRequest
{
    public function rules(): array
    {
        $order = $this->route('order');
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];

        if ($order && in_array($order->diamond_status, $cancelledStatuses) && !auth('admin')->user()->is_super) {
            return [
                'special_notes' => 'nullable|string|max:2000',
            ];
        }

        return parent::rules();
    }
}
