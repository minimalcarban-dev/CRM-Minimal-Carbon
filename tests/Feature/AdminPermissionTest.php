<?php

use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

uses(Tests\CreatesApplication::class);

it('allows super admin to view and update permissions for another admin', function () {
    // create super admin
    $super = Admin::create([
        'name' => 'Super Admin',
        'email' => 'super@example.com',
        'password' => Hash::make('supersafe'),
        'phone_number' => '0000000001',
        'is_super' => true,
    ]);

    // create target admin
    $target = Admin::create([
        'name' => 'Target Admin',
        'email' => 'target@example.com',
        'password' => Hash::make('targetpass'),
        'phone_number' => '0000000002',
        'is_super' => false,
    ]);

    // create a permission
    $perm = Permission::create([
        'name' => 'Test Permission',
        'slug' => 'test.permission',
        'description' => 'Used in feature tests',
    ]);

    // login as super admin via guard
    Auth::guard('admin')->loginUsingId($super->id);

    // visit permission page
    $response = $this->get(route('admins.permissions.show', $target));
    $response->assertStatus(200);

    // update permissions
    $response = $this->post(route('admins.permissions.update', $target), [
        'permissions' => [$perm->id],
    ]);

    $response->assertRedirect(route('admins.permissions.show', $target));
    $this->assertTrue($target->fresh()->permissions->contains($perm->id));
});

it('prevents normal admin without assign permission from editing permissions', function () {
    $normal = Admin::create([
        'name' => 'Normal Admin',
        'email' => 'normal@example.com',
        'password' => Hash::make('normalpass'),
        'phone_number' => '0000000003',
        'is_super' => false,
    ]);

    $target = Admin::create([
        'name' => 'Target 2',
        'email' => 'target2@example.com',
        'password' => Hash::make('targetpass2'),
        'phone_number' => '0000000004',
        'is_super' => false,
    ]);

    Auth::guard('admin')->loginUsingId($normal->id);

    $response = $this->get(route('admins.permissions.show', $target));
    $response->assertStatus(403);
});
