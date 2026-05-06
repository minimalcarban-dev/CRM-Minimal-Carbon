<?php

use App\Models\Admin;
use App\Models\JewelleryStock;
use App\Models\MetalType;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeJewelleryPermission(string $slug, string $name): Permission
{
    return Permission::updateOrCreate(
        ['slug' => $slug],
        [
            'name' => $name,
            'category' => 'jewellery_stock',
            'description' => $name,
        ]
    );
}

function makeJewelleryAdminWithPermissions(string $email, array $permissionSlugs = [], bool $isSuper = false): Admin
{
    $admin = Admin::create([
        'name' => $email,
        'email' => $email,
        'password' => bcrypt('secret'),
        'phone_number' => '9999999999',
        'is_super' => $isSuper,
    ]);

    if (!empty($permissionSlugs)) {
        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id')->all();
        $admin->permissions()->sync($permissionIds);
        $admin->clearPermissionCache();
    }

    return $admin->fresh();
}

function makeJewelleryStockItem(string $sku, float $purchasePrice = 4321.99, float $sellingPrice = 5000.00): JewelleryStock
{
    $metal = MetalType::create([
        'name' => 'Test Metal ' . $sku,
        'is_active' => true,
    ]);

    return JewelleryStock::create([
        'sku' => $sku,
        'type' => 'other',
        'name' => 'Test Jewellery ' . $sku,
        'metal_type_id' => $metal->id,
        'ring_size_id' => null,
        'weight' => 1.250,
        'quantity' => 10,
        'low_stock_threshold' => 3,
        'purchase_price' => $purchasePrice,
        'selling_price' => $sellingPrice,
        'description' => 'Test item',
    ]);
}

beforeEach(function () {
    $compiledPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . 'crm-minimal-carbon-views-' . uniqid('', true);
    if (!is_dir($compiledPath)) {
        mkdir($compiledPath, 0777, true);
    }
    config(['view.compiled' => $compiledPath]);

    makeJewelleryPermission('jewellery_stock.view', 'View Jewellery Stock');
    makeJewelleryPermission('jewellery_stock.view_pricing', 'View Jewellery Pricing');
});

it('hides jewellery pricing in index and detail when admin lacks jewellery_stock.view_pricing', function () {
    $viewer = makeJewelleryAdminWithPermissions('jewelry-viewer-no-price@example.test', ['jewellery_stock.view']);
    $item = makeJewelleryStockItem('JW-PRICE-1', 4321.99, 5000.00);

    $index = $this->actingAs($viewer, 'admin')->get(route('jewellery-stock.index'));
    $index->assertOk();
    $index->assertDontSee('4,321.99');
    $index->assertSee('Restricted');

    $show = $this->actingAs($viewer, 'admin')->get(route('jewellery-stock.show', $item));
    $show->assertOk();
    $show->assertDontSee('4,321.99');
    $show->assertSee('Restricted');
});

it('shows jewellery pricing in index and detail when admin has jewellery_stock.view_pricing', function () {
    $viewer = makeJewelleryAdminWithPermissions(
        'jewelry-viewer-with-price@example.test',
        ['jewellery_stock.view', 'jewellery_stock.view_pricing']
    );
    $item = makeJewelleryStockItem('JW-PRICE-2', 4321.99, 5000.00);

    $index = $this->actingAs($viewer, 'admin')->get(route('jewellery-stock.index'));
    $index->assertOk();
    $index->assertSee('4,321.99');

    $show = $this->actingAs($viewer, 'admin')->get(route('jewellery-stock.show', $item));
    $show->assertOk();
    $show->assertSee('4,321.99');
});

it('shows jewellery pricing to super admin', function () {
    $superAdmin = makeJewelleryAdminWithPermissions('jewelry-super@example.test', [], true);
    $item = makeJewelleryStockItem('JW-PRICE-3', 4321.99, 5000.00);

    $index = $this->actingAs($superAdmin, 'admin')->get(route('jewellery-stock.index'));
    $index->assertOk();
    $index->assertSee('4,321.99');

    $show = $this->actingAs($superAdmin, 'admin')->get(route('jewellery-stock.show', $item));
    $show->assertOk();
    $show->assertSee('4,321.99');
});

it('keeps jewellery detail form columns in sync with the database schema', function () {
    expect(Schema::hasColumns('jewellery_stocks', [
        'metal_purity',
        'closure_type_id',
        'length',
        'width',
        'diameter',
        'bale_size',
        'primary_stone_type_id',
        'primary_stone_weight',
        'primary_stone_count',
        'primary_stone_shape_id',
        'primary_stone_color_id',
        'primary_stone_clarity_id',
        'primary_stone_cut_id',
        'side_stone_type_id',
        'side_stone_weight',
        'side_stone_count',
        'certificate_number',
        'certificate_type',
        'certificate_url',
        'images',
    ]))->toBeTrue();
});
