<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\Order;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('admin with permissions.create can create and see order in index search', function () {
    // seed the orders.create permission
    $createPerm = Permission::create([
        'name' => 'Create Orders',
        'slug' => 'orders.create',
        'description' => 'Allow creating orders',
    ]);

    $viewPerm = Permission::create([
        'name' => 'View Orders',
        'slug' => 'orders.view',
        'description' => 'Allow viewing orders',
    ]);

    // create super admin to assign
    $super = Admin::create([
        'name' => 'Super Admin',
        'email' => 'super@example.test',
        'password' => bcrypt('secret'),
        'is_super' => true,
    ]);

    // create normal admin
    $other = Admin::create([
        'name' => 'Worker Admin',
        'email' => 'worker@example.test',
        'password' => bcrypt('secret'),
        'is_super' => false,
    ]);

    // super grants create and view to other
    $other->permissions()->attach([$createPerm->id, $viewPerm->id]);

    // act as the other admin via session and create an order
    $resp = $this->withSession(['admin_id' => $other->id, 'admin_authenticated' => true])
        ->post('/admin/orders', [
            'title' => 'Test Order',
            'description' => 'Created in test',
            'total_amount' => 123.45,
        ]);

    $resp->assertStatus(302);

    $this->assertDatabaseHas('orders', ['title' => 'Test Order']);

    // search the index
    $listResp = $this->withSession(['admin_id' => $other->id, 'admin_authenticated' => true])
        ->get('/admin/orders?search=Test');

    $listResp->assertStatus(200);
    $listResp->assertSee('Test Order');
});
