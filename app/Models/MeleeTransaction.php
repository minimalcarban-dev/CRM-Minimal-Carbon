<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeleeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'melee_diamond_id',
        'transaction_type', // in, out, adjustment
        'pieces', // + or -
        'carat_weight', // + or - (optional)
        'reference_type',
        'reference_id',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'carat_weight' => 'decimal:3',
    ];

    /**
     * Boot logic: When a transaction is created, AUTO-UPDATE the parent Diamond stock.
     * This keeps logic centralized.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            $diamond = $transaction->meleeDiamond;
            if (!$diamond)
                return;

            // 'pieces' should already be signed (+ for IN, - for OUT)
            // But we follow the 'transaction_type' logic to be safe/explicit if needed.
            // Requirement: "IN" adds to stock. "OUT" deducts.

            // If the code that creates this sets 'pieces' to positive for OUT (common mistake),
            // we should handle it. However, standard accounting is:
            // IN: +10
            // OUT: -5

            // Let's assume the controller sends:
            // Type=IN, Pieces=10
            // Type=OUT, Pieces=5 (Logic needs to subtract 5)

            // For Safety: We will trust the Controller to send Signed values in 'pieces' column?
            // "pieces" column in migration is integer.

            // Let's enforce the logic here:
            // The 'pieces' stored in DB *should* be signed for easy aggregation (sum(pieces) = current stock).
            // BUT our Diamond model splits "Total" vs "Available".

            // Logic for MeleeDiamond Update:
            // IN: Increases Total & Available
            // OUT: Decreases Available (Total remains same usually, or depends on business logic).
            // Wait -> "Total Pieces" usually means "Total Purchased ever" vs "Current on hand".
            // Actually, "Total Pieces" in MeleeDiamond migration likely means "Current Bundle Size" if it's a parcel.
            // But "Sold Pieces" is computed as Total - Available.

            // REVISED LOGIC:
            // IN (Purchase): Increase Total, Increase Available
            // OUT (Sale): Decrease Available only? (So Sold count increases)

            if ($transaction->transaction_type === 'in') {
                $diamond->total_pieces += abs($transaction->pieces);
                $diamond->available_pieces += abs($transaction->pieces);
                $diamond->total_carat_weight += abs($transaction->carat_weight);
                $diamond->available_carat_weight += abs($transaction->carat_weight);
            } elseif ($transaction->transaction_type === 'out') {
                // For sale, we reduce available. Total stays same (so Sold diff increases).
                $diamond->available_pieces -= abs($transaction->pieces);
                $diamond->available_carat_weight -= abs($transaction->carat_weight);
            } elseif ($transaction->transaction_type === 'adjustment') {
                // Adjustment might mean correcting a count error
                // We apply strictly.
                $diamond->available_pieces += $transaction->pieces; // can be negative
                // Optionally adjust total if it was an entry error? 
                // For now, let's just adjust available to fix balance.
            }

            $diamond->save();
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
