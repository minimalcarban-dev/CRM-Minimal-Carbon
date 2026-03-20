<?php

use App\Models\Admin;
use App\Models\Diamond;
use App\Models\Permission;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(TestCase::class, DatabaseTransactions::class);

function makeDiamondPermission(string $slug, string $name): Permission
{
    return Permission::updateOrCreate(
        ['slug' => $slug],
        [
            'name' => $name,
            'category' => 'diamonds',
            'description' => $name,
        ]
    );
}

function makeAdminWithPermissions(string $email, array $permissionSlugs = [], bool $isSuper = false): Admin
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

function makeDiamondFor(Admin $admin, string $lotNo, string $sku, float $purchasePrice = 4321.99): Diamond
{
    return Diamond::create([
        'lot_no' => $lotNo,
        'sku' => $sku,
        'barcode_number' => 'BC-' . $lotNo,
        'admin_id' => $admin->id,
        'purchase_price' => $purchasePrice,
        'margin' => 10,
        'shipping_price' => 0,
    ]);
}

beforeEach(function () {
    makeDiamondPermission('diamonds.view', 'View Diamonds');
    makeDiamondPermission('diamonds.view_team', 'View Team Diamonds');
    makeDiamondPermission('diamonds.view_pricing', 'View Diamond Pricing');
});

it('shows only own diamonds when admin does not have diamonds.view_team', function () {
    $owner = makeAdminWithPermissions('owner@example.test', ['diamonds.view']);
    $other = makeAdminWithPermissions('other@example.test', ['diamonds.view']);

    makeDiamondFor($owner, 'LOT-OWN-1', 'SKU-OWN-1');
    makeDiamondFor($other, 'LOT-TEAM-1', 'SKU-TEAM-1');

    $response = $this->actingAs($owner, 'admin')->get(route('diamond.index'));

    $response->assertOk();
    $response->assertSee('SKU-OWN-1');
    $response->assertDontSee('SKU-TEAM-1');
});

it('shows team diamonds when admin has diamonds.view_team', function () {
    $viewer = makeAdminWithPermissions('viewer@example.test', ['diamonds.view', 'diamonds.view_team']);
    $owner = makeAdminWithPermissions('owner2@example.test', ['diamonds.view']);
    $other = makeAdminWithPermissions('other2@example.test', ['diamonds.view']);

    makeDiamondFor($owner, 'LOT-OWN-2', 'SKU-OWN-2');
    makeDiamondFor($other, 'LOT-TEAM-2', 'SKU-TEAM-2');

    $response = $this->actingAs($viewer, 'admin')->get(route('diamond.index'));

    $response->assertOk();
    $response->assertSee('SKU-OWN-2');
    $response->assertSee('SKU-TEAM-2');
});

it('forbids opening another admin diamond detail without diamonds.view_team', function () {
    $viewer = makeAdminWithPermissions('viewer2@example.test', ['diamonds.view']);
    $owner = makeAdminWithPermissions('owner3@example.test', ['diamonds.view']);
    $diamond = makeDiamondFor($owner, 'LOT-OWNER-3', 'SKU-OWNER-3');

    $response = $this->actingAs($viewer, 'admin')->get(route('diamond.show', $diamond));

    $response->assertRedirect('/admin/dashboard');
});

it('hides pricing in index and details when admin lacks diamonds.view_pricing', function () {
    $viewer = makeAdminWithPermissions('viewer3@example.test', ['diamonds.view']);
    makeDiamondFor($viewer, 'LOT-PRICE-1', 'SKU-PRICE-1', 4321.99);

    $index = $this->actingAs($viewer, 'admin')->get(route('diamond.index'));
    $index->assertOk();
    $index->assertDontSee('4,321.99');
    $index->assertSee('Restricted');

    $diamond = Diamond::where('sku', 'SKU-PRICE-1')->firstOrFail();
    $show = $this->actingAs($viewer, 'admin')->get(route('diamond.show', $diamond));
    $show->assertOk();
    $show->assertDontSee('4,321.99');
    $show->assertSee('Restricted');
});

it('shows pricing when admin has diamonds.view_pricing', function () {
    $viewer = makeAdminWithPermissions('viewer4@example.test', ['diamonds.view', 'diamonds.view_pricing']);
    $diamond = makeDiamondFor($viewer, 'LOT-PRICE-2', 'SKU-PRICE-2', 4321.99);

    $index = $this->actingAs($viewer, 'admin')->get(route('diamond.index'));
    $index->assertOk();
    $index->assertSee('4,321.99');

    $show = $this->actingAs($viewer, 'admin')->get(route('diamond.show', $diamond));
    $show->assertOk();
    $show->assertSee('4,321.99');
});
