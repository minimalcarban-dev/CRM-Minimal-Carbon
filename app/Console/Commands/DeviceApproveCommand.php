<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\AllowedIp;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DeviceApproveCommand extends Command
{
    protected $signature = 'device:approve {email : The admin email to approve}';

    protected $description = 'Emergency: Manually create a trusted device token for an admin so they can regain access';

    public function handle(): int
    {
        $email = $this->argument('email');

        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            $this->error("❌ No admin found with email: {$email}");
            return 1;
        }

        $deviceToken = Str::random(64);

        $record = AllowedIp::create([
            'ip_address'   => '0.0.0.0', // Placeholder — middleware will update on first use
            'device_token' => $deviceToken,
            'user_agent'   => null, // Will be captured on first use
            'last_used_at' => now(),
            'city'         => null,
            'country'      => null,
            'label'        => "CLI Approved ({$admin->name})",
            'is_active'    => true,
            'added_by'     => $admin->id,
        ]);

        // Audit Log entry
        \App\Services\AuditLogger::log('Device Approved (CLI)', $record, $admin->id, [], [
            'ip_address' => $record->ip_address,
            'label' => $record->label,
            'is_active' => $record->is_active,
            'user_agent' => $record->user_agent,
            'last_used_at' => optional($record->last_used_at)?->toDateTimeString(),
            'city' => $record->city,
            'country' => $record->country,
            'added_by' => $record->added_by,
        ]);

        $this->newLine();
        $this->info('✅ Device trust token generated successfully!');
        $this->newLine();

        $this->table(['Field', 'Value'], [
            ['Admin',  $admin->name . ' <' . $admin->email . '>'],
            ['Token',  $deviceToken],
            ['Record', "AllowedIp #{$record->id}"],
        ]);

        $this->newLine();
        $this->warn('⚠️  To activate access, the admin must set this cookie in their browser:');
        $this->line("   Cookie Name:  device_trust_token");
        $this->line("   Cookie Value: {$deviceToken}");
        $this->newLine();
        $this->info('💡 Tip: Use browser DevTools → Application → Cookies → Add the cookie above.');
        $this->info('   On the first request, the middleware will record browser and geo details for audit only.');

        return 0;
    }
}
