<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class InvoicePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            ['name' => 'View Invoices', 'slug' => 'invoices.view', 'description' => 'View invoices'],
            ['name' => 'Create Invoices', 'slug' => 'invoices.create', 'description' => 'Create invoices'],
            ['name' => 'Edit Invoices', 'slug' => 'invoices.edit', 'description' => 'Edit invoices'],
            ['name' => 'Delete Invoices', 'slug' => 'invoices.delete', 'description' => 'Delete invoices'],
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
