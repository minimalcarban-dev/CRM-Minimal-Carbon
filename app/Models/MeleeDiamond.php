<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeleeDiamond extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'melee_category_id',
        'shape',
        'color',
        'sieve_size',
        'size_label',
        'total_pieces',
        'available_pieces',
        'sold_pieces',
        'total_carat_weight',
        'available_carat_weight',
        'purchase_price_per_ct',
        'listing_price_per_ct',
        'total_price',
        'status',
        'low_stock_threshold'
    ];

    protected $casts = [
        'total_carat_weight' => 'decimal:3',
        'available_carat_weight' => 'decimal:3',
        'purchase_price_per_ct' => 'decimal:2',
        'listing_price_per_ct' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot logic to auto-calculate status and sold counts.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 1. Calculate Sold Pieces
            $model->sold_pieces = max(0, $model->total_pieces - $model->available_pieces);

            // 2. Auto-calculate Total Price = available_carat_weight * purchase_price_per_ct
            $model->total_price = ($model->available_carat_weight ?? 0) * ($model->purchase_price_per_ct ?? 0);

            // 3. Update Status based on Availability
            if ($model->available_pieces <= 0) {
                $model->status = 'out_of_stock';
            } elseif ($model->available_pieces <= $model->low_stock_threshold) {
                $model->status = 'low_stock';
            } else {
                $model->status = 'in_stock';
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MeleeCategory::class, 'melee_category_id');
    }

    public function getNameAttribute(): string
    {
        $categoryName = $this->category->name ?? 'Melee';
        $size = str_replace('-', ' ', $this->size_label ?? 'N/A');
        return "{$categoryName} — {$size}";
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MeleeTransaction::class);
    }

    /**
     * Add Stock (Purchase/Return)
     * NOTE: This does NOT create the transaction log. Use the Controller or Service to do both.
     */
    public function addStock(int $pieces, float $carats)
    {
        $this->total_pieces += $pieces;
        $this->available_pieces += $pieces;
        $this->total_carat_weight += $carats;
        $this->available_carat_weight += $carats;
        $this->save();
    }

    /**
     * Deduct Stock (Sale/Adjustment)
     * NOTE: This does NOT create the transaction log. Use the Controller or Service to do both.
     */
    public function deductStock(int $pieces, float $carats)
    {
        // Allow negative balance? Usually yes for "correction later" but warning should show in UI
        $this->available_pieces -= $pieces;
        $this->available_carat_weight -= $carats;
        $this->save();
    }
}
