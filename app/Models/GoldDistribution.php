<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoldDistribution extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Type constants
     */
    public const TYPE_OUT = 'out';       // Gold sent TO factory
    public const TYPE_RETURN = 'return'; // Gold returned FROM factory
    public const TYPE_CONSUMED = 'consumed'; // Gold consumed in production

    /**
     * Type labels for display
     */
    public const TYPE_LABELS = [
        'out' => 'Distributed',
        'return' => 'Returned',
        'consumed' => 'Consumed',
    ];

    protected $fillable = [
        'distribution_date',
        'factory_id',
        'weight_grams',
        'type',
        'purpose',
        'notes',
        'admin_id',
        'order_id',
    ];

    protected $casts = [
        'distribution_date' => 'date',
        'weight_grams' => 'decimal:3',
    ];

    protected $attributes = [
        'type' => self::TYPE_OUT,
    ];

    /**
     * Get the factory this distribution belongs to.
     */
    public function factory()
    {
        return $this->belongsTo(Factory::class);
    }

    /**
     * Get the order associated with this consumption (if any).
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the admin who made this distribution.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Scope for outgoing distributions (sent to factory).
     */
    public function scopeOutgoing($query)
    {
        return $query->where('type', self::TYPE_OUT);
    }

    /**
     * Scope for returns (returned from factory).
     */
    public function scopeReturns($query)
    {
        return $query->where('type', self::TYPE_RETURN);
    }

    /**
     * Scope for consumption occurrences.
     */
    public function scopeConsumed($query)
    {
        return $query->where('type', self::TYPE_CONSUMED);
    }

    /**
     * Check if this is an outgoing distribution.
     */
    public function isOutgoing(): bool
    {
        return $this->type === self::TYPE_OUT;
    }

    /**
     * Check if this is a return.
     */
    public function isReturn(): bool
    {
        return $this->type === self::TYPE_RETURN;
    }

    /**
     * Check if this is a consumption.
     */
    public function isConsumed(): bool
    {
        return $this->type === self::TYPE_CONSUMED;
    }

    /**
     * Get type display label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Format distribution_date for HTML date input.
     */
    public function getDistributionDateFormattedAttribute(): ?string
    {
        return $this->distribution_date?->format('Y-m-d');
    }

    /**
     * Get total gold distributed to all factories.
     */
    public static function getTotalDistributed(): float
    {
        return (float) self::outgoing()->sum('weight_grams');
    }

    /**
     * Get total gold returned from all factories.
     */
    public static function getTotalReturned(): float
    {
        return (float) self::returns()->sum('weight_grams');
    }

    /**
     * Get total gold currently in factories (distributed - returned).
     */
    public static function getTotalInFactories(): float
    {
        return self::getTotalDistributed() - self::getTotalReturned();
    }

    /**
     * Get available owner stock (purchased - in factories).
     */
    public static function getAvailableOwnerStock(): float
    {
        $totalPurchased = GoldPurchase::getTotalPurchasedStock();
        $inFactories = self::getTotalInFactories();
        return round($totalPurchased - $inFactories, 3);
    }
}
