<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'title',
        'amount',
        'transaction_type',
        'category',
        'payment_method',
        'paid_to_received_from',
        'party_id',
        'reference_number',
        'notes',
        'invoice_image',
        'admin_id',
        'purchase_id',
        'gold_purchase_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'invoice_image' => 'array',
    ];

    /**
     * Income categories (Money In)
     */
    public const INCOME_CATEGORIES = [
        'miteshbhai_in' => 'Miteshbhai (In)',
        'vijaybhai_in' => 'Vijaybhai (In)',
        'chithi_in' => 'Chithi Receipt',
        'other_income' => 'Other Income',
    ];

    /**
     * Expense categories (Money Out)
     */
    public const EXPENSE_CATEGORIES = [
        'india_post' => 'Courier - India Post',
        'heppy_ship' => 'Courier - Heppy Ship',
        'angadia' => 'Courier - Angadia',
        'miteshbhai_out' => 'Miteshbhai (Out)',
        'shanti_jewellers' => 'Shanti Jewellers',
        'weight_diamond' => 'Diamond Purchase',
        'gold_purchase' => 'Gold Purchase',
        'tedras' => 'Tedras Work',
        'orenge' => 'Orenge Charges',
        'chithi_ex' => 'Chithi Payment',
        'light_bill' => 'Electricity Bill',
        'pani_bill' => 'Water Bill',
        'petrol' => 'Fuel / Petrol',
        'safai' => 'Cleaning / Safai',
        'stationary' => 'Office Supplies',
        'other_expense' => 'Miscellaneous',
    ];

    /**
     * Payment methods
     */
    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'upi' => 'UPI',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
    ];

    /**
     * Get all categories based on transaction type
     */
    public static function getCategoriesForType(string $type): array
    {
        return $type === 'in' ? self::INCOME_CATEGORIES : self::EXPENSE_CATEGORIES;
    }

    /**
     * Get category display name
     */
    public function getCategoryNameAttribute(): string
    {
        $allCategories = array_merge(self::INCOME_CATEGORIES, self::EXPENSE_CATEGORIES);
        return $allCategories[$this->category] ?? $this->category;
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the linked party (Banks / In Person)
     */
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Get the linked purchase (if this expense was auto-created from a diamond purchase)
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the linked gold purchase (if this expense was auto-created from a gold purchase)
     */
    public function goldPurchase()
    {
        return $this->belongsTo(GoldPurchase::class);
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
     * Scope for income transactions
     */
    public function scopeIncome($query)
    {
        return $query->where('transaction_type', 'in');
    }

    /**
     * Scope for expense transactions
     */
    public function scopeExpense($query)
    {
        return $query->where('transaction_type', 'out');
    }

    /**
     * Scope for specific month
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    /**
     * Scope for specific year
     */
    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    /**
     * Format date for HTML date input
     */
    public function getDateFormattedAttribute(): ?string
    {
        return $this->date?->format('Y-m-d');
    }
}
