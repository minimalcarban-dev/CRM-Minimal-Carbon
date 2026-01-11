<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diamond extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lot_no',
        'sku',
        'material',
        'cut',
        'clarity',
        'color',
        'shape',
        'measurement',
        'weight',
        'per_ct',
        'purchase_price',
        'margin',
        'listing_price',
        'offer_calculation',
        'actual_listing_price',
        'shipping_price',
        'purchase_date',
        'sold_out_date',
        'is_sold_out',
        'duration_days',
        'duration_price',
        'sold_out_price',
        'profit',
        'sold_out_month',
        'barcode_number',
        'barcode_image_url',
        'description',
        'admin_id',
        'note',
        'diamond_type',
        'multi_img_upload',
        'assign_by',
        'assigned_at',
        'last_modified_by',
    ];

    protected $casts = [
        'listing_price' => 'decimal:2',
        'offer_calculation' => 'decimal:2',
        'actual_listing_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'per_ct' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'duration_price' => 'decimal:2',
        'sold_out_price' => 'decimal:2',
        'profit' => 'decimal:2',
        'margin' => 'decimal:2',
        'multi_img_upload' => 'array',
        'assigned_at' => 'datetime',
        'purchase_date' => 'date',
        'sold_out_date' => 'date',
    ];

    /**
     * Flag to skip auto-recalculation during save.
     * Used when markAsSold() is called, which has its own calculation logic.
     */
    protected bool $skipRecalculation = false;

    /**
     * Boot method - auto-recalculate derived fields on every save.
     * This ensures data consistency regardless of entry point (import/create/edit).
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (Diamond $diamond) {
            // Skip if markAsSold() already did the calculations
            if (!$diamond->skipRecalculation) {
                $diamond->recalculateDerivedFields();
            }
            // Reset the flag for future saves
            $diamond->skipRecalculation = false;
        });
    }

    /**
     * Recalculate all derived fields from source data.
     * This is the SINGLE SOURCE OF TRUTH for all calculations.
     * Called automatically on save via boot event.
     * 
     * Derived fields: is_sold_out, sold_out_month, duration_days, duration_price, profit
     * Source fields: sold_out_date, purchase_date, purchase_price, sold_out_price, shipping_price
     */
    public function recalculateDerivedFields(): void
    {
        // 1. STATUS: Derived from sold_out_date presence
        $this->is_sold_out = !empty($this->sold_out_date) ? 'Sold' : 'IN Stock';

        // 2. SOLD OUT MONTH: From sold_out_date
        if (!empty($this->sold_out_date)) {
            $soldDate = \Carbon\Carbon::parse($this->sold_out_date);
            $this->sold_out_month = $soldDate->format('Y-m');
        } else {
            $this->sold_out_month = null;
        }

        // 3. DURATION DAYS: From purchase_date to sold_out_date (or today if in stock)
        $base = (float) ($this->purchase_price ?? 0);

        if (!empty($this->purchase_date)) {
            $purchaseDate = \Carbon\Carbon::parse($this->purchase_date);
            $endDate = !empty($this->sold_out_date)
                ? \Carbon\Carbon::parse($this->sold_out_date)
                : now();
            $this->duration_days = max(0, $purchaseDate->diffInDays($endDate));
        } else {
            $this->duration_days = 0;
        }

        // 4. DURATION PRICE: Compound interest formula
        // Formula: purchase_price × (1 + 0.0005)^days where 0.0005 = 0.05% daily rate
        $days = (int) ($this->duration_days ?? 0);
        $dailyRate = 0.0005;
        $this->duration_price = round($base * pow(1 + $dailyRate, $days), 2);

        // 5. PROFIT: Only calculate if sold with price
        if (!empty($this->sold_out_date) && !empty($this->sold_out_price)) {
            $shipping = (float) ($this->shipping_price ?? 0);
            $this->profit = round($this->sold_out_price - $base - $shipping, 2);
        }
    }

    /**
     * Format purchase_date for HTML date input (YYYY-MM-DD format)
     */
    public function getPurchaseDateFormattedAttribute(): ?string
    {
        return $this->purchase_date?->format('Y-m-d');
    }

    /**
     * Format sold_out_date for HTML date input (YYYY-MM-DD format)
     */
    public function getSoldOutDateFormattedAttribute(): ?string
    {
        return $this->sold_out_date?->format('Y-m-d');
    }

    /**
     * Get the admin who is assigned this diamond
     */
    public function assignedAdmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get the admin who assigned this diamond
     */
    public function assignedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'assign_by');
    }

    /**
     * Get the admin who last modified this diamond
     */
    public function lastModifier()
    {
        return $this->belongsTo(Admin::class, 'last_modified_by');
    }

    /**
     * Get all admins assigned to this diamond (many-to-many)
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'diamond_admin', 'diamond_id', 'admin_id')
            ->withPivot('assign_by', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Mark this diamond as sold with proper calculations.
     * 
     * @param float $soldOutPrice The price at which diamond was sold
     * @return void
     */
    public function markAsSold(float $soldOutPrice): void
    {
        $today = now();

        // Set sold status and date
        $this->is_sold_out = 'Sold';
        $this->sold_out_price = $soldOutPrice;

        if (empty($this->sold_out_date)) {
            $this->sold_out_date = $today->toDateString();
        }

        // Use sold_out_date for month calculation
        $soldDate = \Carbon\Carbon::parse($this->sold_out_date);
        $this->sold_out_month = $soldDate->format('Y-m');

        // Calculate duration days from purchase_date to sold_out_date
        if ($this->purchase_date) {
            $purchaseDate = \Carbon\Carbon::parse($this->purchase_date);
            $this->duration_days = max(0, $purchaseDate->diffInDays($soldDate));
        }

        // Calculate duration price using daily compound interest formula:
        // Final Cost = purchase_price × (1 + 0.0005)^days
        // where 0.0005 = 0.05% daily rate
        $base = (float) ($this->purchase_price ?? 0);
        $days = (int) ($this->duration_days ?? 0);
        $dailyRate = 0.0005; // 0.05% per day
        $this->duration_price = round($base * pow(1 + $dailyRate, $days), 2);

        // Calculate profit: sold_price - purchase_price - shipping_price
        $shipping = (float) ($this->shipping_price ?? 0);
        $this->profit = round($soldOutPrice - $base - $shipping, 2);

        // Skip boot event recalculation since we already did the calculations
        $this->skipRecalculation = true;
    }
}
