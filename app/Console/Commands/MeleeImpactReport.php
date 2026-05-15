<?php

namespace App\Console\Commands;

use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MeleeImpactReport extends Command
{
    protected $signature = 'melee:impact-report';
    protected $description = 'Analyze diamond usage and stock inflation impact.';

    public function handle(): int
    {
        $this->info("===== MELEE STOCK IMPACT REPORT =====\n");

        // 1. Top Usage Frequency (Diamonds used in most orders)
        $this->info('── TOP 10 MOST USED DIAMONDS (In Orders) ──');
        $topUsage = DB::table('melee_transactions')
            ->where('transaction_type', 'out')
            ->where('reference_type', 'order')
            ->select('melee_diamond_id', DB::raw('count(distinct reference_id) as order_count'), DB::raw('sum(pieces) as total_pieces_used'))
            ->groupBy('melee_diamond_id')
            ->orderBy('order_count', 'desc')
            ->limit(10)
            ->get();

        $table = [];
        foreach ($topUsage as $u) {
            $d = MeleeDiamond::with('category')->find($u->melee_diamond_id);
            if (!$d) continue;
            $table[] = [
                "#{$u->melee_diamond_id}",
                "{$d->category->name} | {$d->size_label} | {$d->shape}",
                $u->order_count,
                $u->total_pieces_used . " pcs",
                $d->total_pieces,
                $d->available_pieces
            ];
        }
        $this->table(['ID', 'Details', 'Order Count', 'Tot Used', 'DB Total', 'DB Avail'], $table);
        $this->newLine();

        // 2. Top Inflated Diamonds (Most Orphaned Returns)
        $this->info('── TOP 10 MOST INFLATED DIAMONDS (Fake Returns) ──');
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        $problematic = DB::table('melee_transactions as mt')
            ->join('orders as o', 'o.id', '=', 'mt.reference_id')
            ->where('mt.reference_type', 'order')
            ->where('mt.transaction_type', 'in')
            ->whereNotIn('o.diamond_status', $cancelledStatuses)
            ->select('mt.melee_diamond_id', DB::raw('count(*) as orphan_count'), DB::raw('sum(mt.pieces) as inflated_pieces'))
            ->groupBy('mt.melee_diamond_id')
            ->orderBy('inflated_pieces', 'desc')
            ->limit(10)
            ->get();

        $table = [];
        foreach ($problematic as $p) {
            $d = MeleeDiamond::with('category')->find($p->melee_diamond_id);
            if (!$d) continue;
            
            // Calculate how much this inflation affects total_pieces %
            $inflationPct = $d->total_pieces > 0 ? round(($p->inflated_pieces / $d->total_pieces) * 100, 1) : 0;

            $table[] = [
                "#{$p->melee_diamond_id}",
                "{$d->category->name} | {$d->size_label} | {$d->shape}",
                $p->orphan_count,
                $p->inflated_pieces . " pcs",
                $inflationPct . "%",
                $d->total_pieces
            ];
        }
        $this->table(['ID', 'Details', 'Fake Ret Count', 'Inflated Pcs', 'Inflation %', 'DB Total'], $table);
        $this->newLine();

        // 3. Top Drift (Mathematical mismatch)
        $this->info('── TOP 10 DRIFT DIAMONDS (Math Mismatch) ──');
        $diamonds = MeleeDiamond::all();
        $driftTable = [];

        foreach ($diamonds as $diamond) {
            $ledgerTotal = (int) MeleeTransaction::where('melee_diamond_id', $diamond->id)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('transaction_type', 'in')->where('reference_type', '!=', 'order')->orWhereNull('reference_type');
                    })->orWhere('transaction_type', 'adjustment');
                })->sum('pieces');

            $totalOut = (int) MeleeTransaction::where('melee_diamond_id', $diamond->id)->where('transaction_type', 'out')->sum('pieces');
            $orderReturns = (int) MeleeTransaction::where('melee_diamond_id', $diamond->id)->where('transaction_type', 'in')->where('reference_type', 'order')->sum('pieces');
            $expectedAvail = $ledgerTotal - $totalOut + $orderReturns;

            $availDrift = (int) $diamond->available_pieces - $expectedAvail;
            $totalDrift = (int) $diamond->total_pieces - $ledgerTotal;

            if ($availDrift !== 0 || $totalDrift !== 0) {
                $driftTable[] = [
                    'id' => $diamond->id,
                    'details' => "{$diamond->category->name} | {$diamond->size_label}",
                    'drift' => abs($availDrift) + abs($totalDrift),
                    'avail_drift' => $availDrift,
                    'total_drift' => $totalDrift
                ];
            }
        }

        usort($driftTable, fn($a, $b) => $b['drift'] <=> $a['drift']);
        $displayDrift = array_slice($driftTable, 0, 10);

        $finalDrift = [];
        foreach ($displayDrift as $d) {
            $finalDrift[] = [$d['id'], $d['details'], $d['avail_drift'], $d['total_drift']];
        }
        $this->table(['ID', 'Details', 'Avail Drift', 'Total Drift'], $finalDrift);

        $this->info("\n===== ANALYSIS COMPLETE =====");
        return 0;
    }
}
