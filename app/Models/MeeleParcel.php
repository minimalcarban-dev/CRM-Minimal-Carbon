<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeeleParcel extends Model
{
    use HasFactory;

    protected $fillable = [
        'parcel_code',
        'sieve_size',
        'category',
        'current_pieces',
        'current_weight',
        'avg_cost_per_carat',
        'status',
    ];

    protected $casts = [
        'current_pieces' => 'integer',
        'current_weight' => 'decimal:4',
        'avg_cost_per_carat' => 'decimal:2',
    ];

    /**
     * The transactions associated with the parcel.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(MeeleTransaction::class);
    }
}
