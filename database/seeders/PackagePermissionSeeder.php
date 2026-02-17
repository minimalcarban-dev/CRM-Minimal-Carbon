<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackagePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perms = [
            // Packages
            ['name' => 'View Packages', 'slug' => 'packages.view', 'description' => 'View package list and details', 'category' => 'packages'],
            ['name' => 'Create Packages', 'slug' => 'packages.create', 'description' => 'Issue new packages', 'category' => 'packages'],
            ['name' => 'Edit Packages', 'slug' => 'packages.edit', 'description' => 'Edit existing packages (if applicable)', 'category' => 'packages'], // Though mostly we just return or delete
            ['name' => 'Delete Packages', 'slug' => 'packages.delete', 'description' => 'Delete package records (soft delete)', 'category' => 'packages'],
            ['name' => 'Return Packages', 'slug' => 'packages.return', 'description' => 'Mark packages as returned', 'category' => 'packages'],
        ];

        foreach ($perms as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
