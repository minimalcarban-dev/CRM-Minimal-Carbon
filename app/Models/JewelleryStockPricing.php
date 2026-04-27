<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JewelleryStockPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'jewellery_stock_id',
        'material_code',
        'metal_color',
        'net_weight_grams',
        'color_weights',
        'purity_percent',
        'base_rate_usd_per_gram',
        'material_value',
        'labor_rate_usd_per_gram',
        'labor_cost',
        'stone_cost',
        'extra_cost',
        'subtotal_cost',
        'commission_percent',
        'commission_amount',
        'profit_percent',
        'profit_amount',
        'sales_markup_percent',
        'sales_markup_amount',
        'listing_price',
        'rate_source',
        'rate_fetched_at',
        'is_default_listing',
    ];

    protected $casts = [
        'net_weight_grams' => 'decimal:3',
        'color_weights' => 'array',
        'purity_percent' => 'decimal:2',
        'base_rate_usd_per_gram' => 'decimal:4',
        'material_value' => 'decimal:2',
        'labor_rate_usd_per_gram' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'stone_cost' => 'decimal:2',
        'extra_cost' => 'decimal:2',
        'subtotal_cost' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'profit_percent' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'sales_markup_percent' => 'decimal:2',
        'sales_markup_amount' => 'decimal:2',
        'listing_price' => 'decimal:2',
        'rate_fetched_at' => 'datetime',
        'is_default_listing' => 'boolean',
    ];

    public function jewelleryStock()
    {
        return $this->belongsTo(JewelleryStock::class);
    }

    public function getMaterialLabelAttribute(): string
    {
        return match ($this->material_code) {
            'silver_925' => '925 Silver',
            'silver_935' => '935 Argentium',
            'platinum_950' => '950 Platinum',
            'gold_10k' => '10K Gold',
            'gold_14k' => '14K Gold',
            'gold_18k' => '18K Gold',
            'gold_22k' => '22K Gold',
            default => ucwords(str_replace('_', ' ', (string) $this->material_code)),
        };
    }

    public function getVariantLabelAttribute(): string
    {
        $color = $this->metal_color ? ucfirst($this->metal_color) . ' ' : '';

        return trim($color . $this->material_label);
    }
}
