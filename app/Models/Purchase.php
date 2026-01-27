<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Purchase status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Payment mode constants
     */
    public const PAYMENT_UPI = 'upi';
    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_BANK_TRANSFER = 'bank_transfer';

    /**
     * Payment mode labels for display
     */
    public const PAYMENT_MODES = [
        'upi' => 'UPI',
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
    ];

    protected $fillable = [
        'status',
        'purchase_date',
        'diamond_type',
        'per_ct_price',
        'weight',
        'discount_percent',
        'total_price',
        'payment_mode',
        'upi_id',
        'bank_account_name',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'party_name',
        'party_mobile',
        'party_id',
        'invoice_number',
        'notes',
        'invoice_image',
        'admin_id',
        'expense_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'per_ct_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total_price' => 'decimal:2',
        'invoice_image' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_COMPLETED,
    ];

    /**
     * Calculate total price automatically before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (Purchase $purchase) {
            $purchase->calculateTotalPrice();
        });
    }

    /**
     * Calculate total price: (per_ct_price × weight) - discount
     */
    public function calculateTotalPrice(): void
    {
        $subtotal = $this->per_ct_price * $this->weight;
        $discountAmount = ($subtotal * $this->discount_percent) / 100;
        $this->total_price = round($subtotal - $discountAmount, 2);
    }

    /**
     * Get the admin who created this purchase
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the linked party (Diamond & Gemstone vendor)
     */
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Get the linked expense (auto-created when purchase is completed)
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get invoice image URL from Cloudinary metadata
     */
    public function getInvoiceImageUrlAttribute(): ?string
    {
        if (!$this->invoice_image) {
            return null;
        }
        return is_array($this->invoice_image) 
            ? ($this->invoice_image['url'] ?? null) 
            : $this->invoice_image;
    }

    /**
     * Get invoice image public_id for Cloudinary deletion
     */
    public function getInvoiceImagePublicIdAttribute(): ?string
    {
        if (!$this->invoice_image || !is_array($this->invoice_image)) {
            return null;
        }
        return $this->invoice_image['public_id'] ?? null;
    }

    /**
     * Check if invoice is a PDF
     */
    public function isInvoicePdf(): bool
    {
        if (!$this->invoice_image || !is_array($this->invoice_image)) {
            return false;
        }
        return ($this->invoice_image['format'] ?? '') === 'pdf';
    }

    /**
     * Scope for pending purchases only
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for completed purchases only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if purchase is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if purchase is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get payment mode display label
     */
    public function getPaymentModeLabelAttribute(): string
    {
        return self::PAYMENT_MODES[$this->payment_mode] ?? strtoupper($this->payment_mode ?? 'N/A');
    }

    /**
     * Format purchase_date for HTML date input
     */
    public function getPurchaseDateFormattedAttribute(): ?string
    {
        return $this->purchase_date?->format('Y-m-d');
    }
}
