<?php

use App\Models\Admin;
use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createMeleeTestAdmin(string $email, bool $isSuper = false): Admin
{
    return Admin::create([
        'name' => $email,
        'email' => $email,
        'password' => bcrypt('secret'),
        'phone_number' => '9999999999',
        'is_super' => $isSuper,
    ]);
}

function createMeleeTestDiamond(): MeleeDiamond
{
    $category = MeleeCategory::create([
        'name' => 'Test Melee',
        'slug' => 'test-melee',
        'type' => 'natural',
        'allowed_shapes' => ['Round'],
        'is_active' => true,
    ]);

    return MeleeDiamond::create([
        'melee_category_id' => $category->id,
        'shape' => 'Round',
        'size_label' => 'round-1.0',
        'low_stock_threshold' => 10,
    ]);
}

it('blocks cost edits when admin lacks melee_diamonds.edit_cost permission', function () {
    $admin = createMeleeTestAdmin('melee-no-cost@example.test');
    $diamond = createMeleeTestDiamond();

    MeleeTransaction::create([
        'melee_diamond_id' => $diamond->id,
        'transaction_type' => 'in',
        'pieces' => 100,
        'carat_weight' => 1.000,
        'price_per_ct' => 100.00,
        'reference_type' => 'manual',
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->putJson(route('melee.update', $diamond->id), [
            'shape' => 'Round',
            'size' => '1.0',
            'last_price_per_ct' => 120.00,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);

    $latestInTx = MeleeTransaction::where('melee_diamond_id', $diamond->id)
        ->where('transaction_type', 'in')
        ->latest()
        ->first();

    expect((float) $latestInTx->price_per_ct)->toBe(100.0);
});

it('updates avg cost and dependent values when authorized admin edits stock-in cost', function () {
    $admin = createMeleeTestAdmin('melee-cost-editor@example.test');

    $permission = Permission::updateOrCreate(
        ['slug' => 'melee_diamonds.edit_cost'],
        [
            'name' => 'Edit Melee Cost',
            'category' => 'melee_diamonds',
            'description' => 'Can edit manual melee stock IN cost ($/ct) from transaction history',
        ]
    );
    $admin->permissions()->sync([$permission->id]);
    $admin->clearPermissionCache();

    $diamond = createMeleeTestDiamond();

    MeleeTransaction::create([
        'melee_diamond_id' => $diamond->id,
        'transaction_type' => 'in',
        'pieces' => 100,
        'carat_weight' => 1.000,
        'price_per_ct' => 100.00,
        'reference_type' => 'manual',
        'created_by' => $admin->id,
        'created_at' => Carbon::now()->subSecond(),
        'updated_at' => Carbon::now()->subSecond(),
    ]);

    $transactionToEdit = MeleeTransaction::create([
        'melee_diamond_id' => $diamond->id,
        'transaction_type' => 'in',
        'pieces' => 100,
        'carat_weight' => 1.000,
        'price_per_ct' => 200.00,
        'reference_type' => 'manual',
        'created_by' => $admin->id,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->putJson(route('melee.update', $diamond->id), [
            'shape' => 'Round',
            'size' => '1.0',
            'last_price_per_ct' => 300.00,
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $diamond->refresh();
    $transactionToEdit->refresh();

    expect((float) $transactionToEdit->price_per_ct)->toBe(300.0);
    expect((float) $diamond->purchase_price_per_ct)->toBe(200.0);
    expect((float) $diamond->available_carat_weight)->toBe(2.0);
    expect((float) $diamond->total_price)->toBe(400.0);
});
