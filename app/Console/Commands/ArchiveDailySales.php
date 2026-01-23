<?php

namespace App\Console\Commands;

use App\Services\CompanySalesReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveDailySales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:archive-daily {--date= : Specific date to archive (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive daily sales data for all companies to history table';

    /**
     * Execute the console command.
     */
    public function handle(CompanySalesReportService $service): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $this->info("Archiving sales for: {$date->toDateString()}");

        try {
            $archived = $service->archiveDailySales($date);

            if (empty($archived)) {
                $this->warn('No sales data to archive for this date.');
                return Command::SUCCESS;
            }

            foreach ($archived as $record) {
                $this->line("  Company #{$record['company_id']}: {$record['orders']} orders, \${$record['revenue']}");
            }

            $this->info('Archived ' . count($archived) . ' company records.');

            Log::info('Daily sales archived', [
                'date' => $date->toDateString(),
                'companies_count' => count($archived),
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to archive: {$e->getMessage()}");
            Log::error('Daily sales archive failed', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
            ]);
            return Command::FAILURE;
        }
    }
}
