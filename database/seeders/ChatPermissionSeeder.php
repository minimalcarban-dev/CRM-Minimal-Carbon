<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class                                              ChatPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $chatPermission = Permission::where('slug', 'chat.access')->first();
        if (!$chatPermission) return;

        $admins = Admin::all();
        foreach ($admins as $admin) {
            if (!$admin->hasPermission('chat.access')) {
                $admin->permissions()->attach($chatPermission->id);
            }
        }
    }
}