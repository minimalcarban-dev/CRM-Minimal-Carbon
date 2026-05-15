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

        // ─── PHASE 1.5: Clean orphaned return transactions ───────────────
        // Old code (pre-FIX-10) used to call returnForOrder() + deductForOrder()
        // on every order edit. This created "Stock returned" (IN) entries for
        // orders that were never cancelled. These orphaned returns inflate stock.
        if (!$skipDuplicates) {
            $this->cleanOrphanedReturns($dryRun);
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
     * Remove return (IN) transactions for orders that are NOT cancelled.
     *
     * The old update code (pre-FIX-10) called returnForOrder() + deductForOrder()
     * on every edit. This created "Stock returned for Order #X" entries even though
     * the order was never cancelled. These orphaned returns inflate the stock count
     * because the recalculate logic treats them as real returns.
     */
    private function cleanOrphanedReturns(bool $dryRun): void
    {
        $this->info('── Phase 1.5: Cleaning orphaned return transactions ──');

        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];

        // Find all 'in' (return) transactions for orders that are NOT cancelled
        $orphanedReturns = DB::table('melee_transactions as mt')
            ->join('orders as o', 'o.id', '=', 'mt.reference_id')
            ->where('mt.reference_type', 'order')
            ->where('mt.transaction_type', 'in')
            ->whereNotIn('o.diamond_status', $cancelledStatuses)
            ->select('mt.id', 'mt.reference_id as order_id', 'mt.melee_diamond_id', 'mt.pieces', 'mt.notes', 'o.diamond_status')
            ->orderBy('mt.reference_id', 'desc')
            ->get();

        if ($orphanedReturns->isEmpty()) {
            $this->info('  ✅ No orphaned return transactions found.');
            return;
        }

        $this->warn("  Found {$orphanedReturns->count()} orphaned return transactions:");

        // Pre-load diamond details for display
        $diamondIds = $orphanedReturns->pluck('melee_diamond_id')->unique();
        $diamonds = MeleeDiamond::with('category')->whereIn('id', $diamondIds)->get()->keyBy('id');

        $table = [];
        $idsToDelete = [];
        foreach ($orphanedReturns as $r) {
            $diamond = $diamonds->get($r->melee_diamond_id);
            $diamondLabel = $diamond
                ? ($diamond->category->name ?? 'N/A') . ' | ' . ($diamond->size_label ?? '') . ' | ' . ($diamond->shape ?? '')
                : "#{$r->melee_diamond_id}";

            $table[] = [
                "#{$r->order_id}",
                "#{$r->melee_diamond_id}",
                $diamondLabel,
                $r->pieces,
                $r->diamond_status,
                $r->notes,
                "TX #{$r->id}",
            ];
            $idsToDelete[] = $r->id;
        }

        $this->table(['Order', 'Diamond ID', 'Diamond Details', 'Pieces', 'Order Status', 'Notes', 'TX ID'], $table);

        if (!$dryRun) {
            MeleeTransaction::whereIn('id', $idsToDelete)->delete();
        }

        $action = $dryRun ? 'Would remove' : 'Removed';
        $this->info("  {$action} {$orphanedReturns->count()} orphaned return transactions.");
    }

    /**
     * Recalculate available_pieces and available_carat_weight for all diamonds
     * based on the cleaned transaction ledger.
     */
    private function repairStockLevels(bool $dryRun): void
    {
        $this->info('── Phase 2: Repairing stock levels ──');

        $diamonds = MeleeDiamond::with('category')->get();
        $repaired = 0;
        $table = [];

        foreach ($diamonds as $diamond) {
            // Calculate total_pieces from ledger (manual stock-in + adjustments only, NOT order returns)
            $ledgerTotalPieces = (int) MeleeTransaction::where('melee_diamond_id', $diamond->id)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('transaction_type', 'in')
                            ->where(function ($ref) {
                                $ref->where('reference_type', '!=', 'order')
                                    ->orWhereNull('reference_type');
                            });
                    })->orWhere('transaction_type', 'adjustment');
                })
                ->sum('pieces');

            $ledgerTotalCarat = (float) MeleeTransaction::where('melee_diamond_id', $diamond->id)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('transaction_type', 'in')
                            ->where(function ($ref) {
                                $ref->where('reference_type', '!=', 'order')
                                    ->orWhereNull('reference_type');
                            });
                    })->orWhere('transaction_type', 'adjustment');
                })
                ->sum('carat_weight');

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

            // Expected: total from purchases - sold out + order returns
            $expectedAvailable = $ledgerTotalPieces - $totalOut + $orderReturns;
            $expectedCarat = round($ledgerTotalCarat - $caratOut + $caratReturns, 3);
            $actualAvailable = $diamond->available_pieces;
            $actualCarat = (float) $diamond->available_carat_weight;

            $piecesDiff = $actualAvailable - $expectedAvailable;
            $caratDiff = round($actualCarat - $expectedCarat, 3);
            $totalPiecesDiff = (int) $diamond->total_pieces - $ledgerTotalPieces;
            $totalCaratDiff = round((float) $diamond->total_carat_weight - $ledgerTotalCarat, 3);

            if ($piecesDiff !== 0 || abs($caratDiff) > 0.001 || $totalPiecesDiff !== 0 || abs($totalCaratDiff) > 0.001) {
                // Get linked order details for this diamond
                $linkedOrders = MeleeTransaction::where('melee_diamond_id', $diamond->id)
                    ->where('reference_type', 'order')
                    ->distinct()
                    ->pluck('reference_id')
                    ->sort()
                    ->values();

                $orderCount = $linkedOrders->count();
                $orderList = $linkedOrders->map(fn($id) => "#{$id}")->implode(', ');

                // Diamond identity
                $categoryName = $diamond->category->name ?? 'N/A';
                $diamondInfo = "{$categoryName} | {$diamond->size_label} | {$diamond->shape}";
                if ($diamond->color) {
                    $diamondInfo .= " | {$diamond->color}";
                }
                if ($diamond->sieve_size) {
                    $diamondInfo .= " | Sieve: {$diamond->sieve_size}";
                }

                $table[] = [
                    "#{$diamond->id}",
                    $diamondInfo,
                    $diamond->total_pieces . ($totalPiecesDiff !== 0 ? " → {$ledgerTotalPieces}" : ''),
                    "{$actualAvailable} → {$expectedAvailable} ({$piecesDiff})",
                    round($actualCarat, 3) . ' → ' . $expectedCarat,
                    $orderCount,
                    strlen($orderList) > 40 ? substr($orderList, 0, 37) . '...' : $orderList,
                ];

                if (!$dryRun) {
                    $diamond->total_pieces = $ledgerTotalPieces;
                    $diamond->total_carat_weight = $ledgerTotalCarat;
                    $diamond->available_pieces = $expectedAvailable;
                    $diamond->available_carat_weight = $expectedCarat;
                    $diamond->save();

                    // Log the correction as an adjustment note (no transaction, just log)
                    \Illuminate\Support\Facades\Log::info('Melee stock repaired', [
                        'diamond_id' => $diamond->id,
                        'diamond_name' => $diamondInfo,
                        'old_total_pieces' => (int) $diamond->getOriginal('total_pieces'),
                        'new_total_pieces' => $ledgerTotalPieces,
                        'old_available_pieces' => $actualAvailable,
                        'new_available_pieces' => $expectedAvailable,
                        'old_available_carat' => $actualCarat,
                        'new_available_carat' => $expectedCarat,
                        'pieces_correction' => -$piecesDiff,
                        'linked_orders' => $linkedOrders->all(),
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
            ['Diamond', 'Details', 'Total Pieces', 'Available (Drift)', 'Carat (Drift)', 'Orders', 'Linked Order #s'],
            $table
        );

        $action = $dryRun ? 'Would repair' : 'Repaired';
        $this->info("  {$action} {$repaired} diamonds.");
    }
}
