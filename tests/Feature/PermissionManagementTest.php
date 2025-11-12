<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\Permission;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('admin granted permissions.create can create permission', function () {
    // create the permission that represents ability to create permissions
    $createPerm = Permission::create([
        'name' => 'Create Permissions',
        'slug' => 'permissions.create',
        'description' => 'Allow creating permissions',
    ]);

    // create a super admin who will grant the permission
    $super = Admin::create([
        'name' => 'Super Admin',
        'email' => 'super@example.test',
        'password' => bcrypt('secret'),
        'is_super' => true,
    ]);

    // create another admin who will be granted the create permission
    $other = Admin::create([
        'name' => 'Worker Admin',
        'email' => 'worker@example.test',
        'password' => bcrypt('secret'),
        'is_super' => false,
    ]);

    // super attaches the create permission to the other admin
    $other->permissions()->attach($createPerm->id);

    // simulate logged-in session as the granted admin and attempt to create a new permission
    $response = $this->withSession(['admin_id' => $other->id, 'admin_authenticated' => true])
        ->post('/admin/permissions', [
            'name' => 'Test Created Permission',
            'slug' => 'test.permission.created',
            'description' => 'Created by test',
        ]);

    // should redirect to permissions.index on success
    $response->assertStatus(302);

    // database should contain the new permission
    $this->assertDatabaseHas('permissions', [
        'slug' => 'test.permission.created',
    ]);
});
