<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_type',
        'client_details',
        'jewellery_details',
        'diamond_details',
        'images',
        'order_pdfs',
        'gold_detail_id',
        'ring_size_id',
        'setting_type_id',
        'earring_type_id',
        'company_id',
        'diamond_status',
        'gross_sell',
        'note',
        'shipping_company_name',
        'tracking_number',
        'tracking_url',
        'dispatch_date',
        'submitted_by',
    ];

    protected $casts = [
        'images' => 'array',
        'order_pdfs' => 'array',
        'dispatch_date' => 'date',
        'gross_sell' => 'decimal:2',
    ];

    /**
     * Relations
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'submitted_by');
    }

    public function goldDetail()
    {
        return $this->belongsTo(MetalType::class, 'gold_detail_id');
    }

    public function ringSize()
    {
        return $this->belongsTo(RingSize::class, 'ring_size_id');
    }

    public function settingType()
    {
        return $this->belongsTo(SettingType::class, 'setting_type_id');
    }

    public function earringDetail()
    {
        return $this->belongsTo(ClosureType::class, 'earring_type_id');
    }
}
