<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyDailySales;
use App\Models\CompanyMonthlyTarget;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanySalesReportService
{
    /**
     * Get all companies with their current sales stats.
     */
    public function getCompaniesWithSalesStats(): Collection
    {
        $companies = Company::where('status', 'active')->get();

        return $companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'logo' => $company->logo,
                'currency_symbol' => $company->currency_symbol,
                'todays_orders' => $company->todays_order_count,
                'todays_sales' => $company->todays_sales,
                'month_to_date' => $company->month_to_date_sales,
                'current_target' => $company->current_month_target,
                'target_progress' => $company->target_progress,
            ];
        });
    }

    /**
     * Get today's total sales across all companies.
     */
    public function getTodaysTotalSales(): array
    {
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];

        $result = Order::whereDate('created_at', Carbon::today())
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereNotIn('diamond_status', $cancelledStatuses)
                    ->orWhereNull('diamond_status');
            })
            ->get();

        return [
            'order_count' => (int) $result->count(),
            'total_revenue' => (float) $result->sum('amount_received_total'),
        ];
    }

    /**
     * Get daily sales history for a company within a date range.
     * Includes today's live data from orders table if within range.
     */
    public function getDailySalesHistory(int $companyId, Carbon $from, Carbon $to): Collection
    {
        $today = Carbon::today();
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];

        // Get archived data (excluding today since it's live)
        $archivedData = CompanyDailySales::where('company_id', $companyId)
            ->whereBetween('sales_date', [$from, $to])
            ->where('sales_date', '<', $today)
            ->orderBy('sales_date', 'desc')
            ->get();

        // If today is within the date range, get today's live data from orders table
        if ($today->between($from, $to)) {
            $todayOrders = Order::where('company_id', $companyId)
                ->whereDate('created_at', $today)
                ->where(function ($q) use ($cancelledStatuses) {
                    $q->whereNotIn('diamond_status', $cancelledStatuses)
                        ->orWhereNull('diamond_status');
                })
                ->get();

            if ($todayOrders->count() > 0) {
                $orderCount = $todayOrders->count();
                $totalRevenue = $todayOrders->sum('amount_received_total');
                $breakdown = $todayOrders->groupBy('order_type')
                    ->map(fn($group) => $group->count())
                    ->toArray();

                // Create a virtual daily sales record for today
                $todayRecord = new CompanyDailySales([
                    'company_id' => $companyId,
                    'sales_date' => $today,
                    'order_count' => $orderCount,
                    'total_revenue' => $totalRevenue,
                    'order_type_breakdown' => $breakdown,
                ]);

                // Prepend today's data (since it's most recent)
                return collect([$todayRecord])->merge($archivedData);
            }
        }

        return $archivedData;
    }

    /**
     * Get monthly summary for a company for a specific year.
     * Includes today's live data from orders table for current month.
     */
    public function getMonthlySummary(int $companyId, int $year): array
    {
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentYear = $today->year;
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];

        // Get archived monthly sales
        $monthlySales = CompanyDailySales::where('company_id', $companyId)
            ->whereYear('sales_date', $year)
            ->selectRaw('MONTH(sales_date) as month, SUM(order_count) as orders, SUM(total_revenue) as revenue')
            ->groupBy(DB::raw('MONTH(sales_date)'))
            ->pluck('revenue', 'month')
            ->toArray();

        $monthlyOrders = CompanyDailySales::where('company_id', $companyId)
            ->whereYear('sales_date', $year)
            ->selectRaw('MONTH(sales_date) as month, SUM(order_count) as orders')
            ->groupBy(DB::raw('MONTH(sales_date)'))
            ->pluck('orders', 'month')
            ->toArray();

        // If viewing current year, add live data for current month
        if ($year == $currentYear) {
            // Get today's live sales
            $todayOrders = Order::where('company_id', $companyId)
                ->whereDate('created_at', $today)
                ->where(function ($q) use ($cancelledStatuses) {
                    $q->whereNotIn('diamond_status', $cancelledStatuses)
                        ->orWhereNull('diamond_status');
                })
                ->get();

            if ($todayOrders->count() > 0) {
                $monthlySales[$currentMonth] = ($monthlySales[$currentMonth] ?? 0) + $todayOrders->sum('amount_received_total');
                $monthlyOrders[$currentMonth] = ($monthlyOrders[$currentMonth] ?? 0) + $todayOrders->count();
            }
        }

        $targets = CompanyMonthlyTarget::where('company_id', $companyId)
            ->where('year', $year)
            ->pluck('target_amount', 'month')
            ->toArray();

        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $result[$month] = [
                'month' => $month,
                'month_name' => Carbon::create()->month($month)->format('M'),
                'orders' => (int) ($monthlyOrders[$month] ?? 0),
                'revenue' => (float) ($monthlySales[$month] ?? 0),
                'target' => (float) ($targets[$month] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Calculate projected month-end total based on current pace.
     */
    public function getProjectedMonthEnd(int $companyId, ?Carbon $date = null): float
    {
        $date = $date ?? Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $daysElapsed = $startOfMonth->diffInDays($date) + 1;
        $totalDays = $startOfMonth->diffInDays($endOfMonth) + 1;

        $monthToDate = CompanyDailySales::getMonthToDateTotal($companyId, $date);
        $totalSoFar = $monthToDate['total_revenue'];

        // Add today's live sales (not yet archived)
        $company = Company::find($companyId);
        if ($company) {
            $totalSoFar += $company->todays_sales;
        }

        if ($daysElapsed <= 0) {
            return 0;
        }

        $dailyAverage = $totalSoFar / $daysElapsed;
        $daysRemaining = $totalDays - $daysElapsed;

        return round($totalSoFar + ($dailyAverage * $daysRemaining), 2);
    }

    /**
     * Calculate projected month-end total using provided month total (from orders table).
     */
    public function getProjectedMonthEndFromOrders(int $companyId, float $monthToDateTotal, ?Carbon $date = null): float
    {
        $date = $date ?? Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $daysElapsed = $startOfMonth->diffInDays($date) + 1;
        $totalDays = $startOfMonth->diffInDays($endOfMonth) + 1;

        if ($daysElapsed <= 0) {
            return 0;
        }

        $dailyAverage = $monthToDateTotal / $daysElapsed;
        $daysRemaining = $totalDays - $daysElapsed;

        return round($monthToDateTotal + ($dailyAverage * $daysRemaining), 2);
    }

    /**
     * Archive today's sales for all companies (called by scheduled command).
     */
    public function archiveDailySales(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::yesterday(); // Archive yesterday's data by default
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];

        $companySales = Order::whereDate('created_at', $date)
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereNotIn('diamond_status', $cancelledStatuses)
                    ->orWhereNull('diamond_status');
            })
            ->get()
            ->groupBy('company_id');
        $archived = [];
        foreach ($companySales as $companyId => $orders) {
            $orderCount = $orders->count();
            $totalRevenue = $orders->sum('amount_received_total');
            $breakdown = $orders->groupBy('order_type')->map(fn($group) => $group->count())->toArray();

            CompanyDailySales::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'sales_date' => $date,
                ],
                [
                    'order_count' => $orderCount,
                    'total_revenue' => $totalRevenue,
                    'order_type_breakdown' => $breakdown,
                ]
            );

            $archived[] = [
                'company_id' => $companyId,
                'date' => $date->toDateString(),
                'orders' => $orderCount,
                'revenue' => $totalRevenue,
            ];
        }

        return $archived;
    }
}
