<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Diamond;
use Illuminate\Support\Facades\DB;

class RecalculateDiamondProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diamonds:recalculate-profits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates and backfills the profit for all sold diamonds that have missing or $0.00 profit.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting profit recalculation for sold diamonds...');

        $query = Diamond::whereNotNull('sold_out_date')
            ->whereNotNull('sold_out_price')
            ->where(function ($q) {
                $q->whereNull('profit')
                  ->orWhere('profit', 0);
            });

        $total = $query->count();
        $this->info("Found {$total} sold diamonds needing profit recalculation.");
        
        if ($total === 0) {
            $this->info("Nothing to do!");
            return;
        }

        $count = 0;
        $bar = $this->output->createProgressBar($total);

        // Use chunkById to safely process thousands of records without running out of memory
        $query->chunkById(500, function ($diamonds) use (&$count, $bar) {
            foreach ($diamonds as $diamond) {
                $base     = (float) ($diamond->purchase_price ?? 0);
                $shipping = (float) ($diamond->shipping_price ?? 0);
                $profit   = round((float) $diamond->sold_out_price - $base - $shipping, 2);

                // We strictly ONLY update the 'profit' column. No other data is touched.
                DB::table('diamonds')
                    ->where('id', $diamond->id)
                    ->update(['profit' => $profit, 'updated_at' => now()]);

                $count++;
                $bar->advance();
            }
        });

        $bar->finish();
        
        $this->newLine(2);
        $this->info("Done! Successfully recalculated and updated profit for {$count} sold diamonds.");
    }
}
