<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_date',
        'diamond_type',
        'per_ct_price',
        'weight',
        'discount_percent',
        'total_price',
        'payment_mode',
        'upi_id',
        'party_name',
        'party_mobile',
        'invoice_number',
        'notes',
        'admin_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'per_ct_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Calculate total price automatically before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (Purchase $purchase) {
            $purchase->calculateTotalPrice();
        });
    }

    /**
     * Calculate total price: (per_ct_price × weight) - discount
     */
    public function calculateTotalPrice(): void
    {
        $subtotal = $this->per_ct_price * $this->weight;
        $discountAmount = ($subtotal * $this->discount_percent) / 100;
        $this->total_price = round($subtotal - $discountAmount, 2);
    }

    /**
     * Get the admin who created this purchase
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Format purchase_date for HTML date input
     */
    public function getPurchaseDateFormattedAttribute(): ?string
    {
        return $this->purchase_date?->format('Y-m-d');
    }
}
