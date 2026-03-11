<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JewelleryStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jewellery_stocks';

    protected $fillable = [
        'sku',
        'type',
        'name',
        'metal_type_id',
        'ring_size_id',
        'weight',
        'quantity',
        'low_stock_threshold',
        'purchase_price',
        'selling_price',
        'status',
        'description',
        'image_url',
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    /**
     * Boot method to auto-calculate status on save.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            if ($item->quantity <= 0) {
                $item->status = 'out_of_stock';
            } elseif ($item->quantity <= $item->low_stock_threshold) {
                $item->status = 'low_stock';
            } else {
                $item->status = 'in_stock';
            }
        });
    }

    /**
     * Add stock quantity.
     */
    public function addStock(int $qty): void
    {
        $this->quantity += $qty;
        $this->save();
    }

    /**
     * Deduct stock quantity.
     */
    public function deductStock(int $qty): void
    {
        $this->quantity = max(0, $this->quantity - $qty);
        $this->save();
    }

    // ── Relationships ────────────────────────────────────────

    public function metalType()
    {
        return $this->belongsTo(\App\Models\MetalType::class);
    }

    public function ringSize()
    {
        return $this->belongsTo(\App\Models\RingSize::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeInStock($query)
    {
        return $query->where('status', 'in_stock');
    }

    public function scopeLowStock($query)
    {
        return $query->where('status', 'low_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('status', 'out_of_stock');
    }
}
