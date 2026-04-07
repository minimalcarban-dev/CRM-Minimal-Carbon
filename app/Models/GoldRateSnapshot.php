<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoldRateSnapshot extends Model
{
    protected $fillable = [
        'rate_date',
        'inr_per_gram',
        'inr_per_10g',
        'source',
        'fetched_at',
        'is_live',
    ];

    protected $casts = [
        'rate_date' => 'date',
        'inr_per_gram' => 'decimal:2',
        'inr_per_10g' => 'decimal:2',
        'fetched_at' => 'datetime',
        'is_live' => 'boolean',
    ];
}
