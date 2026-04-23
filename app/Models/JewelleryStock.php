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
        'metal_purity',
        'ring_size_id',
        'length',
        'width',
        'diameter',
        'bale_size',
        'weight',
        'quantity',
        'low_stock_threshold',
        'purchase_price',
        'selling_price',
        'status',
        'description',
        'image_url',
        'images',
        'closure_type_id',
        'primary_stone_type_id',
        'primary_stone_weight',
        'primary_stone_count',
        'primary_stone_shape_id',
        'primary_stone_color_id',
        'primary_stone_clarity_id',
        'primary_stone_cut_id',
        'side_stone_type_id',
        'side_stone_weight',
        'total_stone_weight',
        'side_stone_count',
        'certificate_number',
        'certificate_type',
        'certificate_url',
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'primary_stone_weight' => 'decimal:3',
        'side_stone_weight' => 'decimal:3',
        'total_stone_weight' => 'decimal:3',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'images' => 'array',
    ];

    /**
     * Boot method to auto-calculate status on save.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $hasPrimaryWeight = $item->primary_stone_weight !== null && $item->primary_stone_weight !== '';
            $hasSideWeight = $item->side_stone_weight !== null && $item->side_stone_weight !== '';
            $primaryWeight = (float) ($item->primary_stone_weight ?? 0);
            $sideWeight = (float) ($item->side_stone_weight ?? 0);
            $hasStoneWeight = $hasPrimaryWeight || $hasSideWeight;
            $item->total_stone_weight = $hasStoneWeight ? round($primaryWeight + $sideWeight, 3) : null;

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
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        static::where('id', $this->id)->increment('quantity', $qty);
        $this->refresh();
    }
    /**
     * Deduct stock quantity.
     */
    public function deductStock(int $qty): bool
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        if ($this->quantity < $qty) {
            return false; // Not enough stock
        }

        static::where('id', $this->id)->decrement('quantity', $qty);
        $this->refresh();

        return true;
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

    public function closureType()
    {
        return $this->belongsTo(\App\Models\ClosureType::class);
    }

    public function primaryStoneType()
    {
        return $this->belongsTo(\App\Models\StoneType::class, 'primary_stone_type_id');
    }

    public function primaryStoneShape()
    {
        return $this->belongsTo(\App\Models\StoneShape::class, 'primary_stone_shape_id');
    }

    public function primaryStoneColor()
    {
        return $this->belongsTo(\App\Models\StoneColor::class, 'primary_stone_color_id');
    }

    public function primaryStoneClarity()
    {
        return $this->belongsTo(\App\Models\DiamondClarity::class, 'primary_stone_clarity_id');
    }

    public function primaryStoneCut()
    {
        return $this->belongsTo(\App\Models\DiamondCut::class, 'primary_stone_cut_id');
    }

    public function sideStoneType()
    {
        return $this->belongsTo(\App\Models\StoneType::class, 'side_stone_type_id');
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
