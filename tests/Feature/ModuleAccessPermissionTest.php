<?php

use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(\Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Ensure standard permissions are created in database
    $this->goldViewPerm = Permission::updateOrCreate(
        ['slug' => 'gold_tracking.view'],
        ['name' => 'View Gold Tracking', 'description' => 'Can view gold tracking', 'category' => 'gold_tracking']
    );

    $this->meleeViewPerm = Permission::updateOrCreate(
        ['slug' => 'melee_diamonds.view'],
        ['name' => 'View Melee Diamonds', 'description' => 'Can view melee diamonds', 'category' => 'melee_diamonds']
    );
});

it('denies gold tracking module access to admin without permission', function () {
    /** @var Admin $admin */
    $admin = Admin::factory()->create([
        'is_super' => false,
    ]);

    $this->actingAs($admin, 'admin');

    // Should redirect to admin.dashboard since permission is missing
    $response = $this->get('/admin/gold-tracking');
    $response->assertRedirect(route('admin.dashboard'));
    $response->assertSessionHas('error');
});

it('allows gold tracking module access to admin with permission', function () {
    /** @var Admin $admin */
    $admin = Admin::factory()->create([
        'is_super' => false,
    ]);

    $admin->permissions()->attach($this->goldViewPerm->id);
    $admin->clearPermissionCache();

    $this->actingAs($admin, 'admin');

    $response = $this->get('/admin/gold-tracking');
    $response->assertStatus(200);
});

it('denies melee diamonds module access to admin without permission', function () {
    /** @var Admin $admin */
    $admin = Admin::factory()->create([
        'is_super' => false,
    ]);

    $this->actingAs($admin, 'admin');

    // Should redirect to admin.dashboard since permission is missing
    $response = $this->get('/admin/melee');
    $response->assertRedirect(route('admin.dashboard'));
    $response->assertSessionHas('error');
});

it('allows melee diamonds module access to admin with permission', function () {
    /** @var Admin $admin */
    $admin = Admin::factory()->create([
        'is_super' => false,
    ]);

    $admin->permissions()->attach($this->meleeViewPerm->id);
    $admin->clearPermissionCache();

    $this->actingAs($admin, 'admin');

    $response = $this->get('/admin/melee');
    $response->assertStatus(200);
});

it('restricts super admin on strict prefix modules but allows them on others', function () {
    /** @var Admin $admin */
    $admin = Admin::factory()->create([
        'is_super' => true,
        'email' => 'super@example.com', // Normal super admin email (not God Admin)
    ]);

    $this->actingAs($admin, 'admin');

    // Gold tracking has a 'gold_tracking.' strict prefix, so normal super admin gets redirected
    $responseGold = $this->get('/admin/gold-tracking');
    $responseGold->assertRedirect(route('admin.dashboard'));

    // Melee diamonds does not have a strict prefix, so super admin is allowed
    $responseMelee = $this->get('/admin/melee');
    $responseMelee->assertStatus(200);
});

it('allows god admin to access all modules including strict prefixes without explicit permissions', function () {
    /** @var Admin $admin */
    $admin = Admin::factory()->create([
        'is_super' => true,
        'email' => config('auth.god_admin_email') ?? 'admin@omgems.com',
    ]);

    $this->actingAs($admin, 'admin');

    $responseGold = $this->get('/admin/gold-tracking');
    $responseGold->assertStatus(200);

    $responseMelee = $this->get('/admin/melee');
    $responseMelee->assertStatus(200);
});

it('returns human-friendly permission names in JSON responses when unauthorized', function () {
    /** @var Admin $admin */
    $admin = Admin::factory()->create([
        'is_super' => false,
    ]);

    // Attach view permission so the request passes the outer group middleware
    $admin->permissions()->attach($this->meleeViewPerm->id);
    $admin->clearPermissionCache();

    $this->actingAs($admin, 'admin');

    // Make an AJAX call requesting JSON to a route that requires 'melee_diamonds.create'
    $response = $this->postJson('/admin/melee/add-shape');
    
    $response->assertStatus(403);
    $response->assertJson([
        'message' => 'You do not have permission to: Create Melee Diamonds.',
        'error' => 'forbidden',
        'permission' => 'melee_diamonds.create'
    ]);
});

it('returns human-friendly permission names in session flash when unauthorized', function () {
    /** @var Admin $admin */
    $admin = Admin::factory()->create([
        'is_super' => false,
    ]);

    $this->actingAs($admin, 'admin');

    // Make a normal GET request to a route that requires 'gold_tracking.view'
    $response = $this->get('/admin/gold-tracking');
    
    $response->assertRedirect(route('admin.dashboard'));
    $response->assertSessionHas('error', function ($value) {
        return str_contains($value, 'Your permission to: <strong>View Gold Tracking</strong> may have changed');
    });
});
