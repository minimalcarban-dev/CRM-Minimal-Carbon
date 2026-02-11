<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeleeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'type', // lab_grown, natural
        'cut_type', // brilliant, rose, salt_pepper, etc.
        'allowed_shapes',
        'has_color_layer',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        // Automatically cast allowed_shapes JSON to array
        'allowed_shapes' => 'array',
        'has_color_layer' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Parent category (e.g., Natural/Lab Grown top level if needed, or structured differently)
     * For this system, we mostly use Type enum, but parent_id allows nesting cuts under main categories if desired.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MeleeCategory::class, 'parent_id');
    }

    /**
     * Sub-categories (not strictly used in the current flat-ish structure but good to have)
     */
    public function children(): HasMany
    {
        return $this->hasMany(MeleeCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Diamonds/Stock Parcels in this category
     */
    public function diamonds(): HasMany
    {
        return $this->hasMany(MeleeDiamond::class, 'melee_category_id');
    }

    /**
     * Scope: Lab Grown
     */
    public function scopeLabGrown($query)
    {
        return $query->where('type', 'lab_grown');
    }

    /**
     * Scope: Natural
     */
    public function scopeNatural($query)
    {
        return $query->where('type', 'natural');
    }

    /**
     * Scope: Active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
