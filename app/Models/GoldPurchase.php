<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoldPurchase extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Payment mode constants (no UPI - only cash and bank_transfer)
     */
    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';

    /**
     * Payment mode labels for display
     */
    public const PAYMENT_MODES = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
    ];

    protected $fillable = [
        'purchase_date',
        'weight_grams',
        'rate_per_gram',
        'total_amount',
        'supplier_name',
        'supplier_mobile',
        'invoice_number',
        'status',
        'payment_mode',
        'bank_account_name',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'notes',
        'admin_id',
        'expense_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'weight_grams' => 'decimal:3',
        'rate_per_gram' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => self::STATUS_COMPLETED,
    ];

    /**
     * Calculate total amount automatically before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (GoldPurchase $purchase) {
            $purchase->calculateTotalAmount();
        });
    }

    /**
     * Calculate total amount: weight × rate
     */
    public function calculateTotalAmount(): void
    {
        $this->total_amount = round((float) $this->weight_grams * (float) $this->rate_per_gram, 2);
    }

    /**
     * Get the admin who created this purchase.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the linked expense (auto-created when purchase is completed).
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Scope for pending purchases only.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for completed purchases only.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if purchase is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if purchase is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get payment mode display label.
     */
    public function getPaymentModeLabelAttribute(): string
    {
        return self::PAYMENT_MODES[$this->payment_mode] ?? strtoupper($this->payment_mode ?? 'N/A');
    }

    /**
     * Format purchase_date for HTML date input.
     */
    public function getPurchaseDateFormattedAttribute(): ?string
    {
        return $this->purchase_date?->format('Y-m-d');
    }

    /**
     * Get total available gold stock from all completed purchases.
     */
    public static function getTotalPurchasedStock(): float
    {
        return (float) self::completed()->sum('weight_grams');
    }

    /**
     * Get this month's purchase total.
     */
    public static function getThisMonthPurchases(): array
    {
        $purchases = self::completed()
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year);

        return [
            'weight' => (float) $purchases->sum('weight_grams'),
            'amount' => (float) $purchases->sum('total_amount'),
        ];
    }
}
