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
     * Get shipped statuses array.
     */
    private function getShippedStatuses(): array
    {
        return ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];
    }

    /**
     * Get all shipped orders for this company.
     */
    private function getShippedOrders()
    {
        return $this->orders()
            ->whereIn('diamond_status', $this->getShippedStatuses())
            ->get();
    }

    /**
     * Get today's shipped orders count for this company (live calculation).
     * Uses dispatch_date if available, otherwise falls back to created_at.
     */
    public function getTodaysOrderCountAttribute(): int
    {
        $today = Carbon::today();
        return $this->getShippedOrders()->filter(function ($order) use ($today) {
            $dispatchDate = $order->dispatch_date ? Carbon::parse($order->dispatch_date)->startOfDay() : null;
            $createdDate = Carbon::parse($order->created_at)->startOfDay();
            return ($dispatchDate && $dispatchDate->eq($today)) || (!$dispatchDate && $createdDate->eq($today));
        })->count();
    }

    /**
     * Get today's sales amount for this company (live calculation from shipped orders).
     * Uses dispatch_date if available, otherwise falls back to created_at.
     */
    public function getTodaysSalesAttribute(): float
    {
        $today = Carbon::today();
        return (float) $this->getShippedOrders()->filter(function ($order) use ($today) {
            $dispatchDate = $order->dispatch_date ? Carbon::parse($order->dispatch_date)->startOfDay() : null;
            $createdDate = Carbon::parse($order->created_at)->startOfDay();
            return ($dispatchDate && $dispatchDate->eq($today)) || (!$dispatchDate && $createdDate->eq($today));
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
     * Get month-to-date sales total (calculated directly from orders table).
     * Uses dispatch_date if available, otherwise falls back to created_at.
     */
    public function getMonthToDateSalesAttribute(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $monthOrders = $this->getShippedOrders()->filter(function ($order) use ($startOfMonth, $now) {
            $dispatchDate = $order->dispatch_date ? Carbon::parse($order->dispatch_date) : null;
            $createdDate = Carbon::parse($order->created_at);
            $checkDate = $dispatchDate ?? $createdDate;
            return $checkDate->between($startOfMonth, $now);
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
        return match ($this->currency) {
            'USD' => '$',
            'GBP' => '£',
            'INR' => '₹',
            'EUR' => '€',
            default => '$', // Default to USD since orders are created in dollars
        };
    }
}
