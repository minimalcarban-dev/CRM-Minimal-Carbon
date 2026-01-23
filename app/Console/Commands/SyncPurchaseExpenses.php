<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\Purchase;
use Illuminate\Console\Command;

class SyncPurchaseExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:purchase-expenses {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync expense records with their linked purchases to fix any mismatched values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('');
        $this->info('Checking Purchase-Expense sync status...');
        $this->info('');

        // Get all purchases with linked expenses
        $purchases = Purchase::with('expense')
            ->whereNotNull('expense_id')
            ->completed()
            ->get();

        $this->info("Found {$purchases->count()} completed purchases with linked expenses");

        $mismatched = 0;
        $synced = 0;
        $orphaned = 0;

        foreach ($purchases as $purchase) {
            if (!$purchase->expense) {
                $this->warn("⚠️  Purchase #{$purchase->id}: Linked expense #{$purchase->expense_id} not found (Orphaned link)");
                $orphaned++;
                continue;
            }

            $expense = $purchase->expense;

            // Check for mismatches
            $hasMismatch = false;
            $changes = [];

            if ($purchase->total_price != $expense->amount) {
                $hasMismatch = true;
                $changes[] = "Amount: ₹{$expense->amount} → ₹{$purchase->total_price}";
            }

            if ($purchase->purchase_date->format('Y-m-d') != $expense->date->format('Y-m-d')) {
                $hasMismatch = true;
                $changes[] = "Date: {$expense->date->format('Y-m-d')} → {$purchase->purchase_date->format('Y-m-d')}";
            }

            $expectedTitle = "Diamond Purchase - {$purchase->diamond_type}";
            if ($expense->title != $expectedTitle) {
                $hasMismatch = true;
                $changes[] = "Title: {$expense->title} → {$expectedTitle}";
            }

            if ($hasMismatch) {
                $mismatched++;
                $this->warn("❌ Purchase #{$purchase->id} <-> Expense #{$expense->id}: MISMATCH");
                foreach ($changes as $change) {
                    $this->line("   └─ {$change}");
                }

                if (!$isDryRun) {
                    $expense->update([
                        'date' => $purchase->purchase_date,
                        'title' => "Diamond Purchase - {$purchase->diamond_type}",
                        'amount' => $purchase->total_price,
                        'payment_method' => $purchase->payment_mode,
                        'paid_to_received_from' => $purchase->party_name,
                        'reference_number' => $purchase->invoice_number,
                        'notes' => "Auto-synced from Purchase #{$purchase->id}. " . ($purchase->notes ?? ''),
                    ]);
                    $synced++;
                    $this->info("   ✓ Synced!");
                }
            } else {
                $this->line("✓ Purchase #{$purchase->id} <-> Expense #{$expense->id}: OK (₹{$purchase->total_price})");
            }
        }

        $this->info('');
        $this->info('=== Summary ===');
        $this->info("Total checked: {$purchases->count()}");
        $this->info("Mismatched: {$mismatched}");
        $this->info("Orphaned links: {$orphaned}");

        if (!$isDryRun) {
            $this->info("Synced: {$synced}");
        } else {
            $this->info('');
            $this->info('💡 Run without --dry-run to apply the changes');
        }

        return Command::SUCCESS;
    }
}
