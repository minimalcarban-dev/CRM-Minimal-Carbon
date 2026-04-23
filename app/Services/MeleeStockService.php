<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use App\Notifications\MeleeLowStockNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MeleeStockService
{
    /**
     * Deduct stock for an order with locked-row validation.
     *
     * @param array<int, array<string, mixed>> $entries
     * @return array<string, mixed>
     */
    public function deductForOrder(int $orderId, array $entries, array $options = []): array
    {
        $entries = $this->normalizeEntries($entries);
        $allowNegative = (bool) ($options['allow_negative'] ?? false);

        if (empty($entries)) {
            return [
                'success' => true,
                'message' => 'No melee stock entries to deduct.',
                'diamond_ids' => [],
            ];
        }

        try {
            return DB::transaction(function () use ($orderId, $entries, $allowNegative) {
                $diamonds = $this->lockDiamondsForEntries($entries);
                $validationResult = $this->validateLockedAvailability($entries, $diamonds, $allowNegative);

                if (!$validationResult['valid']) {
                    return [
                        'success' => false,
                        'status' => 422,
                        'message' => $validationResult['message'],
                    ];
                }

                $transactions = [];
                $touchedDiamonds = collect();

                foreach ($entries as $entry) {
                    /** @var MeleeDiamond $diamond */
                    $diamond = $diamonds[$entry['melee_diamond_id']];
                    $carats = round($entry['pieces'] * ($entry['avg_carat_per_piece'] ?? 0), 3);

                    $diamond->available_pieces -= $entry['pieces'];
                    $diamond->available_carat_weight -= $carats;
                    $diamond->save();

                    $touchedDiamonds->put($diamond->id, $diamond);

                    $transactions[] = [
                        'melee_diamond_id' => $diamond->id,
                        'transaction_type' => 'out',
                        'pieces' => $entry['pieces'],
                        'carat_weight' => $carats,
                        'price_per_ct' => (float) $diamond->purchase_price_per_ct,
                        'reference_type' => 'order',
                        'reference_id' => $orderId,
                        'created_by' => $this->resolveActorId(),
                        'notes' => "Stock used in Order #{$orderId}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                MeleeTransaction::insert($transactions);
                $this->notifyLowStockIfNeeded($touchedDiamonds);

                Log::info('Melee stock deducted for order', [
                    'order_id' => $orderId,
                    'entries_count' => count($entries),
                    'total_pieces' => array_sum(array_column($entries, 'pieces')),
                ]);

                return [
                    'success' => true,
                    'message' => 'Stock deducted successfully.',
                    'diamond_ids' => $touchedDiamonds->keys()->all(),
                ];
            });
        } catch (\Throwable $e) {
            Log::error('Melee stock deduction failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'entries' => $entries,
            ]);

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Failed to deduct stock: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Return stock for order cancellation/refund/deletion.
     *
     * @param array<int, array<string, mixed>> $entries
     * @return array<string, mixed>
     */
    public function returnForOrder(int $orderId, array $entries): array
    {
        $entries = $this->normalizeEntries($entries);

        if (empty($entries)) {
            return [
                'success' => true,
                'message' => 'No melee stock entries to return.',
                'diamond_ids' => [],
            ];
        }

        try {
            return DB::transaction(function () use ($orderId, $entries) {
                $diamonds = $this->lockDiamondsForEntries($entries);
                $transactions = [];
                $touchedDiamonds = collect();

                foreach ($entries as $entry) {
                    /** @var MeleeDiamond $diamond */
                    $diamond = $diamonds[$entry['melee_diamond_id']];
                    $carats = round($entry['pieces'] * ($entry['avg_carat_per_piece'] ?? 0), 3);

                    $diamond->available_pieces += $entry['pieces'];
                    $diamond->available_carat_weight += $carats;
                    $diamond->save();

                    $touchedDiamonds->put($diamond->id, $diamond);

                    $transactions[] = [
                        'melee_diamond_id' => $diamond->id,
                        'transaction_type' => 'in',
                        'pieces' => $entry['pieces'],
                        'carat_weight' => $carats,
                        'price_per_ct' => (float) $diamond->purchase_price_per_ct,
                        'reference_type' => 'order',
                        'reference_id' => $orderId,
                        'created_by' => $this->resolveActorId(),
                        'notes' => "Stock returned for Order #{$orderId}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                MeleeTransaction::insert($transactions);

                return [
                    'success' => true,
                    'message' => 'Stock returned successfully.',
                    'diamond_ids' => $touchedDiamonds->keys()->all(),
                ];
            });
        } catch (\Throwable $e) {
            Log::error('Melee stock return failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'entries' => $entries,
            ]);

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Failed to return stock: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate if requested stock is available using current database values.
     *
     * @param array<int, array<string, mixed>> $entries
     * @return array{valid: bool, message: string}
     */
    public function validateAvailability(array $entries, array $options = []): array
    {
        $entries = $this->normalizeEntries($entries);
        $allowNegative = (bool) ($options['allow_negative'] ?? false);

        if (empty($entries)) {
            return ['valid' => true, 'message' => 'No melee stock selected.'];
        }

        $diamonds = MeleeDiamond::with('category')
            ->whereIn('id', array_keys($this->aggregateRequestedPieces($entries)))
            ->get()
            ->keyBy('id');

        return $this->validateLockedAvailability($entries, $diamonds, $allowNegative);
    }

    /**
     * Create a manual stock transaction with stock safety.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function recordManualTransaction(array $payload): array
    {
        $diamondId = (int) ($payload['melee_diamond_id'] ?? 0);
        $pieces = (int) ($payload['pieces'] ?? 0);
        $caratWeight = round((float) ($payload['carat_weight'] ?? 0), 3);
        $transactionType = (string) ($payload['transaction_type'] ?? '');
        $pricePerCt = (float) ($payload['price_per_ct'] ?? 0);
        $notes = trim((string) ($payload['notes'] ?? ''));

        if ($diamondId <= 0 || $pieces <= 0) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Please select a melee lot and enter valid pieces.',
            ];
        }

        try {
            return DB::transaction(function () use ($diamondId, $pieces, $caratWeight, $transactionType, $pricePerCt, $notes) {
                /** @var MeleeDiamond $diamond */
                $diamond = MeleeDiamond::with('category')->lockForUpdate()->findOrFail($diamondId);

                if ($transactionType === 'out' && $diamond->available_pieces < $pieces) {
                    return [
                        'success' => false,
                        'status' => 422,
                        'message' => $this->buildInsufficientStockMessage($diamond, $pieces),
                    ];
                }

                if ($transactionType === 'in' && $pricePerCt > 0 && $caratWeight > 0) {
                    $this->applyWeightedAverageCost($diamond, $caratWeight, $pricePerCt);
                }

                $transaction = MeleeTransaction::create([
                    'melee_diamond_id' => $diamond->id,
                    'transaction_type' => $transactionType,
                    'pieces' => $pieces,
                    'carat_weight' => $caratWeight,
                    'price_per_ct' => $this->resolveTransactionPricePerCt($diamond, $transactionType, $pricePerCt),
                    'created_by' => $this->resolveActorId(),
                    'notes' => $notes,
                    'reference_type' => 'manual',
                ]);

                $diamond->refresh();

                return [
                    'success' => true,
                    'message' => 'Transaction recorded successfully.',
                    'transaction_id' => $transaction->id,
                    'diamond' => $this->mapDiamondForResponse($diamond),
                ];
            });
        } catch (\Throwable $e) {
            Log::error('Manual melee transaction failed', [
                'diamond_id' => $diamondId,
                'transaction_type' => $transactionType,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Failed to record transaction: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Rebuild diamond balances and weighted average from its full transaction ledger.
     */
    public function recalculateDiamondFromTransactions(MeleeDiamond $diamond): void
    {
        $transactions = MeleeTransaction::where('melee_diamond_id', $diamond->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $totalPieces = 0;
        $availablePieces = 0;
        $totalCarats = 0.0;
        $availableCarats = 0.0;
        $avgPricePerCt = 0.0;

        foreach ($transactions as $transaction) {
            $pieces = abs((int) $transaction->pieces);
            $carats = abs((float) $transaction->carat_weight);

            if ($transaction->transaction_type === 'in') {
                if ($transaction->reference_type === 'order') {
                    $availablePieces += $pieces;
                    $availableCarats += $carats;
                    continue;
                }

                $incomingPrice = (float) ($transaction->price_per_ct ?? 0);
                $this->applyWeightedAverageInMemory($avgPricePerCt, $availableCarats, $carats, $incomingPrice);

                $totalPieces += $pieces;
                $availablePieces += $pieces;
                $totalCarats += $carats;
                $availableCarats += $carats;
                continue;
            }

            if ($transaction->transaction_type === 'adjustment') {
                $totalPieces += $pieces;
                $availablePieces += $pieces;
                $totalCarats += $carats;
                $availableCarats += $carats;
                continue;
            }

            if ($transaction->transaction_type === 'out') {
                $availablePieces -= $pieces;
                $availableCarats -= $carats;
            }
        }

        $diamond->total_pieces = $totalPieces;
        $diamond->available_pieces = $availablePieces;
        $diamond->total_carat_weight = round($totalCarats, 3);
        $diamond->available_carat_weight = round($availableCarats, 3);
        $diamond->purchase_price_per_ct = round($avgPricePerCt, 2);
        $diamond->save();
    }

    /**
     * Get stock summary for multiple diamonds.
     *
     * @param array<int, int|string> $diamondIds
     * @return array<int, array<string, mixed>>
     */
    public function getStockSummary(array $diamondIds): array
    {
        $diamondIds = array_values(array_unique(array_filter(array_map('intval', $diamondIds))));

        if (empty($diamondIds)) {
            return [];
        }

        return MeleeDiamond::with('category')
            ->whereIn('id', $diamondIds)
            ->get()
            ->mapWithKeys(function (MeleeDiamond $diamond) {
                return [$diamond->id => $this->mapDiamondForResponse($diamond)];
            })
            ->toArray();
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @return array<int, array<string, mixed>>
     */
    private function normalizeEntries(array $entries): array
    {
        $normalized = [];

        foreach ($entries as $entry) {
            $diamondId = (int) ($entry['melee_diamond_id'] ?? 0);
            $pieces = (int) ($entry['pieces'] ?? 0);

            if ($diamondId <= 0 || $pieces <= 0) {
                continue;
            }

            $normalized[] = [
                'melee_diamond_id' => $diamondId,
                'pieces' => $pieces,
                'avg_carat_per_piece' => round((float) ($entry['avg_carat_per_piece'] ?? 0), 5),
                'price_per_ct' => (float) ($entry['price_per_ct'] ?? 0),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @return array<int, int>
     */
    private function aggregateRequestedPieces(array $entries): array
    {
        $requestedByDiamond = [];

        foreach ($entries as $entry) {
            $diamondId = (int) $entry['melee_diamond_id'];
            $requestedByDiamond[$diamondId] = ($requestedByDiamond[$diamondId] ?? 0) + (int) $entry['pieces'];
        }

        return $requestedByDiamond;
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @return \Illuminate\Support\Collection<int, MeleeDiamond>
     */
    private function lockDiamondsForEntries(array $entries): Collection
    {
        $diamondIds = array_keys($this->aggregateRequestedPieces($entries));

        return MeleeDiamond::with('category')
            ->whereIn('id', $diamondIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @param \Illuminate\Support\Collection<int, MeleeDiamond> $diamonds
     * @return array{valid: bool, message: string}
     */
    private function validateLockedAvailability(array $entries, Collection $diamonds, bool $allowNegative = false): array
    {
        $requestedByDiamond = $this->aggregateRequestedPieces($entries);

        foreach ($requestedByDiamond as $diamondId => $requestedPieces) {
            /** @var MeleeDiamond|null $diamond */
            $diamond = $diamonds->get($diamondId);

            if (!$diamond) {
                return [
                    'valid' => false,
                    'message' => "Melee diamond ID {$diamondId} not found.",
                ];
            }

            if (!$allowNegative && (int) $diamond->available_pieces < $requestedPieces) {
                return [
                    'valid' => false,
                    'message' => $this->buildInsufficientStockMessage($diamond, $requestedPieces),
                ];
            }
        }

        return ['valid' => true, 'message' => 'Stock available.'];
    }

    private function buildInsufficientStockMessage(MeleeDiamond $diamond, int $requestedPieces): string
    {
        $categoryName = optional($diamond->category)->name ?? 'Melee';
        $label = "{$categoryName} - {$diamond->shape} - {$diamond->size_label}";
        $availablePieces = (int) $diamond->available_pieces;

        return "Stock low for {$label}. Only {$availablePieces} pieces left, but {$requestedPieces} requested.";
    }

    private function applyWeightedAverageCost(MeleeDiamond $diamond, float $newCarats, float $newPricePerCt): void
    {
        $currentCarats = (float) $diamond->available_carat_weight;
        $totalCarats = $currentCarats + $newCarats;

        if ($totalCarats <= 0) {
            return;
        }

        $currentValue = $currentCarats * (float) $diamond->purchase_price_per_ct;
        $newValue = $newCarats * $newPricePerCt;

        $diamond->purchase_price_per_ct = ($currentValue + $newValue) / $totalCarats;
        $diamond->save();
    }

    private function applyWeightedAverageInMemory(float &$avgPricePerCt, float $currentCarats, float $newCarats, float $newPricePerCt): void
    {
        if ($newCarats <= 0 || $newPricePerCt <= 0) {
            return;
        }

        $totalCarats = $currentCarats + $newCarats;
        if ($totalCarats <= 0) {
            return;
        }

        $currentValue = $currentCarats * $avgPricePerCt;
        $newValue = $newCarats * $newPricePerCt;

        $avgPricePerCt = ($currentValue + $newValue) / $totalCarats;
    }

    private function resolveTransactionPricePerCt(MeleeDiamond $diamond, string $transactionType, float $incomingPricePerCt): float
    {
        if ($transactionType === 'in' && $incomingPricePerCt > 0) {
            return $incomingPricePerCt;
        }

        return (float) $diamond->purchase_price_per_ct;
    }

    /**
     * @param \Illuminate\Support\Collection<int, MeleeDiamond> $diamonds
     */
    private function notifyLowStockIfNeeded(Collection $diamonds): void
    {
        $superAdmins = null;

        foreach ($diamonds as $diamond) {
            $threshold = (int) ($diamond->low_stock_threshold ?? 10);

            if ((int) $diamond->available_pieces > $threshold) {
                continue;
            }

            if ($superAdmins === null) {
                $superAdmins = Admin::where('is_super', true)->get();
            }

            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new MeleeLowStockNotification($diamond->fresh('category'), (int) $diamond->available_pieces));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDiamondForResponse(MeleeDiamond $diamond): array
    {
        return [
            'id' => $diamond->id,
            'name' => (optional($diamond->category)->name ?? 'Melee') . ' - ' . $diamond->shape . ' - ' . $diamond->size_label,
            'available_pieces' => (int) $diamond->available_pieces,
            'available_carat_weight' => (float) $diamond->available_carat_weight,
            'purchase_price_per_ct' => (float) $diamond->purchase_price_per_ct,
            'total_price' => (float) $diamond->total_price,
            'status' => $diamond->status,
        ];
    }

    private function resolveActorId(): int
    {
        return (int) (Auth::guard('admin')->id() ?? Auth::id() ?? 1);
    }
}
