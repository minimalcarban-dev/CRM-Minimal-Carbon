<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class SettingsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'settings.manage',
                'slug' => 'settings-manage',
                'category' => 'settings',
                'description' => 'Access and manage application settings including IP security',
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        $this->command->info('✅ Settings permissions seeded successfully.');
    }
}
