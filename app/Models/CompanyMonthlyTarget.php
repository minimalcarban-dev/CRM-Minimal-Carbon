<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyMonthlyTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'year',
        'month',
        'target_amount',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
    ];

    /**
     * Get the company that owns this target.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get target for a specific company and period.
     */
    public static function getTarget(int $companyId, int $year, int $month): ?float
    {
        $target = self::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        return $target?->target_amount;
    }

    /**
     * Set or update target for a company and period.
     */
    public static function setTarget(int $companyId, int $year, int $month, float $amount): self
    {
        return self::updateOrCreate(
            [
                'company_id' => $companyId,
                'year' => $year,
                'month' => $month,
            ],
            ['target_amount' => $amount]
        );
    }
}
