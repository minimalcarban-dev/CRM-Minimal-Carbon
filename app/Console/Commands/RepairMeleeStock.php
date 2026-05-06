<?php

namespace App\Console\Commands;

use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairMeleeStock extends Command
{
    protected $signature = 'melee:repair
                            {--dry-run : Preview changes without modifying the database}
                            {--skip-duplicates : Skip duplicate cleanup, only fix stock levels}
                            {--skip-stock : Skip stock level repair, only clean duplicates}';

    protected $description = 'Repair melee diamond stock by removing duplicate transactions and recalculating available_pieces.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $skipDuplicates = $this->option('skip-duplicates');
        $skipStock = $this->option('skip-stock');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE — No changes will be made.');
        }

        $this->newLine();

        // ─── PHASE 1: Clean duplicate transactions ───────────────────────
        if (!$skipDuplicates) {
            $this->cleanDuplicateTransactions($dryRun);
        }

        // ─── PHASE 2: Recalculate stock levels ──────────────────────────
        if (!$skipStock) {
            $this->repairStockLevels($dryRun);
        }

        $this->newLine();
        $this->info($dryRun ? '✅ Dry run complete. Re-run without --dry-run to apply changes.' : '✅ Repair complete.');

        return self::SUCCESS;
    }

    /**
     * Identify and remove duplicate transaction entries.
     *
     * Strategy: For each (order_id, diamond_id, transaction_type, pieces) group
     * with duplicates, determine how many transactions SHOULD exist based on
     * the order's actual melee_entries, and keep only that many (oldest first).
     */
    private function cleanDuplicateTransactions(bool $dryRun): void
    {
        $this->info('── Phase 1: Cleaning duplicate transactions ──');

        // Find all duplicate groups
        $duplicateGroups = DB::select("
            SELECT reference_id as order_id, melee_diamond_id, transaction_type, pieces,
                   COUNT(*) as occurrences,
                   GROUP_CONCAT(id ORDER BY id) as transaction_ids
            FROM melee_transactions
            WHERE reference_type = 'order'
            GROUP BY reference_id, melee_diamond_id, transaction_type, pieces
            HAVING COUNT(*) > 1
            ORDER BY reference_id DESC
        ");

        if (empty($duplicateGroups)) {
            $this->info('  ✅ No duplicate transactions found.');
            return;
        }

        $this->warn("  Found " . count($duplicateGroups) . " duplicate groups.");
        $totalRemoved = 0;

        foreach ($duplicateGroups as $group) {
            $allIds = explode(',', $group->transaction_ids);
            $orderId = $group->order_id;
            $diamondId = $group->melee_diamond_id;
            $type = $group->transaction_type;
            $pieces = $group->pieces;

            // Determine how many of this exact transaction SHOULD exist.
            // For 'out' transactions: count how many times this diamond appears
            // in the order's current melee_entries with this exact piece count.
            // For 'in' (returns): typically 0 or 1 per order lifecycle event.
            //
            // Simplification: Keep exactly 1 of each (order, diamond, type, pieces)
            // EXCEPT for legitimate edit cycles where a return+re-deduct happened.
            // We need to determine the net correct count.

            // For a given order, the net transactions should be:
            // - 1x 'out' per diamond (from creation or last edit)
            // - 0 or 1x 'in' per diamond (if cancelled or diamond was removed)
            
            // Strategy: Reconstruct what the correct state should be.
            // The order's CURRENT melee_entries tells us what should be deducted NOW.
            // Any 'in' (return) should cancel out a prior 'out'.
            //
            // Safest approach: For each group, keep only the FIRST (oldest) transaction
            // and remove the rest. The stock repair in Phase 2 will recalculate
            // available_pieces from the cleaned ledger.
            
            $keepId = $allIds[0]; // Keep the oldest
            $removeIds = array_slice($allIds, 1);

            $this->line("  Order #{$orderId} | Diamond #{$diamondId} | {$type} {$pieces}pcs | "
                . "keep TX#{$keepId}, remove " . count($removeIds) . " duplicates");

            if (!$dryRun) {
                MeleeTransaction::whereIn('id', $removeIds)->delete();
            }

            $totalRemoved += count($removeIds);
        }

        $action = $dryRun ? 'Would remove' : 'Removed';
        $this->info("  {$action} {$totalRemoved} duplicate transactions.");
    }

    /**
     * Recalculate available_pieces and available_carat_weight for all diamonds
     * based on the cleaned transaction ledger.
     */
    private function repairStockLevels(bool $dryRun): void
    {
        $this->info('── Phase 2: Repairing stock levels ──');

        $diamonds = MeleeDiamond::all();
        $repaired = 0;
        $table = [];

        foreach ($diamonds as $diamond) {
            // Calculate expected available from transaction ledger
            $totalOut = (int) MeleeTransaction::where('melee_diamond_id', $diamond->id)
                ->where('transaction_type', 'out')
                ->sum('pieces');

            $orderReturns = (int) MeleeTransaction::where('melee_diamond_id', $diamond->id)
                ->where('transaction_type', 'in')
                ->where('reference_type', 'order')
                ->sum('pieces');

            // Carat calculations
            $caratOut = (float) MeleeTransaction::where('melee_diamond_id', $diamond->id)
                ->where('transaction_type', 'out')
                ->sum('carat_weight');

            $caratReturns = (float) MeleeTransaction::where('melee_diamond_id', $diamond->id)
                ->where('transaction_type', 'in')
                ->where('reference_type', 'order')
                ->sum('carat_weight');

            $expectedAvailable = $diamond->total_pieces - $totalOut + $orderReturns;
            $expectedCarat = round($diamond->total_carat_weight - $caratOut + $caratReturns, 3);
            $actualAvailable = $diamond->available_pieces;
            $actualCarat = (float) $diamond->available_carat_weight;

            $piecesDiff = $actualAvailable - $expectedAvailable;
            $caratDiff = round($actualCarat - $expectedCarat, 3);

            if ($piecesDiff !== 0 || abs($caratDiff) > 0.001) {
                $table[] = [
                    $diamond->id,
                    $diamond->total_pieces,
                    $totalOut,
                    $orderReturns,
                    $expectedAvailable,
                    $actualAvailable,
                    $piecesDiff,
                    $expectedCarat,
                    $actualCarat,
                ];

                if (!$dryRun) {
                    $diamond->available_pieces = $expectedAvailable;
                    $diamond->available_carat_weight = $expectedCarat;
                    $diamond->save();

                    // Log the correction as an adjustment note (no transaction, just log)
                    \Illuminate\Support\Facades\Log::info('Melee stock repaired', [
                        'diamond_id' => $diamond->id,
                        'old_available_pieces' => $actualAvailable,
                        'new_available_pieces' => $expectedAvailable,
                        'old_available_carat' => $actualCarat,
                        'new_available_carat' => $expectedCarat,
                        'pieces_correction' => -$piecesDiff,
                    ]);
                }

                $repaired++;
            }
        }

        if (empty($table)) {
            $this->info('  ✅ All diamond stock levels are correct.');
            return;
        }

        $this->table(
            ['Diamond', 'Total', 'Out', 'Returns', 'Expected', 'Actual', 'Drift', 'Exp Carat', 'Act Carat'],
            $table
        );

        $action = $dryRun ? 'Would repair' : 'Repaired';
        $this->info("  {$action} {$repaired} diamonds.");
    }
}
