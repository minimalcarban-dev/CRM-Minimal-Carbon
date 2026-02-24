<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalMonthlyTarget extends Model
{
    use HasFactory;

    protected $fillable = [
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
     * Get or set global target for a period.
     */
    public static function setTarget(int $year, int $month, float $amount): self
    {
        return self::updateOrCreate(
            [
                'year' => $year,
                'month' => $month,
            ],
            ['target_amount' => $amount]
        );
    }
}
