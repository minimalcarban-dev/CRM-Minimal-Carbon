<?php

namespace App\Console\Commands;

use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MeleeAudit extends Command
{
    // ================================================================
    // READ-ONLY — no mutations permitted.
    // This command MUST NEVER write, update, or delete any data.
    // ================================================================

    protected $signature = 'melee:audit';
    protected $description = 'Read-only audit of melee stock data — checks for duplicates, drift, and anomalies.';

    public function handle(): int
    {
        $this->info("===== MELEE STOCK AUDIT =====\n");

        // 1. Summary Table (Sprint 1 format)
        $totalDiamonds = MeleeDiamond::count();
        $totalTransactions = MeleeTransaction::count();
        $orphanedRecords = MeleeDiamond::whereNull('melee_category_id')->count();
        $negativeStockCount = MeleeDiamond::where('available_pieces', '<', 0)->count();
        $updatedToday = MeleeDiamond::whereDate('updated_at', today())->count();
        $totalValue = MeleeDiamond::sum('total_price');

        $this->table(['Metric', 'Value'], [
            ['Total melee diamond records', $totalDiamonds],
            ['Total melee transactions', $totalTransactions],
            ['Orphaned records (no category)', $orphanedRecords],
            ['Negative stock records', $negativeStockCount],
            ['Records updated today', $updatedToday],
            ['Total stock value', number_format((float) $totalValue, 2)],
        ]);

        $this->newLine();

        // 2. Duplicate transactions
        $this->info('── DUPLICATE TRANSACTIONS ──');
        $duplicates = DB::select("
            SELECT reference_id as order_id, melee_diamond_id, transaction_type, pieces,
                   COUNT(*) as occurrences,
                   GROUP_CONCAT(id) as transaction_ids
            FROM melee_transactions
            WHERE reference_type = 'order'
            GROUP BY reference_id, melee_diamond_id, transaction_type, pieces
            HAVING COUNT(*) > 1
            ORDER BY reference_id DESC
        ");

        if (empty($duplicates)) {
            $this->info('  ✅ No duplicate transactions found.');
        } else {
            $this->warn("  Found " . count($duplicates) . " duplicate groups:");
            $table = [];
            foreach ($duplicates as $d) {
                $table[] = ["#{$d->order_id}", "#{$d->melee_diamond_id}", $d->transaction_type, $d->pieces, $d->occurrences, $d->transaction_ids];
            }
            $this->table(['Order', 'Diamond', 'Type', 'Pieces', 'Count', 'TX IDs'], $table);
        }

        $this->newLine();

        // 3. Orders with BOTH in AND out transactions (paired return+deduct)
        $this->info('── PAIRED IN+OUT PER ORDER ──');
        $paired = DB::select("
            SELECT t1.reference_id as order_id,
                   t1.melee_diamond_id,
                   t1.pieces as out_pieces,
                   t2.pieces as in_pieces,
                   t1.created_at as out_date,
                   t2.created_at as in_date,
                   t2.notes as in_notes
            FROM melee_transactions t1
            JOIN melee_transactions t2
                ON t1.reference_id = t2.reference_id
                AND t1.melee_diamond_id = t2.melee_diamond_id
                AND t1.reference_type = 'order'
                AND t2.reference_type = 'order'
                AND t1.transaction_type = 'out'
                AND t2.transaction_type = 'in'
            ORDER BY t1.reference_id DESC
        ");

        if (empty($paired)) {
            $this->info('  ✅ No paired in+out transactions found.');
        } else {
            $this->warn("  Found " . count($paired) . " paired entries:");
            $table = [];
            foreach ($paired as $p) {
                $table[] = ["#{$p->order_id}", "#{$p->melee_diamond_id}", $p->out_pieces, $p->in_pieces, $p->out_date, $p->in_date, $p->in_notes];
            }
            $this->table(['Order', 'Diamond', 'OUT pcs', 'IN pcs', 'OUT date', 'IN date', 'IN notes'], $table);
        }

        $this->newLine();

        // 4. Non-cancelled orders with return transactions
        $this->info('── NON-CANCELLED ORDERS WITH RETURN TRANSACTIONS ──');
        $orderIdsWithReturns = DB::table('melee_transactions')
            ->where('reference_type', 'order')
            ->where('transaction_type', 'in')
            ->distinct()
            ->pluck('reference_id')
            ->toArray();

        if (!empty($orderIdsWithReturns)) {
            $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
            $activeOrdersWithReturns = DB::table('orders')
                ->whereIn('id', $orderIdsWithReturns)
                ->whereNotIn('diamond_status', $cancelledStatuses)
                ->select('id', 'diamond_status', 'order_type')
                ->get();

            if ($activeOrdersWithReturns->isEmpty()) {
                $this->info('  ✅ All orders with return transactions are cancelled (correct).');
            } else {
                $this->warn("  ⚠️ " . $activeOrdersWithReturns->count() . " ACTIVE orders have return transactions:");
                $table = [];
                foreach ($activeOrdersWithReturns as $o) {
                    $table[] = ["#{$o->id}", $o->diamond_status, $o->order_type];
                }
                $this->table(['Order', 'Status', 'Type'], $table);
            }
        } else {
            $this->info('  No return transactions exist.');
        }

        $this->newLine();

        // 5. Stock drift check
        $this->info('── STOCK DRIFT CHECK ──');
        $diamonds = MeleeDiamond::all();
        $driftTable = [];

        foreach ($diamonds as $diamond) {
            $transactions = MeleeTransaction::where('melee_diamond_id', $diamond->id)->get();

            $ledgerTotal = 0;
            $ledgerAvailable = 0;

            foreach ($transactions as $t) {
                if ($t->transaction_type === 'in' && $t->reference_type === 'order') {
                    $ledgerAvailable += $t->pieces;
                } elseif (in_array($t->transaction_type, ['in', 'adjustment'])) {
                    $ledgerTotal += $t->pieces;
                    $ledgerAvailable += $t->pieces;
                } elseif ($t->transaction_type === 'out') {
                    $ledgerAvailable -= $t->pieces;
                }
            }

            $totalDrift = (int) $diamond->total_pieces - $ledgerTotal;
            $availDrift = (int) $diamond->available_pieces - $ledgerAvailable;

            if ($totalDrift !== 0 || $availDrift !== 0) {
                $driftTable[] = [
                    "#{$diamond->id}",
                    $diamond->total_pieces,
                    $ledgerTotal,
                    $totalDrift > 0 ? "+{$totalDrift}" : $totalDrift,
                    $diamond->available_pieces,
                    $ledgerAvailable,
                    $availDrift > 0 ? "+{$availDrift}" : $availDrift,
                ];
            }
        }

        if (empty($driftTable)) {
            $this->info('  ✅ All diamonds match their ledger. No drift.');
        } else {
            $this->warn("  ⚠️ " . count($driftTable) . " diamonds with drift:");
            $this->table(
                ['Diamond', 'DB Total', 'Ledger Total', 'Total Drift', 'DB Avail', 'Ledger Avail', 'Avail Drift'],
                $driftTable
            );
        }


        // 6. Diamonds with zero purchase price
        $this->info('── ZERO PURCHASE PRICE ──');
        $zeroPriceDiamonds = MeleeDiamond::with('category')
            ->where('purchase_price_per_ct', 0)
            ->get();

        if ($zeroPriceDiamonds->isEmpty()) {
            $this->info('  ✅ All diamonds have a purchase price set.');
        } else {
            $this->warn("  ⚠️ {$zeroPriceDiamonds->count()} diamond(s) have zero purchase price:");
            $table = [];
            foreach ($zeroPriceDiamonds as $d) {
                $table[] = ["#{$d->id}", optional($d->category)->name ?? '—', $d->shape, $d->size_label];
            }
            $this->table(['Diamond', 'Category', 'Shape', 'Size'], $table);
        }

        $this->newLine();

        // 7. Categories with zero diamonds
        $this->info('── EMPTY CATEGORIES ──');
        $emptyCategories = MeleeCategory::doesntHave('diamonds')->get();

        if ($emptyCategories->isEmpty()) {
            $this->info('  ✅ All categories have at least one diamond.');
        } else {
            $this->warn("  ⚠️ {$emptyCategories->count()} category/categories have no diamonds:");
            $table = [];
            foreach ($emptyCategories as $c) {
                $table[] = ["#{$c->id}", $c->name, $c->type ?? '—'];
            }
            $this->table(['Category', 'Name', 'Type'], $table);
        }

        $this->newLine();

        // 8. Recent transactions (last 24 hours)
        $this->info('── RECENT TRANSACTIONS (LAST 24H) ──');
        $recentCount = MeleeTransaction::where('created_at', '>=', now()->subDay())->count();
        $this->info("  🕐 {$recentCount} transaction(s) recorded in the last 24 hours.");

        $this->newLine();
        $this->info('===== AUDIT COMPLETE =====');
        return self::SUCCESS;
    }
}
