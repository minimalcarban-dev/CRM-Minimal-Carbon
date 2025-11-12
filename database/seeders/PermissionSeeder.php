<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perms = [
            // Admins
            ['name' => 'View Admins', 'slug' => 'admins.view', 'description' => 'View admin list and details', 'category' => 'admins'],
            ['name' => 'Create Admins', 'slug' => 'admins.create', 'description' => 'Create new admin records', 'category' => 'admins'],
            ['name' => 'Edit Admins', 'slug' => 'admins.edit', 'description' => 'Edit existing admins', 'category' => 'admins'],
            ['name' => 'Delete Admins', 'slug' => 'admins.delete', 'description' => 'Delete admins', 'category' => 'admins'],
            ['name' => 'Assign Permissions', 'slug' => 'admins.assign_permissions', 'description' => 'Assign/remove permissions to/from admins', 'category' => 'admins'],

            // Chat
            ['name' => 'Access Chat', 'slug' => 'chat.access', 'description' => 'Access and use the chat system', 'category' => 'chat'],

            // Permission management
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'description' => 'View permission list and details', 'category' => 'permissions'],
            ['name' => 'Create Permissions', 'slug' => 'permissions.create', 'description' => 'Create new permissions via admin UI', 'category' => 'permissions'],
            ['name' => 'Edit Permissions', 'slug' => 'permissions.edit', 'description' => 'Edit existing permissions via admin UI', 'category' => 'permissions'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions.delete', 'description' => 'Delete permissions via admin UI', 'category' => 'permissions'],

            // Orders
            ['name' => 'View Orders', 'slug' => 'orders.view', 'description' => 'View orders list and details', 'category' => 'orders'],
            ['name' => 'Create Orders', 'slug' => 'orders.create', 'description' => 'Create new orders via admin UI', 'category' => 'orders'],
            ['name' => 'Edit Orders', 'slug' => 'orders.edit', 'description' => 'Edit orders via admin UI', 'category' => 'orders'],
            ['name' => 'Delete Orders', 'slug' => 'orders.delete', 'description' => 'Delete orders via admin UI', 'category' => 'orders'],

            // Attribute types
            ['name' => 'View Metal Types', 'slug' => 'metal_types.view', 'description' => 'View metal types', 'category' => 'metal_types'],
            ['name' => 'Create Metal Types', 'slug' => 'metal_types.create', 'description' => 'Create metal types', 'category' => 'metal_types'],
            ['name' => 'Edit Metal Types', 'slug' => 'metal_types.edit', 'description' => 'Edit metal types', 'category' => 'metal_types'],
            ['name' => 'Delete Metal Types', 'slug' => 'metal_types.delete', 'description' => 'Delete metal types', 'category' => 'metal_types'],

            ['name' => 'View Setting Types', 'slug' => 'setting_types.view', 'description' => 'View setting types', 'category' => 'setting_types'],
            ['name' => 'Create Setting Types', 'slug' => 'setting_types.create', 'description' => 'Create setting types', 'category' => 'setting_types'],
            ['name' => 'Edit Setting Types', 'slug' => 'setting_types.edit', 'description' => 'Edit setting types', 'category' => 'setting_types'],
            ['name' => 'Delete Setting Types', 'slug' => 'setting_types.delete', 'description' => 'Delete setting types', 'category' => 'setting_types'],

            ['name' => 'View Closure Types', 'slug' => 'closure_types.view', 'description' => 'View closure types', 'category' => 'closure_types'],
            ['name' => 'Create Closure Types', 'slug' => 'closure_types.create', 'description' => 'Create closure types', 'category' => 'closure_types'],
            ['name' => 'Edit Closure Types', 'slug' => 'closure_types.edit', 'description' => 'Edit closure types', 'category' => 'closure_types'],
            ['name' => 'Delete Closure Types', 'slug' => 'closure_types.delete', 'description' => 'Delete closure types', 'category' => 'closure_types'],

            ['name' => 'View Ring Sizes', 'slug' => 'ring_sizes.view', 'description' => 'View ring sizes', 'category' => 'ring_sizes'],
            ['name' => 'Create Ring Sizes', 'slug' => 'ring_sizes.create', 'description' => 'Create ring sizes', 'category' => 'ring_sizes'],
            ['name' => 'Edit Ring Sizes', 'slug' => 'ring_sizes.edit', 'description' => 'Edit ring sizes', 'category' => 'ring_sizes'],
            ['name' => 'Delete Ring Sizes', 'slug' => 'ring_sizes.delete', 'description' => 'Delete ring sizes', 'category' => 'ring_sizes'],

            ['name' => 'View Stone Types', 'slug' => 'stone_types.view', 'description' => 'View stone types', 'category' => 'stone_types'],
            ['name' => 'Create Stone Types', 'slug' => 'stone_types.create', 'description' => 'Create stone types', 'category' => 'stone_types'],
            ['name' => 'Edit Stone Types', 'slug' => 'stone_types.edit', 'description' => 'Edit stone types', 'category' => 'stone_types'],
            ['name' => 'Delete Stone Types', 'slug' => 'stone_types.delete', 'description' => 'Delete stone types', 'category' => 'stone_types'],

            ['name' => 'View Stone Shapes', 'slug' => 'stone_shapes.view', 'description' => 'View stone shapes', 'category' => 'stone_shapes'],
            ['name' => 'Create Stone Shapes', 'slug' => 'stone_shapes.create', 'description' => 'Create stone shapes', 'category' => 'stone_shapes'],
            ['name' => 'Edit Stone Shapes', 'slug' => 'stone_shapes.edit', 'description' => 'Edit stone shapes', 'category' => 'stone_shapes'],
            ['name' => 'Delete Stone Shapes', 'slug' => 'stone_shapes.delete', 'description' => 'Delete stone shapes', 'category' => 'stone_shapes'],

            ['name' => 'View Stone Colors', 'slug' => 'stone_colors.view', 'description' => 'View stone colors', 'category' => 'stone_colors'],
            ['name' => 'Create Stone Colors', 'slug' => 'stone_colors.create', 'description' => 'Create stone colors', 'category' => 'stone_colors'],
            ['name' => 'Edit Stone Colors', 'slug' => 'stone_colors.edit', 'description' => 'Edit stone colors', 'category' => 'stone_colors'],
            ['name' => 'Delete Stone Colors', 'slug' => 'stone_colors.delete', 'description' => 'Delete stone colors', 'category' => 'stone_colors'],
        ];

        foreach ($perms as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
