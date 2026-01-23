<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Factory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'contact_phone',
        'location',
        'address',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate code if not provided
        static::creating(function (Factory $factory) {
            if (empty($factory->code)) {
                $factory->code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Generate unique factory code (FAC-A, FAC-B, etc.)
     */
    public static function generateUniqueCode(): string
    {
        $count = self::withTrashed()->count();
        $letter = chr(65 + $count); // A, B, C, etc.

        // Handle more than 26 factories
        if ($count >= 26) {
            $letter = 'F' . ($count + 1);
        }

        $code = 'FAC-' . $letter;

        // Ensure uniqueness
        while (self::withTrashed()->where('code', $code)->exists()) {
            $count++;
            $letter = $count >= 26 ? 'F' . ($count + 1) : chr(65 + $count);
            $code = 'FAC-' . $letter;
        }

        return $code;
    }

    /**
     * Get all gold distributions sent to/from this factory.
     */
    public function distributions()
    {
        return $this->hasMany(GoldDistribution::class);
    }

    /**
     * Get the admin who created this factory.
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Calculate current gold stock held by this factory.
     * Stock = Total OUT - Total RETURN
     */
    public function getCurrentStockAttribute(): float
    {
        $out = $this->distributions()
            ->where('type', GoldDistribution::TYPE_OUT)
            ->sum('weight_grams');

        $returned = $this->distributions()
            ->where('type', GoldDistribution::TYPE_RETURN)
            ->sum('weight_grams');

        return round((float) $out - (float) $returned, 3);
    }

    /**
     * Scope for active factories only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to include current stock calculation.
     */
    public function scopeWithStock($query)
    {
        return $query->withCount([
            'distributions as stock_out' => function ($q) {
                $q->where('type', GoldDistribution::TYPE_OUT)
                    ->select(DB::raw('COALESCE(SUM(weight_grams), 0)'));
            },
            'distributions as stock_return' => function ($q) {
                $q->where('type', GoldDistribution::TYPE_RETURN)
                    ->select(DB::raw('COALESCE(SUM(weight_grams), 0)'));
            },
        ]);
    }

    /**
     * Check if factory can be deleted (no gold allocated).
     */
    public function canBeDeleted(): bool
    {
        return $this->current_stock <= 0;
    }
}
