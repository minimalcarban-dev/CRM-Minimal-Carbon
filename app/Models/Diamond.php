<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diamond extends Model
{
    use HasFactory, SoftDeletes;

    private const DAILY_DURATION_RATE = 0.0005;

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
        'current_location',
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
     * Derived fields: listing_price, actual_listing_price, is_sold_out, sold_out_month, duration_days, duration_price, profit
     * Source fields: purchase_price, margin, offer_calculation, sold_out_date, purchase_date, sold_out_price, shipping_price
     */
    public function recalculateDerivedFields(): void
    {
        // 0. PRICING: listing and actual listing
        $base = (float) ($this->purchase_price ?? 0);
        $margin = (float) ($this->margin ?? 0);
        $offer = (float) ($this->offer_calculation ?? 0);

        // listing_price = purchase_price * (1 + margin/100)
        if ($base > 0) {
            $this->listing_price = round($base * (1 + ($margin / 100)), 2);
        } else {
            $this->listing_price = null;
        }

        // actual_listing_price = listing_price + (listing_price * offer/100)
        $listing = (float) ($this->listing_price ?? 0);
        if ($listing > 0) {
            $this->actual_listing_price = round($listing * (1 + ($offer / 100)), 2);
        } else {
            $this->actual_listing_price = null;
        }

        // 1. STATUS: Derived from sold_out_date presence
        $snapshot = $this->deriveDurationSnapshot();
        $this->is_sold_out = $snapshot['is_sold_out'];
        $this->sold_out_month = $snapshot['sold_out_month'];
        $this->setAttribute('duration_days', $snapshot['duration_days']);
        $this->setAttribute('duration_price', $snapshot['duration_price']);

        // 5. PROFIT: Only calculate if sold with price
        if (!empty($this->sold_out_date) && !empty($this->sold_out_price)) {
            $shipping = (float) ($this->shipping_price ?? 0);
            $this->profit = round((float) $this->sold_out_price - $base - $shipping, 2);
        } else {
            // Reset profit if not sold or sold_out_price is missing
            $this->profit = null;
        }
    }

    /**
     * Duration snapshot that can be persisted in DB.
     */
    public function deriveDurationSnapshot(?CarbonInterface $asOf = null): array
    {
        $soldDate = $this->normalizeToAppDate($this->sold_out_date);
        $durationDays = $this->calculateDurationDays($asOf);

        return [
            'is_sold_out' => $soldDate ? 'Sold' : 'IN Stock',
            'sold_out_month' => $soldDate ? $soldDate->format('Y-m') : null,
            'duration_days' => $durationDays,
            'duration_price' => $this->calculateDurationPrice($durationDays),
        ];
    }

    /**
     * Calculate duration days using date-only arithmetic in app timezone.
     */
    public function calculateDurationDays(?CarbonInterface $asOf = null): int
    {
        $purchaseDate = $this->normalizeToAppDate($this->purchase_date);
        if (!$purchaseDate) {
            return 0;
        }

        $endDate = $this->normalizeToAppDate($this->sold_out_date);
        if (!$endDate) {
            $endDate = $asOf
                ? $this->normalizeToAppDate($asOf)
                : Carbon::now($this->durationTimezone())->startOfDay();
        }

        return max(0, $purchaseDate->diffInDays($endDate));
    }

    /**
     * Calculate duration price using daily compound rate.
     */
    public function calculateDurationPrice(?int $days = null): float
    {
        $base = (float) ($this->purchase_price ?? 0);
        $effectiveDays = max(0, (int) ($days ?? $this->calculateDurationDays()));

        return round($base * pow(1 + self::DAILY_DURATION_RATE, $effectiveDays), 2);
    }

    public function getDurationDaysAttribute($value): int
    {
        return $this->calculateDurationDays();
    }

    public function getDurationPriceAttribute($value): float
    {
        return $this->calculateDurationPrice($this->calculateDurationDays());
    }

    protected function durationTimezone(): string
    {
        return config('app.timezone', 'UTC');
    }

    protected function normalizeToAppDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        $timezone = $this->durationTimezone();

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value)->setTimezone($timezone)->startOfDay();
        }

        return Carbon::parse($value, $timezone)->startOfDay();
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
            ->withPivot('assign_by', 'assigned_at', 'note')
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
        $today = Carbon::now($this->durationTimezone())->startOfDay();

        // Set sold status and date
        $this->is_sold_out = 'Sold';
        $this->sold_out_price = $soldOutPrice;

        if (empty($this->sold_out_date)) {
            $this->sold_out_date = $today->toDateString();
        }

        // Use sold_out_date for month calculation
        $soldDate = $this->normalizeToAppDate($this->sold_out_date);
        $this->sold_out_month = $soldDate->format('Y-m');

        $durationDays = $this->calculateDurationDays($soldDate);
        $this->setAttribute('duration_days', $durationDays);
        $this->setAttribute('duration_price', $this->calculateDurationPrice($durationDays));

        // Calculate profit: sold_price - purchase_price - shipping_price
        $base = (float) ($this->purchase_price ?? 0);
        $shipping = (float) ($this->shipping_price ?? 0);
        $this->profit = round($soldOutPrice - $base - $shipping, 2);

        // Skip boot event recalculation since we already did the calculations
        $this->skipRecalculation = true;
    }
}
