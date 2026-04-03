<?php

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeOrderPaymentAdmin(): Admin
{
    $admin = Admin::create([
        'name' => 'Payment Admin',
        'email' => 'payment-admin@example.test',
        'password' => Hash::make('secret'),
        'phone_number' => '5550000001',
        'is_super' => false,
    ]);

    $permissions = [
        ['name' => 'Create Orders', 'slug' => 'orders.create', 'description' => 'Create orders'],
        ['name' => 'View Orders', 'slug' => 'orders.view', 'description' => 'View orders'],
        ['name' => 'Edit Orders', 'slug' => 'orders.edit', 'description' => 'Edit orders'],
    ];

    foreach ($permissions as $permissionData) {
        $permission = Permission::create($permissionData);
        $admin->permissions()->attach($permission->id);
    }

    return $admin;
}

function makePaymentCompany(): Company
{
    return Company::create([
        'name' => 'Payment Test Co',
        'email' => 'company-payment@example.test',
        'phone' => '5551234567',
        'currency' => 'USD',
        'status' => 'active',
    ]);
}

it('stores a partial order payment summary on create', function () {
    $admin = makeOrderPaymentAdmin();
    $company = makePaymentCompany();

    $response = $this->actingAs($admin, 'admin')->post(route('orders.store'), [
        'order_type' => 'ready_to_ship',
        'client_name' => 'Partial Client',
        'client_address' => '123 Market Street',
        'client_email' => 'partial-client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'gross_sell' => 1000,
        'payment_status' => 'partial',
        'amount_received' => 700,
        'amount_due' => 300,
    ]);

    $response->assertRedirect(route('orders.index'));

    $order = Order::latest()->first();
    expect($order)->not->toBeNull();
    expect($order->payment_status)->toBe('partial');
    expect((float) $order->amount_received_total)->toBe(700.0);
    expect((float) $order->amount_due_total)->toBe(300.0);

    $this->assertDatabaseHas('order_payments', [
        'order_id' => $order->id,
        'amount' => 700.00,
    ]);
});

it('defaults orders to fully paid when payment fields are omitted', function () {
    $admin = makeOrderPaymentAdmin();
    $company = makePaymentCompany();

    $response = $this->actingAs($admin, 'admin')->post(route('orders.store'), [
        'order_type' => 'ready_to_ship',
        'client_name' => 'Legacy Client',
        'client_address' => '88 Heritage Road',
        'client_email' => 'legacy-client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'gross_sell' => 1200,
    ]);

    $response->assertRedirect(route('orders.index'));

    $order = Order::latest()->first();
    expect($order->payment_status)->toBe('full');
    expect((float) $order->amount_received_total)->toBe(1200.0);
    expect((float) $order->amount_due_total)->toBe(0.0);
    expect(OrderPayment::where('order_id', $order->id)->count())->toBe(1);
});

it('records an additional payment and closes the remaining balance', function () {
    $admin = makeOrderPaymentAdmin();
    $company = makePaymentCompany();

    $order = Order::create([
        'order_type' => 'ready_to_ship',
        'client_name' => 'Due Client',
        'client_address' => '45 Balance Lane',
        'client_email' => 'due-client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'gross_sell' => 1000,
        'payment_status' => 'due',
        'amount_received' => 0,
        'amount_due' => 1000,
        'submitted_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin, 'admin')->post(route('orders.payments.store', $order->id), [
        'amount' => 400,
        'payment_method' => 'cash',
        'reference_number' => 'RCPT-400',
        'notes' => 'First receipt',
    ]);

    $response->assertRedirect(route('orders.show', $order->id));

    $order->refresh();
    expect($order->payment_status)->toBe('partial');
    expect((float) $order->amount_received_total)->toBe(400.0);
    expect((float) $order->amount_due_total)->toBe(600.0);
    expect(OrderPayment::where('order_id', $order->id)->count())->toBe(1);

    $auditLog = AuditLog::where('auditable_type', Order::class)
        ->where('auditable_id', $order->id)
        ->latest('id')
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->new_values ?? [])->toHaveKey('Amount Received');
    expect($auditLog->new_values ?? [])->toHaveKey('Payment Entry');
});

it('allows payment updates from the edit form and records the new amount', function () {
    $admin = makeOrderPaymentAdmin();
    $company = makePaymentCompany();

    $order = Order::create([
        'order_type' => 'ready_to_ship',
        'client_name' => 'Edit Payment Client',
        'client_address' => '22 Update Street',
        'client_email' => 'edit-payment-client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'gross_sell' => 1000,
        'payment_status' => 'due',
        'amount_received' => 0,
        'amount_due' => 1000,
        'submitted_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin, 'admin')->put(route('orders.update', $order->id), [
        'order_type' => 'ready_to_ship',
        'client_name' => 'Edit Payment Client',
        'client_address' => '22 Update Street',
        'client_email' => 'edit-payment-client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'gross_sell' => 1000,
        'payment_status' => 'partial',
        'amount_received' => 400,
        'amount_due' => 600,
    ]);

    $response->assertRedirect(route('orders.index'));

    $order->refresh();

    expect($order->payment_status)->toBe('partial');
    expect((float) $order->amount_received_total)->toBe(400.0);
    expect((float) $order->amount_due_total)->toBe(600.0);
    expect(OrderPayment::where('order_id', $order->id)->count())->toBe(1);
});

it('counts only received amount in company sales totals', function () {
    $admin = makeOrderPaymentAdmin();
    $company = makePaymentCompany();

    Order::create([
        'order_type' => 'ready_to_ship',
        'client_name' => 'Cash Client',
        'client_address' => '1 Paid Road',
        'client_email' => 'cash-client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'gross_sell' => 1000,
        'payment_status' => 'full',
        'amount_received' => 1000,
        'amount_due' => 0,
        'submitted_by' => $admin->id,
    ]);

    Order::create([
        'order_type' => 'ready_to_ship',
        'client_name' => 'Partial Sales Client',
        'client_address' => '2 Balance Road',
        'client_email' => 'partial-sales-client@example.test',
        'company_id' => $company->id,
        'diamond_status' => 'r_order_in_process',
        'gross_sell' => 1000,
        'payment_status' => 'partial',
        'amount_received' => 700,
        'amount_due' => 300,
        'submitted_by' => $admin->id,
    ]);

    $company->refresh();

    expect((float) $company->todays_sales)->toBe(1700.0);
    expect((float) $company->month_to_date_sales['total_revenue'])->toBe(1700.0);
});
