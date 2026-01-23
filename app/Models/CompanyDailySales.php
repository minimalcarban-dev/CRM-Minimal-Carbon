<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CompanyDailySales extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'sales_date',
        'order_count',
        'total_revenue',
        'order_type_breakdown',
    ];

    protected $casts = [
        'sales_date' => 'date',
        'total_revenue' => 'decimal:2',
        'order_count' => 'integer',
        'order_type_breakdown' => 'array',
    ];

    /**
     * Get the company that owns this daily sales record.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get or create today's sales record for a company.
     */
    public static function getOrCreateToday(int $companyId): self
    {
        return self::firstOrCreate(
            [
                'company_id' => $companyId,
                'sales_date' => Carbon::today(),
            ],
            [
                'order_count' => 0,
                'total_revenue' => 0,
                'order_type_breakdown' => [],
            ]
        );
    }

    /**
     * Get month-to-date total for a company.
     */
    public static function getMonthToDateTotal(int $companyId, ?Carbon $date = null): array
    {
        $date = $date ?? Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth();

        $result = self::where('company_id', $companyId)
            ->whereBetween('sales_date', [$startOfMonth, $date])
            ->selectRaw('SUM(order_count) as total_orders, SUM(total_revenue) as total_revenue')
            ->first();

        return [
            'order_count' => (int) ($result->total_orders ?? 0),
            'total_revenue' => (float) ($result->total_revenue ?? 0),
        ];
    }
}
