<?php

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\Order;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);



test('admin with permissions.create can create and see order in index search', function () {
    // Fake events and background jobs to avoid side effects like Pusher/Email
    \Illuminate\Support\Facades\Event::fake();
    \Illuminate\Support\Facades\Bus::fake();

    // Mock upload service to avoid environment issues
    $this->mock(\App\Services\CloudinaryUploadService::class, function ($mock) {
        $mock->shouldReceive('uploadFromRequest')->andReturn([]);
        $mock->shouldReceive('deleteFile')->andReturn(true);
    });

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
        'phone_number' => '5551112222',
        'is_super' => true,
    ]);

    // create normal admin
    $other = Admin::create([
        'name' => 'Worker Admin',
        'email' => 'worker@example.test',
        'password' => bcrypt('secret'),
        'phone_number' => '5553334444',
        'is_super' => false,
    ]);

    // create a company for validation
    $company = Company::create([
        'name' => 'Test Company',
        'email' => 'co@example.test',
        'phone' => '123456789',
        'currency' => 'USD',
        'status' => 'active'
    ]);

    // super grants create and view to other
    $other->permissions()->attach([$createPerm->id, $viewPerm->id]);

    // act as the other admin via session and create an order
    $resp = $this->actingAs($other, 'admin')
        ->post('/admin/orders', [
            'order_type' => 'ready_to_ship',
            'client_name' => 'Test Client',
            'client_address' => 'Test Address',
            'client_email' => 'client@example.test',
            'company_id' => $company->id,
            'diamond_status' => 'r_order_in_process',
            'gross_sell' => 1000,
        ]);

    $resp->assertStatus(302);

    $this->assertDatabaseHas('orders', ['client_name' => 'Test Client']);

    // search the index
    $listResp = $this->actingAs($other, 'admin')
        ->get('/admin/orders?search=Test');

    $listResp->assertStatus(200);
    $listResp->assertSee('Test Client');
});
