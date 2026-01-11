<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\Invoice;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('admin with invoices.create can create invoice with items', function () {
    $createPerm = Permission::create([
        'name' => 'Create Invoices',
        'slug' => 'invoices.create',
        'description' => 'Allow creating invoices',
    ]);

    $viewPerm = Permission::create([
        'name' => 'View Invoices',
        'slug' => 'invoices.view',
        'description' => 'Allow viewing invoices',
    ]);

    $super = Admin::create([
        'name' => 'Super Admin',
        'email' => 'super@example.test',
        'password' => bcrypt('secret'),
        'is_super' => true,
    ]);

    $other = Admin::create([
        'name' => 'Worker Admin',
        'email' => 'worker@example.test',
        'password' => bcrypt('secret'),
        'is_super' => false,
    ]);

    $other->permissions()->attach([$createPerm->id, $viewPerm->id]);

    $items = [[
        'description' => 'Test Item',
        'hsn' => '7102',
        'qty' => 2,
        'rate' => 100,
    ]];

    $resp = $this->withSession(['admin_id' => $other->id, 'admin_authenticated' => true])
        ->post('/admin/invoices', [
            'invoice_number' => 'INV-2025-0001',
            'invoice_date' => now()->toDateString(),
            'billed_name' => 'Client Test',
            'billed_address' => 'Some address',
            'items' => json_encode($items),
            'tax_amount' => 0,
        ]);

    $resp->assertStatus(302);

    $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV-2025-0001', 'billed_name' => 'Client Test']);

    $invoice = Invoice::where('invoice_number', 'INV-2025-0001')->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->items)->toBeArray();
    expect($invoice->items[0]['description'])->toBe('Test Item');
});
