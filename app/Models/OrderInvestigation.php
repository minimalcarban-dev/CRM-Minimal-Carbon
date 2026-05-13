<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderInvestigation extends Model
{
    protected $fillable = [
        'order_id',
        'customer_name',
        'courier_name',
        'tracking_number',
        'shipment_status',
        'last_tracking_update',
        'no_movement_days',
        'investigation_status',
        'investigation_notes',
        'created_by',
    ];

    protected $casts = [
        'investigation_notes' => 'array',
        'last_tracking_update' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
