<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;

    /**
     * Party Category Constants
     * Used for filtering parties in different modules
     */
    public const CATEGORY_GOLD_METAL = 'gold_metal';
    public const CATEGORY_JEWELRY_MFG = 'jewelry_mfg';
    public const CATEGORY_DIAMOND_GEMSTONE = 'diamond_gemstone';
    public const CATEGORY_BANKS = 'banks';
    public const CATEGORY_IN_PERSON = 'in_person';

    /**
     * Category labels for display in forms
     */
    public const CATEGORIES = [
        self::CATEGORY_GOLD_METAL => 'Gold Metal',
        self::CATEGORY_JEWELRY_MFG => 'Jewelry Mfg.',
        self::CATEGORY_DIAMOND_GEMSTONE => 'Diamond & Gemstone',
        self::CATEGORY_BANKS => 'Banks',
        self::CATEGORY_IN_PERSON => 'In Person',
    ];

    protected $fillable = [
        'name',
        'category',
        'address',
        'gst_no',
        'pan_no',
        'state',
        'state_code',
        'country',
        'tax_id',
        'is_foreign',
        'email',
        'phone'
    ];

    /**
     * Scope to filter parties by single category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter parties by multiple categories
     */
    public function scopeByCategories($query, array $categories)
    {
        return $query->whereIn('category', $categories);
    }

    /**
     * Get category label for display
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category ?? 'Unknown';
    }

    public function billedInvoices()
    {
        return $this->hasMany(Invoice::class, 'billed_to_id');
    }

    public function shippedInvoices()
    {
        return $this->hasMany(Invoice::class, 'shipped_to_id');
    }

    /**
     * Relationship: Gold Purchases where this party is supplier
     */
    public function goldPurchases()
    {
        return $this->hasMany(GoldPurchase::class, 'party_id');
    }

    /**
     * Relationship: Purchases where this party is vendor
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'party_id');
    }

    /**
     * Relationship: Expenses where this party is paid to/received from
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'party_id');
    }
}
