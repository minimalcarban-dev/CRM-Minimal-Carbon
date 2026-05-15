<?php
// Quick audit script for melee stock data — read-only, no modifications
// Run: php artisan tinker < melee_audit.php

use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use Illuminate\Support\Facades\DB;

echo "===== MELEE STOCK AUDIT =====\n\n";

// 1. Total diamonds and transactions
$totalDiamonds = MeleeDiamond::count();
$totalTransactions = MeleeTransaction::count();
echo "Total Melee Diamonds: {$totalDiamonds}\n";
echo "Total Melee Transactions: {$totalTransactions}\n\n";

// 2. Find duplicate transactions (same order, diamond, type, pieces — more than 1)
echo "===== DUPLICATE TRANSACTIONS =====\n";
$duplicates = DB::select("
    SELECT reference_id as order_id, melee_diamond_id, transaction_type, pieces,
           COUNT(*) as occurrences,
           GROUP_CONCAT(id ORDER BY id) as transaction_ids
    FROM melee_transactions
    WHERE reference_type = 'order'
    GROUP BY reference_id, melee_diamond_id, transaction_type, pieces
    HAVING COUNT(*) > 1
    ORDER BY reference_id DESC
    LIMIT 30
");

if (empty($duplicates)) {
    echo "  No duplicate transactions found.\n";
} else {
    echo "  Found " . count($duplicates) . " duplicate groups:\n";
    echo str_pad('Order', 8) . str_pad('Diamond', 10) . str_pad('Type', 8) . str_pad('Pieces', 8) . str_pad('Count', 8) . "IDs\n";
    echo str_repeat('-', 70) . "\n";
    foreach ($duplicates as $d) {
        echo str_pad("#{$d->order_id}", 8)
            . str_pad("#{$d->melee_diamond_id}", 10)
            . str_pad($d->transaction_type, 8)
            . str_pad($d->pieces, 8)
            . str_pad($d->occurrences, 8)
            . $d->transaction_ids . "\n";
    }
}

echo "\n";

// 3. Find orders that have BOTH in AND out transactions (paired return+deduct)
echo "===== PAIRED IN+OUT PER ORDER =====\n";
$paired = DB::select("
    SELECT t1.reference_id as order_id,
           t1.melee_diamond_id,
           t1.pieces as out_pieces,
           t2.pieces as in_pieces,
           t1.created_at as out_date,
           t2.created_at as in_date,
           t1.notes as out_notes,
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
    LIMIT 30
");

if (empty($paired)) {
    echo "  No paired in+out transactions found.\n";
} else {
    echo "  Found " . count($paired) . " paired entries:\n";
    echo str_pad('Order', 8) . str_pad('Diamond', 10) . str_pad('OUT pcs', 10) . str_pad('IN pcs', 10) . "IN notes\n";
    echo str_repeat('-', 80) . "\n";
    foreach ($paired as $p) {
        echo str_pad("#{$p->order_id}", 8)
            . str_pad("#{$p->melee_diamond_id}", 10)
            . str_pad($p->out_pieces, 10)
            . str_pad($p->in_pieces, 10)
            . $p->in_notes . "\n";
    }
}

echo "\n";

// 4. Check which orders with paired IN transactions are NOT cancelled
echo "===== NON-CANCELLED ORDERS WITH RETURN TRANSACTIONS =====\n";
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
        echo "  All orders with return transactions are cancelled (correct).\n";
    } else {
        echo "  WARNING: " . $activeOrdersWithReturns->count() . " ACTIVE orders have return transactions:\n";
        foreach ($activeOrdersWithReturns as $o) {
            echo "  Order #{$o->id} — Status: {$o->diamond_status} — Type: {$o->order_type}\n";
        }
    }
} else {
    echo "  No return transactions exist.\n";
}

echo "\n";

// 5. Stock drift check — for each diamond, compare DB values vs ledger-calculated values
echo "===== STOCK DRIFT CHECK =====\n";
$diamonds = MeleeDiamond::all();
$driftCount = 0;

echo str_pad('ID', 6) . str_pad('DB Total', 10) . str_pad('Ledger', 10) . str_pad('DB Avail', 10) . str_pad('Expected', 10) . str_pad('Drift', 8) . "\n";
echo str_repeat('-', 54) . "\n";

foreach ($diamonds as $diamond) {
    $transactions = MeleeTransaction::where('melee_diamond_id', $diamond->id)->get();

    $ledgerTotal = 0;
    $ledgerAvailable = 0;

    foreach ($transactions as $t) {
        if ($t->transaction_type === 'in' && $t->reference_type === 'order') {
            // Order return: only available
            $ledgerAvailable += $t->pieces;
        } elseif (in_array($t->transaction_type, ['in', 'adjustment'])) {
            // Manual stock-in: both total and available
            $ledgerTotal += $t->pieces;
            $ledgerAvailable += $t->pieces;
        } elseif ($t->transaction_type === 'out') {
            $ledgerAvailable -= $t->pieces;
        }
    }

    $totalDrift = (int)$diamond->total_pieces - $ledgerTotal;
    $availDrift = (int)$diamond->available_pieces - $ledgerAvailable;

    if ($totalDrift !== 0 || $availDrift !== 0) {
        echo str_pad("#{$diamond->id}", 6)
            . str_pad($diamond->total_pieces, 10)
            . str_pad($ledgerTotal, 10)
            . str_pad($diamond->available_pieces, 10)
            . str_pad($ledgerAvailable, 10)
            . str_pad($availDrift, 8)
            . "\n";
        $driftCount++;
    }
}

if ($driftCount === 0) {
    echo "  All diamonds match their ledger. No drift.\n";
} else {
    echo "\n  Total diamonds with drift: {$driftCount}\n";
}

echo "\n===== AUDIT COMPLETE =====\n";
