<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\OrderDraft;
use App\Notifications\DraftCompletionReminder;
use App\Notifications\OrderProductivityReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendOrderReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reminders:send-orders {--force : Skip cooldown check}';

    /**
     * The console command description.
     */
    protected $description = 'Send order productivity and draft completion reminders to admins';

    /**
     * Cooldown period in hours between reminders for each admin.
     */
    protected int $cooldownHours = 4;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting order reminder notifications...');

        // Get all admins who should receive order reminders
        // Super admins OR admins with orders.create permission
        $admins = Admin::where('is_super', true)
            ->orWhereHas('permissions', function ($query) {
                $query->where('slug', 'orders.create');
            })
            ->get();

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($admins as $admin) {
            // Check cooldown (skip if recently notified, unless forced)
            $cacheKey = "order_reminder_{$admin->id}";

            if (!$this->option('force') && Cache::has($cacheKey)) {
                $this->line("  Skipping {$admin->name} (recently notified)");
                $skippedCount++;
                continue;
            }

            // Check for pending drafts
            $draftCount = OrderDraft::where('admin_id', $admin->id)
                ->notExpired()
                ->count();

            if ($draftCount > 0) {
                // Send draft reminder
                $admin->notify(new DraftCompletionReminder($draftCount));
                $this->info("  Sent draft reminder to {$admin->name} ({$draftCount} drafts)");
            }

            // Send productivity reminder
            $admin->notify(new OrderProductivityReminder());
            $this->info("  Sent productivity reminder to {$admin->name}");

            // Set cooldown cache
            Cache::put($cacheKey, true, now()->addHours($this->cooldownHours));
            $sentCount++;
        }

        $this->newLine();
        $this->info("Done! Sent reminders to {$sentCount} admins, skipped {$skippedCount}.");

        return Command::SUCCESS;
    }
}
