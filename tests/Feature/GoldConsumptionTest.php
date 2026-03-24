<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Factory;
use App\Models\GoldDistribution;
use App\Models\Order;
use App\Models\Client;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Permission;

class GoldConsumptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure the permission exists
        Permission::firstOrCreate(
            ['slug' => 'orders.add_gold_weight'],
            ['name' => 'Can Add Gold Weight', 'category' => 'orders', 'description' => 'Can Add Gold Weight']
        );
        Permission::firstOrCreate(
            ['slug' => 'orders.create'],
            ['name' => 'Create Orders', 'category' => 'orders', 'description' => 'Create Orders']
        );
        Permission::firstOrCreate(
            ['slug' => 'orders.edit'],
            ['name' => 'Edit Orders', 'category' => 'orders', 'description' => 'Edit Orders']
        );
        Permission::firstOrCreate(
            ['slug' => 'orders.view'],
            ['name' => 'View Orders', 'category' => 'orders', 'description' => 'View Orders']
        );
    }

    protected function makeAdmin()
    {
        $email = 'admin' . rand() . '@example.com';
        return Admin::create([
            'name' => 'Test Admin',
            'email' => $email,
            'password' => bcrypt('password'),
            'phone_number' => '1234567890',
            'is_super' => false,
        ]);
    }

    protected function makeFactory()
    {
        return Factory::create([
            'name' => 'Test Factory ' . rand(),
            'code' => 'TF' . rand(10, 99),
            'is_active' => true,
        ]);
    }

    protected function makeClient()
    {
        return Client::create([
            'name' => 'Test Client',
            'email' => 'client' . rand() . '@example.com',
            'mobile' => '0987654321',
        ]);
    }

    protected function makeCompany()
    {
        return Company::create([
            'name' => 'Test Company',
            'email' => 'company' . rand() . '@example.com',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function order_creation_records_gold_consumption()
    {
        $admin = $this->makeAdmin();
        $perms = Permission::whereIn('slug', ['orders.add_gold_weight', 'orders.create', 'orders.view'])->get();
        $admin->permissions()->sync($perms->pluck('id'));
        $admin->clearPermissionCache();

        $factory = $this->makeFactory();
        $client = $this->makeClient();
        $company = $this->makeCompany();

        // Give the factory some initial stock
        GoldDistribution::create([
            'admin_id' => $admin->id,
            'factory_id' => $factory->id,
            'type' => 'out',
            'weight_grams' => 100,
            'distribution_date' => now(),
        ]);

        $this->assertEquals(100, $factory->fresh()->current_stock);

        $response = $this->actingAs($admin, 'admin')->post(route('orders.store'), [
            'client_name' => $client->name,
            'client_email' => $client->email,
            'client_address' => 'Test Address, City, Country',
            'company_id' => $company->id,
            'order_type' => 'custom_jewellery',
            'jewellery_details' => 'Test Jewellery details here',
            'factory_id' => $factory->id,
            'gold_net_weight' => 25.5,
            // standard order fields
            'order_date' => now()->format('Y-m-d'),
            'priority' => 'normal',
            'gross_sell' => 500,
            'budget' => 500,
            'advance_received' => 0,
        ], ['Accept' => 'application/json']);

        if ($response->status() !== 200 && $response->status() !== 302) {
            dd($response->getContent());
        }
        
        if (session()->has('error')) {
            dd("Order Creation Failed with Exception:", session('error'));
        }

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(); // Usually to orders.index

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(25.5, $order->gold_net_weight);

        // Verify distribution was created
        $distribution = GoldDistribution::where('order_id', $order->id)->where('type', 'consumed')->first();
        $this->assertNotNull($distribution);
        $this->assertEquals(25.5, $distribution->weight_grams);
        $this->assertEquals($factory->id, $distribution->factory_id);

        // Verify factory stock is reduced
        $this->assertEquals(74.5, $factory->fresh()->current_stock);
    }

    /** @test */
    public function order_update_adjusts_gold_consumption()
    {
        $admin = $this->makeAdmin();
        $perms = Permission::whereIn('slug', ['orders.add_gold_weight', 'orders.create', 'orders.edit', 'orders.view'])->get();
        $admin->permissions()->sync($perms->pluck('id'));
        $admin->clearPermissionCache();

        $factory = $this->makeFactory();
        $client = $this->makeClient();
        $company = $this->makeCompany();

        // Initial factory stock = 100
        GoldDistribution::create([
            'admin_id' => $admin->id,
            'factory_id' => $factory->id,
            'type' => 'out',
            'weight_grams' => 100,
            'distribution_date' => now(),
        ]);

        // Create an order dynamically via HTTP
        $response1 = $this->actingAs($admin, 'admin')->post(route('orders.store'), [
            'client_name' => $client->name,
            'client_email' => $client->email,
            'client_address' => 'Test Address',
            'company_id' => $company->id,
            'factory_id' => $factory->id,
            'order_type' => 'custom_jewellery',
            'jewellery_details' => 'Initial details',
            'order_date' => now()->format('Y-m-d'),
            'priority' => 'normal',
            'gross_sell' => 500,
            'budget' => 500,
            'advance_received' => 0,
            'gold_net_weight' => 10,
        ], ['Accept' => 'application/json']);

        if ($response1->status() !== 200 && $response1->status() !== 302) {
            dd("Error creating order in 2nd test: ", $response1->getContent());
        }

        if (session()->has('error')) {
            dd("Order Creation Failed (Test 2) with Exception:", session('error'));
        }

        $order = Order::latest()->first();

        // Check if the order actually saved gold_net_weight
        dump("Order Gold Net Weight from DB -> ", $order->gold_net_weight);
        dump("Order Factory ID -> ", $order->factory_id);
        
        $dists = GoldDistribution::where('order_id', $order->id)->get();
        dump("Distributions for order -> ", $dists->toArray());

        // Check if consumption of 10 was recorded automatically
        $this->assertEquals(90, $factory->fresh()->current_stock);

        // Now update the order via endpoint
        $response = $this->actingAs($admin, 'admin')->put(route('orders.update', $order), [
            'client_name' => $client->name,
            'client_email' => $client->email,
            'client_address' => 'Test Address',
            'company_id' => $company->id,
            'order_type' => 'custom_jewellery',
            'jewellery_details' => 'Updated details with gold change',
            'factory_id' => $factory->id,
            'gold_net_weight' => 15, // increased by 5 (so total consumption is 15)
            'order_date' => now()->format('Y-m-d'),
            'priority' => 'normal',
            'diamond_status' => 'j_diamond_in_progress',
            'gross_sell' => 500,
            'budget' => 500,
            'advance_received' => 0,
        ]);

        $response->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertEquals(15, $order->gold_net_weight);

        // Verify existing distribution was updated or a new one created? 
        // Based on logic in controller: we delete the old consumed distribution and create a new one.
        $this->assertEquals(1, GoldDistribution::where('order_id', $order->id)->where('type', 'consumed')->count());
        $distribution = GoldDistribution::where('order_id', $order->id)->where('type', 'consumed')->first();
        
        $this->assertEquals(15, $distribution->weight_grams);
        $this->assertEquals(85, $factory->fresh()->current_stock);
    }
}
