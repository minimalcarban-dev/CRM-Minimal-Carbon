<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MeeleTransaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'meele_transactions';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // Custom created_at only

    protected $fillable = [
        'meele_parcel_id',
        'user_id',
        'type',
        'reference_id',
        'reference_type',
        'pieces',
        'weight',
        'price_per_carat',
        'total_value',
        'description',
        'created_at',
    ];

    protected $casts = [
        'pieces' => 'integer',
        'weight' => 'decimal:4',
        'price_per_carat' => 'decimal:2',
        'total_value' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * The parcel that this transaction belongs to.
     */
    public function parcel(): BelongsTo
    {
        return $this->belongsTo(MeeleParcel::class, 'meele_parcel_id');
    }

    /**
     * The user (auditor) who created this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The related reference model (Order, Purchase, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
