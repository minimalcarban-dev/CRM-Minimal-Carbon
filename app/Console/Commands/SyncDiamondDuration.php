<?php

namespace App\Console\Commands;

use App\Models\Diamond;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncDiamondDuration extends Command
{
    protected $signature = 'diamonds:sync-duration
        {--dry-run : Show changes without persisting them}
        {--chunk=500 : Number of records to process per chunk}
        {--only-in-stock : Process only rows where sold_out_date is null}';

    protected $description = 'Recalculate duration fields and normalize inconsistent diamond lifecycle metadata';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $onlyInStock = (bool) $this->option('only-in-stock');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $asOf = Carbon::now(config('app.timezone', 'UTC'))->startOfDay();

        $query = Diamond::query()->orderBy('id');

        if ($onlyInStock) {
            $query->whereNull('sold_out_date');
        }

        $processed = 0;
        $changed = 0;
        $statusNormalized = 0;
        $invalidSoldNormalized = 0;
        $durationDaysUpdated = 0;
        $durationPriceUpdated = 0;
        $monthUpdated = 0;

        $this->info(sprintf(
            'Starting diamonds duration sync (%s, chunk=%d, as_of=%s)',
            $dryRun ? 'DRY RUN' : 'WRITE MODE',
            $chunkSize,
            $asOf->toDateString()
        ));

        $query->chunkById($chunkSize, function ($diamonds) use (
            $dryRun,
            $asOf,
            &$processed,
            &$changed,
            &$statusNormalized,
            &$invalidSoldNormalized,
            &$durationDaysUpdated,
            &$durationPriceUpdated,
            &$monthUpdated
        ) {
            foreach ($diamonds as $diamond) {
                $processed++;

                $rawStatus = (string) ($diamond->getRawOriginal('is_sold_out') ?? '');
                $rawMonth = $diamond->getRawOriginal('sold_out_month');
                $rawDays = (int) ($diamond->getRawOriginal('duration_days') ?? 0);
                $rawPrice = round((float) ($diamond->getRawOriginal('duration_price') ?? 0), 2);

                $expected = $diamond->deriveDurationSnapshot($asOf);
                $expectedPrice = round((float) $expected['duration_price'], 2);

                $statusChanged = $rawStatus !== $expected['is_sold_out'];
                $monthChanged = (string) ($rawMonth ?? '') !== (string) ($expected['sold_out_month'] ?? '');
                $daysChanged = $rawDays !== (int) $expected['duration_days'];
                $priceChanged = abs($rawPrice - $expectedPrice) > 0.01;

                if (!($statusChanged || $monthChanged || $daysChanged || $priceChanged)) {
                    continue;
                }

                $changed++;

                if ($statusChanged) {
                    $statusNormalized++;
                }
                if ($monthChanged) {
                    $monthUpdated++;
                }
                if ($daysChanged) {
                    $durationDaysUpdated++;
                }
                if ($priceChanged) {
                    $durationPriceUpdated++;
                }
                if ($rawStatus === 'Sold' && empty($diamond->sold_out_date) && $expected['is_sold_out'] === 'IN Stock') {
                    $invalidSoldNormalized++;
                }

                if ($dryRun) {
                    continue;
                }

                $diamond->setAttribute('is_sold_out', $expected['is_sold_out']);
                $diamond->setAttribute('sold_out_month', $expected['sold_out_month']);
                $diamond->setAttribute('duration_days', (int) $expected['duration_days']);
                $diamond->setAttribute('duration_price', $expectedPrice);
                $diamond->saveQuietly();
            }
        });

        $this->line(str_repeat('-', 72));
        $this->info('Diamond duration sync summary');
        $this->line("Processed: {$processed}");
        $this->line("Rows changed: {$changed}");
        $this->line("Status normalized: {$statusNormalized}");
        $this->line("Invalid Sold->IN Stock normalized: {$invalidSoldNormalized}");
        $this->line("Sold out month updated: {$monthUpdated}");
        $this->line("Duration days updated: {$durationDaysUpdated}");
        $this->line("Duration price updated: {$durationPriceUpdated}");

        if ($dryRun) {
            $this->warn('Dry run mode enabled: no records were written.');
        }

        return self::SUCCESS;
    }
}

