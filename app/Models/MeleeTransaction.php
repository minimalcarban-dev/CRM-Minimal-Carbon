<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Notification;
use App\Notifications\MeleeLowStockNotification;
use App\Models\Admin;

class MeleeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'melee_diamond_id',
        'transaction_type', // in, out, adjustment
        'pieces', // + or -
        'carat_weight', // + or - (optional)
        'price_per_ct',
        'reference_type',
        'reference_id',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'carat_weight' => 'decimal:3',
        'price_per_ct' => 'decimal:2',
    ];

    /**
     * Boot logic: When a transaction is created, AUTO-UPDATE the parent Diamond stock.
     * Also triggers low-stock notification to super admins when stock < 10.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            $diamond = $transaction->meleeDiamond;
            if (!$diamond)
                return;

            if ($transaction->transaction_type === 'in') {
                if ($transaction->reference_type === 'order') {
                    $diamond->available_pieces += abs($transaction->pieces);
                    $diamond->available_carat_weight += abs($transaction->carat_weight);
                } else {
                    $diamond->total_pieces += abs($transaction->pieces);
                    $diamond->available_pieces += abs($transaction->pieces);
                    $diamond->total_carat_weight += abs($transaction->carat_weight);
                    $diamond->available_carat_weight += abs($transaction->carat_weight);
                }
            } elseif ($transaction->transaction_type === 'adjustment') {
                $diamond->total_pieces += abs($transaction->pieces);
                $diamond->available_pieces += abs($transaction->pieces);
                $diamond->total_carat_weight += abs($transaction->carat_weight);
                $diamond->available_carat_weight += abs($transaction->carat_weight);
            } elseif ($transaction->transaction_type === 'out') {
                $diamond->available_pieces -= abs($transaction->pieces);
                $diamond->available_carat_weight -= abs($transaction->carat_weight);
            }

            $diamond->save();

            // ── Low Stock Notification ──
            // If stock drops below threshold (default 10), notify all super admins
            $threshold = $diamond->low_stock_threshold ?? 10;
            if ($diamond->available_pieces < $threshold) {
                $diamond->loadMissing('category');
                $superAdmins = Admin::where('is_super', true)->get();

                if ($superAdmins->isNotEmpty()) {
                    Notification::send($superAdmins, new MeleeLowStockNotification($diamond, $diamond->available_pieces));
                }
            }
        });
    }

    public function meleeDiamond(): BelongsTo
    {
        return $this->belongsTo(MeleeDiamond::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
