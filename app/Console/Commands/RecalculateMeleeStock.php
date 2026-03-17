<?php

namespace App\Console\Commands;

use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use Illuminate\Console\Command;

class RecalculateMeleeStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'melee:recalculate-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate stock for all melee diamonds based on transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating stock for all melee diamonds...');

        $diamonds = MeleeDiamond::all();

        foreach ($diamonds as $diamond) {
            $transactions = MeleeTransaction::where('melee_diamond_id', $diamond->id)->get();

            $total_pieces = 0;
            $available_pieces = 0;
            $total_carat = 0;
            $available_carat = 0;

            foreach ($transactions as $t) {
                if (in_array($t->transaction_type, ['in', 'adjustment'])) {
                    $total_pieces += $t->pieces;
                    $available_pieces += $t->pieces;
                    $total_carat += $t->carat_weight ?? 0;
                    $available_carat += $t->carat_weight ?? 0;
                } elseif ($t->transaction_type == 'out') {
                    $available_pieces -= $t->pieces;
                    $available_carat -= $t->carat_weight ?? 0;
                }
            }

            $diamond->total_pieces = $total_pieces;
            $diamond->available_pieces = $available_pieces;
            $diamond->total_carat_weight = $total_carat;
            $diamond->available_carat_weight = $available_carat;

            if ($available_pieces <= 0) {
                $diamond->status = 'out_of_stock';
            } elseif ($available_pieces <= $diamond->low_stock_threshold) {
                $diamond->status = 'low_stock';
            } else {
                $diamond->status = 'in_stock';
            }

            $diamond->save();
        }

        $this->info('Stock recalculation completed.');
    }
}
