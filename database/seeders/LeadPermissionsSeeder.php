<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class LeadPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'leads.view', 'slug' => 'leads-view', 'category' => 'leads', 'description' => 'View leads inbox and details'],
            ['name' => 'leads.create', 'slug' => 'leads-create', 'category' => 'leads', 'description' => 'Create new leads'],
            ['name' => 'leads.edit', 'slug' => 'leads-edit', 'category' => 'leads', 'description' => 'Edit lead details and status'],
            ['name' => 'leads.delete', 'slug' => 'leads-delete', 'category' => 'leads', 'description' => 'Delete leads'],
            ['name' => 'leads.assign', 'slug' => 'leads-assign', 'category' => 'leads', 'description' => 'Assign leads to agents'],
            ['name' => 'leads.message', 'slug' => 'leads-message', 'category' => 'leads', 'description' => 'Send messages to leads'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'slug' => $permission['slug'],
                    'category' => $permission['category'],
                    'description' => $permission['description'],
                ]
            );
        }

        $this->command->info('Lead permissions created successfully!');
    }
}
