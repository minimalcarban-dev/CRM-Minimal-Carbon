<?php

namespace App\Console\Commands;

use App\Models\AllowedIp;
use App\Models\AppSetting;
use Illuminate\Console\Command;

class IpResetCommand extends Command
{
    protected $signature = 'ip:reset {--force : Skip confirmation prompt}';

    protected $description = 'Emergency: Disable IP restriction and clear all whitelisted IPs';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  This will DISABLE IP restriction and DELETE all whitelisted IPs. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Disable IP restriction
        AppSetting::set('ip_restriction_enabled', 'false');
        $this->info('✅ IP restriction has been DISABLED.');

        // Clear all whitelisted IPs
        $count = AllowedIp::count();
        AllowedIp::truncate();
        $this->info("✅ Cleared {$count} IP(s) from the whitelist.");

        $this->newLine();
        $this->info('🔓 Site is now accessible from any IP address.');
        $this->info('   Navigate to Settings > IP Security to reconfigure.');

        return 0;
    }
}
