<?php

use App\Models\Admin;
use App\Models\Order;
use App\Models\Permission;
use App\Notifications\OrderUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('other admins receive notification when an order status is updated from index page', function () {
    // 1. Setup
    Notification::fake();

    // Create permissions
    $editPerm = Permission::create([
        'name' => 'Edit Orders',
        'slug' => 'orders.edit',
        'description' => 'Allow editing orders',
    ]);

    $viewPerm = Permission::create([
        'name' => 'View Orders',
        'slug' => 'orders.view',
        'description' => 'Allow viewing orders',
    ]);

    // Admin A: The one performing the update
    $adminA = Admin::create([
        'name' => 'Admin A',
        'email' => 'admin_a@example.test',
        'password' => bcrypt('secret'),
        'phone_number' => '1234567890',
        'is_super' => false,
    ]);
    $adminA->permissions()->attach($editPerm->id);

    // Admin B: The one who should receive the notification (Super Admin)
    $adminB = Admin::create([
        'name' => 'Admin B',
        'email' => 'admin_b@example.test',
        'password' => bcrypt('secret'),
        'phone_number' => '0987654321',
        'is_super' => true,
    ]);

    // create a company for validation
    $company = \App\Models\Company::create([
        'name' => 'Test Company',
        'email' => 'co@example.test',
        'phone' => '123456789',
        'currency' => 'USD',
        'status' => 'active'
    ]);

    // Create an order
    $order = Order::create([
        'order_type' => 'ready_to_ship',
        'client_name' => 'Test Client',
        'client_address' => 'Test Address',
        'client_email' => 'client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'submitted_by' => $adminA->id,
    ]);

    // 2. Action: Update status via the index page endpoint
    $newStatus = 'r_order_shipped';
    $response = $this->actingAs($adminA, 'admin')
        ->postJson("/admin/orders/{$order->id}/update-status", [
            'status' => $newStatus,
        ]);

    // 3. Assertions
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'diamond_status' => $newStatus,
    ]);

    // Verify Admin B received the notification
    Notification::assertSentTo(
        $adminB,
        OrderUpdatedNotification::class,
        function ($notification, $channels) use ($order, $adminA, $adminB) {
            return $notification->toArray($adminB)['order_id'] === $order->id &&
                   $notification->toArray($adminB)['updated_by'] === $adminA->name;
        }
    );

    // Verify Admin A did NOT receive the notification (don't notify yourself)
    Notification::assertNotSentTo($adminA, OrderUpdatedNotification::class);
});
