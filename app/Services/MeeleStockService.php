<?php

namespace App\Services;

use App\Models\MeeleParcel;
use App\Models\MeeleTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class MeeleStockService
{
    /**
     * Add new stock (e.g. Purchase, Return).
     */
    public function addStock(MeeleParcel $parcel, int $pieces, float $weight, string $type, ?int $userId, ?string $description = null, $reference = null): MeeleTransaction
    {
        return DB::transaction(function () use ($parcel, $pieces, $weight, $type, $userId, $description, $reference) {
            // Lock the row
            $parcel = MeeleParcel::lockForUpdate()->find($parcel->id);

            // Create Transaction Record
            $transaction = $parcel->transactions()->create([
                'user_id' => $userId ?? auth()->id(),
                'type' => $type,
                'pieces' => abs($pieces),
                'weight' => abs($weight),
                'description' => $description,
                'reference_id' => $reference?->id,
                'reference_type' => $reference ? get_class($reference) : null,
            ]);

            // Update Stock
            $parcel->increment('current_pieces', abs($pieces));
            $parcel->increment('current_weight', abs($weight));

            // Check for negative stock if this was somehow called invalidly (sanity check)
            if ($parcel->current_weight < 0) {
                throw new Exception("Inventory Integrity Error: Negative Weight Detected.");
            }

            return $transaction;
        });
    }

    /**
     * Deduct stock (e.g. Sale, Adjustment Out).
     */
    public function deductStock(MeeleParcel $parcel, int $pieces, float $weight, string $type, ?int $userId, ?string $description = null, $reference = null): MeeleTransaction
    {
        return DB::transaction(function () use ($parcel, $pieces, $weight, $type, $userId, $description, $reference) {
            // Lock the row
            $parcel = MeeleParcel::lockForUpdate()->find($parcel->id);

            // Validation: Ensure sufficient stock
            if ($parcel->current_pieces < $pieces) {
                throw new Exception("Insufficient Pieces: Requested {$pieces}, Available {$parcel->current_pieces}");
            }
            if ($parcel->current_weight < $weight) {
                throw new Exception("Insufficient Weight: Requested {$weight}, Available {$parcel->current_weight}");
            }

            // Create Transaction Record (Negative values stored? No, store absolute in columns, type determines math? 
            // Spec says: pieces INT (can be negative). Let's follow Spec logic - pieces/weight columns in DB are strict.
            // Wait, standard accounting usually logs Debits/Credits or positive/negative signed values.
            // My Migration schema said: "pieces INT (Can be negative)".
            // So I should store negative values for deductions.

            $transaction = $parcel->transactions()->create([
                'user_id' => $userId ?? auth()->id(),
                'type' => $type,
                'pieces' => -abs($pieces), // Explicitly negative
                'weight' => -abs($weight), // Explicitly negative
                'description' => $description,
                'reference_id' => $reference?->id, // If reference keys are present
                'reference_type' => $reference ? get_class($reference) : null,
            ]);

            // Update Stock
            $parcel->decrement('current_pieces', abs($pieces));
            $parcel->decrement('current_weight', abs($weight));

            return $transaction;
        });
    }

    /**
     * Adjust stock (Set to explicit value? Or delta?).
     * Usually Adjustment Add / Adjustment Sub.
     */
    public function adjustStock(MeeleParcel $parcel, int $piecesDelta, float $weightDelta, ?int $userId, string $reason): MeeleTransaction
    {
        if ($piecesDelta >= 0 && $weightDelta >= 0) {
            return $this->addStock($parcel, $piecesDelta, $weightDelta, 'adjustment_add', $userId, $reason);
        } else {
            return $this->deductStock($parcel, abs($piecesDelta), abs($weightDelta), 'adjustment_sub', $userId, $reason);
        }
    }
}
