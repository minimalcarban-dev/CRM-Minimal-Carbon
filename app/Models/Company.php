<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'logo',
        'gst_no',
        'state_code',
        'ein_cin_no',
        'address',
        'country',
        'bank_name',
        'account_no',
        'ifsc_code',
        'ad_code',
        'sort_code',
        'swift_code',
        'iban',
        'account_holder_name',
        // US Bank Details
        'beneficiary_name',
        'aba_routing_number',
        'us_account_no',
        'account_type',
        'beneficiary_address',
        'currency',
        'status'
    ];

    public $timestamps = true;

    // ===== RELATIONSHIPS =====

    /**
     * Get all orders for this company.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get monthly sales targets for this company.
     */
    public function monthlyTargets()
    {
        return $this->hasMany(CompanyMonthlyTarget::class);
    }

    /**
     * Get daily sales history for this company.
     */
    public function dailySales()
    {
        return $this->hasMany(CompanyDailySales::class);
    }

    // ===== SALES ACCESSORS =====

    /**
     * Get all orders for sales calculation (prepaid model - all orders count as sales).
     * No status filter - as soon as order is created, it's a sale.
     */
    private function getAllOrdersForSales()
    {
        return $this->orders()->get();
    }

    /**
     * Get today's orders count for this company.
     * Uses created_at date (when order was placed).
     */
    public function getTodaysOrderCountAttribute(): int
    {
        $today = Carbon::today();
        return $this->getAllOrdersForSales()->filter(function ($order) use ($today) {
            return Carbon::parse($order->created_at)->startOfDay()->eq($today);
        })->count();
    }

    /**
     * Get today's sales amount for this company.
     * Uses created_at date (when order was placed).
     */
    public function getTodaysSalesAttribute(): float
    {
        $today = Carbon::today();
        return (float) $this->getAllOrdersForSales()->filter(function ($order) use ($today) {
            return Carbon::parse($order->created_at)->startOfDay()->eq($today);
        })->sum('gross_sell');
    }

    /**
     * Get current month's target for this company.
     */
    public function getCurrentMonthTargetAttribute(): ?float
    {
        $now = Carbon::now();
        return CompanyMonthlyTarget::getTarget($this->id, $now->year, $now->month);
    }

    /**
     * Get month-to-date sales total (all orders based on created_at).
     * Prepaid model - all orders count as sales when created.
     */
    public function getMonthToDateSalesAttribute(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $monthOrders = $this->getAllOrdersForSales()->filter(function ($order) use ($startOfMonth, $now) {
            $createdDate = Carbon::parse($order->created_at);
            return $createdDate->between($startOfMonth, $now);
        });

        return [
            'order_count' => $monthOrders->count(),
            'total_revenue' => $monthOrders->sum('gross_sell'),
        ];
    }

    /**
     * Get target progress percentage for current month.
     */
    public function getTargetProgressAttribute(): ?float
    {
        $target = $this->current_month_target;
        if (!$target || $target <= 0) {
            return null;
        }

        $monthSales = $this->month_to_date_sales;
        $totalSales = $monthSales['total_revenue'];

        return min(100, round(($totalSales / $target) * 100, 1));
    }

    // ===== EXISTING METHODS =====

    /**
     * Get the currency symbol based on currency code
     */
    public function getCurrencySymbolAttribute(): string
    {
        $currencies = config('currencies', []);
        return $currencies[$this->currency]['symbol'] ?? '$';
    }
}
