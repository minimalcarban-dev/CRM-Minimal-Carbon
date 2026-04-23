<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class MeleePermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'slug' => 'melee_diamonds.view',
                'name' => 'View Melee Inventory',
                'description' => 'Can view the melee diamond inventory dashboard',
            ],
            [
                'slug' => 'melee_diamonds.create',
                'name' => 'Create Melee Stock',
                'description' => 'Can add new melee diamond categories or parcels',
            ],
            [
                'slug' => 'melee_diamonds.edit',
                'name' => 'Edit Melee Stock',
                'description' => 'Can edit melee diamond details',
            ],
            [
                'slug' => 'melee_diamonds.delete',
                'name' => 'Delete Melee Stock',
                'description' => 'Can delete melee diamond records',
            ],
            [
                'slug' => 'melee_diamonds.transaction',
                'name' => 'Manage Stock Transactions',
                'description' => 'Can perform IN/OUT stock transactions',
            ],
            [
                'slug' => 'melee_diamonds.edit_cost',
                'name' => 'Edit Melee Cost',
                'description' => 'Can edit manual stock IN cost ($/ct) from transaction history',
            ],
        ];

        foreach ($permissions as $perm) {
            if (!Permission::where('slug', $perm['slug'])->exists()) {
                Permission::create([
                    'slug' => $perm['slug'],
                    'name' => $perm['name'],
                    'category' => 'Melee Inventory', // Grouping for UI
                    'description' => $perm['description'],
                ]);
            }
        }

        // Note: Super Admins have access via is_super flag, so no role assignment needed.
    }
}
