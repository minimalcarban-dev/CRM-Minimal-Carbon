<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyProduct extends Model
{
    protected $fillable = [
        'shopify_product_id',
        'shopify_variant_id',
        'title',
        'handle',
        'sku',
        'barcode',
        'price',
        'compare_at_price',
        'status',
        'description_html',
        'images',
        'tags',
        'vendor',
        'product_type',
        'inventory_quantity',
        // Custom metafields
        'metal_purity',
        'metal',
        'resizable',
        'comfort_fit',
        'ring_height_1',
        'ring_width_1',
        'product_video',
        'stone_measurement',
        'stone_clarity',
        'stone_carat_weight',
        'stone_color',
        'stone_shape',
        'stone_type',
        'side_stone_type',
        'side_shape',
        'side_color',
        'side_carat_weight',
        'side_measurement',
        'side_clarity',
        'melee_size',
        // Relations & sync
        'order_id',
        'last_synced_at',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'last_synced_at' => 'datetime',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /*
     |--------------------------------------------------------------------------
     | Scopes
     |--------------------------------------------------------------------------
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSearchByTitle($query, string $term)
    {
        return $query->where('title', 'like', "%{$term}%");
    }

    /*
     |--------------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------------
     */

    /**
     * All custom metafield column names.
     */
    public static function metafieldColumns(): array
    {
        return config('shopify.custom_metafields', []);
    }

    /**
     * Get custom metafields as key-value array.
     */
    public function getMetafieldsAttribute(): array
    {
        $result = [];
        foreach (self::metafieldColumns() as $key) {
            $result[$key] = $this->{ $key};
        }
        return $result;
    }

    /**
     * Primary image URL.
     */
    public function getPrimaryImageAttribute(): ?string
    {
        $images = $this->images;
        return is_array($images) && count($images) > 0
            ? ($images[0]['src'] ?? $images[0] ?? null)
            : null;
    }
}
