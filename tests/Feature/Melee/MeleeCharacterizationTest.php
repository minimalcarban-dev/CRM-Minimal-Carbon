<?php

namespace Tests\Feature\Melee;

use App\Models\Admin;
use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Characterization Tests — Sprint 1 Safety Foundation
 *
 * These tests document CURRENT behaviour, not ideal behaviour.
 * They exist to catch accidental regressions in later sprints.
 * If a test breaks, it means production behaviour changed — investigate before fixing.
 */
class MeleeCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): Admin
    {
        return Admin::factory()->create();
    }

    private function superAdmin(): Admin
    {
        return Admin::factory()->super()->create();
    }

    // =========================================================================
    // MeleeDiamond Boot Hook — saving() Characterization
    // =========================================================================

    #[Test]
    public function boot_hook_auto_calculates_sold_pieces_on_save(): void
    {
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'     => 100,
            'available_pieces' => 70,
        ]);

        $diamond->refresh();

        // Current behaviour: sold_pieces = max(0, total - available)
        $this->assertEquals(30, $diamond->sold_pieces);
    }

    #[Test]
    public function boot_hook_auto_calculates_total_price_on_save(): void
    {
        $diamond = MeleeDiamond::factory()->create([
            'available_carat_weight' => 10.000,
            'purchase_price_per_ct'  => 25.00,
        ]);

        $diamond->refresh();

        // Current behaviour: total_price = available_carat_weight * purchase_price_per_ct
        $this->assertEquals(250.00, (float) $diamond->total_price);
    }

    #[Test]
    public function boot_hook_sets_status_out_of_stock_when_zero_pieces(): void
    {
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'     => 100,
            'available_pieces' => 0,
        ]);

        $diamond->refresh();

        $this->assertEquals('out_of_stock', $diamond->status);
    }

    #[Test]
    public function boot_hook_sets_status_low_stock_at_threshold(): void
    {
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'        => 100,
            'available_pieces'    => 8,
            'low_stock_threshold' => 10,
        ]);

        $diamond->refresh();

        // Current behaviour: available (8) <= threshold (10) → low_stock
        $this->assertEquals('low_stock', $diamond->status);
    }

    #[Test]
    public function boot_hook_sets_status_in_stock_above_threshold(): void
    {
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'        => 200,
            'available_pieces'    => 150,
            'low_stock_threshold' => 10,
        ]);

        $diamond->refresh();

        // Current behaviour: available (150) > threshold (10) → in_stock
        $this->assertEquals('in_stock', $diamond->status);
    }

    #[Test]
    public function boot_hook_recalculates_on_update(): void
    {
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'     => 100,
            'available_pieces' => 50,
        ]);

        // Now update available_pieces
        $diamond->available_pieces = 20;
        $diamond->save();
        $diamond->refresh();

        // sold_pieces should be recalculated
        $this->assertEquals(80, $diamond->sold_pieces);
    }

    // =========================================================================
    // MeleeDiamond Factory + Persistence
    // =========================================================================

    #[Test]
    public function it_stores_a_melee_diamond_with_all_current_fields(): void
    {
        $category = MeleeCategory::factory()->labGrown()->create();

        $diamond = MeleeDiamond::factory()->forCategory($category)->create([
            'shape'                  => 'Round',
            'size_label'             => 'round-1.5',
            'total_pieces'           => 200,
            'available_pieces'       => 150,
            'total_carat_weight'     => 10.000,
            'available_carat_weight' => 7.500,
            'purchase_price_per_ct'  => 50.00,
            'listing_price_per_ct'   => 65.00,
            'low_stock_threshold'    => 10,
        ]);

        $this->assertDatabaseHas('melee_diamonds', [
            'id'                => $diamond->id,
            'melee_category_id' => $category->id,
            'shape'             => 'Round',
            'size_label'        => 'round-1.5',
            'total_pieces'      => 200,
            'available_pieces'  => 150,
        ]);
    }

    #[Test]
    public function it_stores_a_melee_category_with_all_current_fields(): void
    {
        $category = MeleeCategory::factory()->create([
            'name'            => 'Brilliant Cut',
            'type'            => 'lab_grown',
            'cut_type'        => 'brilliant',
            'has_color_layer' => false,
            'is_active'       => true,
        ]);

        $this->assertDatabaseHas('melee_categories', [
            'id'              => $category->id,
            'name'            => 'Brilliant Cut',
            'type'            => 'lab_grown',
            'cut_type'        => 'brilliant',
            'has_color_layer' => false,
            'is_active'       => true,
        ]);
    }

    // =========================================================================
    // MeleeTransaction Boot Hook — created() Characterization (Dual-Write)
    // =========================================================================

    #[Test]
    public function transaction_boot_hook_adds_stock_for_manual_in_transaction(): void
    {
        Notification::fake();

        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'           => 100,
            'available_pieces'       => 80,
            'total_carat_weight'     => 5.000,
            'available_carat_weight' => 4.000,
        ]);

        $originalTotal = $diamond->fresh()->total_pieces;
        $originalAvailable = $diamond->fresh()->available_pieces;

        MeleeTransaction::factory()->stockIn()->create([
            'melee_diamond_id' => $diamond->id,
            'pieces'           => 20,
            'carat_weight'     => 1.500,
            'created_by'       => $admin->id,
        ]);

        $diamond->refresh();

        // Current behaviour: boot hook adds to BOTH total and available for non-order IN
        $this->assertEquals($originalTotal + 20, $diamond->total_pieces);
        $this->assertEquals($originalAvailable + 20, $diamond->available_pieces);
    }

    #[Test]
    public function transaction_boot_hook_deducts_stock_for_out_transaction(): void
    {
        Notification::fake();

        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'           => 100,
            'available_pieces'       => 80,
            'total_carat_weight'     => 5.000,
            'available_carat_weight' => 4.000,
        ]);

        $originalAvailable = $diamond->fresh()->available_pieces;

        MeleeTransaction::factory()->stockOut()->create([
            'melee_diamond_id' => $diamond->id,
            'pieces'           => 10,
            'carat_weight'     => 0.500,
            'created_by'       => $admin->id,
        ]);

        $diamond->refresh();

        // Current behaviour: boot hook deducts from available only (not total) for OUT
        $this->assertEquals($originalAvailable - 10, $diamond->available_pieces);
    }

    #[Test]
    public function transaction_boot_hook_handles_order_return_differently(): void
    {
        Notification::fake();

        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'           => 100,
            'available_pieces'       => 80,
            'total_carat_weight'     => 5.000,
            'available_carat_weight' => 4.000,
        ]);

        $originalTotal = $diamond->fresh()->total_pieces;
        $originalAvailable = $diamond->fresh()->available_pieces;

        MeleeTransaction::factory()->stockIn()->forOrder(999)->create([
            'melee_diamond_id' => $diamond->id,
            'pieces'           => 15,
            'carat_weight'     => 1.000,
            'created_by'       => $admin->id,
        ]);

        $diamond->refresh();

        // Current behaviour: order returns only adjust available, NOT total
        $this->assertEquals($originalTotal, $diamond->total_pieces);
        $this->assertEquals($originalAvailable + 15, $diamond->available_pieces);
    }

    // =========================================================================
    // Controller Endpoint Characterization
    // =========================================================================

    #[Test]
    public function index_returns_200_for_authenticated_admin(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('melee.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function search_returns_json_array(): void
    {
        $admin = $this->adminUser();
        MeleeDiamond::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('melee.search'));

        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    #[Test]
    public function search_returns_expected_fields_per_result(): void
    {
        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create([
            'available_pieces' => 50,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('melee.search', ['term' => $diamond->shape]));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id'               => $diamond->id,
            'available_pieces' => $diamond->fresh()->available_pieces,
        ]);
    }

    #[Test]
    public function get_stock_returns_diamond_data(): void
    {
        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('melee.get-stock', $diamond->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'diamond' => ['id', 'available_pieces', 'available_carat_weight', 'total_price', 'status'],
            ]);
    }

    #[Test]
    public function get_history_returns_transaction_list(): void
    {
        Notification::fake();

        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create();

        MeleeTransaction::factory()->stockIn()->create([
            'melee_diamond_id' => $diamond->id,
            'created_by'       => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('melee.history', $diamond->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'diamond' => ['id', 'shape', 'size_label', 'category_name', 'available_pieces', 'total_pieces'],
                'transactions',
            ]);
    }

    #[Test]
    public function add_shape_creates_new_diamond_in_category(): void
    {
        // Sprint 3: addShape now requires melee.create permission. Super-admin bypasses.
        $admin = $this->superAdmin();
        $category = MeleeCategory::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('melee.add-shape'), [
                'category_id' => $category->id,
                'shape'       => 'Oval',
                'size'        => '1.5',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('melee_diamonds', [
            'melee_category_id' => $category->id,
            'shape'             => 'Oval',
            'size_label'        => 'oval-1.5',
        ]);
    }

    #[Test]
    public function add_shape_rejects_duplicate_in_same_category(): void
    {
        // Sprint 3: addShape now requires melee.create permission. Super-admin bypasses.
        $admin = $this->superAdmin();
        $category = MeleeCategory::factory()->create();

        MeleeDiamond::factory()->forCategory($category)->create([
            'shape'      => 'Round',
            'size_label' => 'round-1.0',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('melee.add-shape'), [
                'category_id' => $category->id,
                'shape'       => 'Round',
                'size'        => '1.0',
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    #[Test]
    public function transaction_endpoint_records_stock_in(): void
    {
        Notification::fake();

        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create([
            'available_pieces' => 50,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('melee.transaction'), [
                'melee_diamond_id' => $diamond->id,
                'transaction_type' => 'in',
                'pieces'           => 10,
                'carat_weight'     => 0.500,
                'notes'            => 'Test stock in',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    #[Test]
    public function destroy_deletes_diamond_and_transactions(): void
    {
        Notification::fake();

        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create();

        MeleeTransaction::factory()->stockIn()->create([
            'melee_diamond_id' => $diamond->id,
            'created_by'       => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->deleteJson(route('melee.destroy', $diamond->id));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Current behaviour: soft-deletes diamond, hard-deletes transactions
        $this->assertSoftDeleted('melee_diamonds', ['id' => $diamond->id]);
        $this->assertDatabaseMissing('melee_transactions', ['melee_diamond_id' => $diamond->id]);
    }

    // =========================================================================
    // No Permission Middleware — Characterizing Current Gap
    // =========================================================================

    #[Test]
    public function melee_routes_have_no_permission_middleware(): void
    {
        // Current behaviour: ANY authenticated admin can access all melee routes
        // This characterizes the lack of permission checks — Sprint 3 will add them
        $regularAdmin = $this->adminUser();

        $response = $this->actingAs($regularAdmin, 'admin')
            ->get(route('melee.index'));

        $response->assertStatus(200);
    }

    // =========================================================================
    // Model Relationship Characterization
    // =========================================================================

    #[Test]
    public function diamond_belongs_to_category(): void
    {
        $category = MeleeCategory::factory()->labGrown()->create();
        $diamond = MeleeDiamond::factory()->forCategory($category)->create();

        $this->assertTrue($diamond->category->is($category));
    }

    #[Test]
    public function diamond_has_many_transactions(): void
    {
        Notification::fake();

        $admin = $this->adminUser();
        $diamond = MeleeDiamond::factory()->create();

        MeleeTransaction::factory()->count(3)->create([
            'melee_diamond_id' => $diamond->id,
            'created_by'       => $admin->id,
        ]);

        $this->assertCount(3, $diamond->transactions);
    }

    #[Test]
    public function diamond_name_accessor_returns_category_and_size(): void
    {
        $category = MeleeCategory::factory()->create(['name' => 'Brilliant Cut']);
        $diamond = MeleeDiamond::factory()->forCategory($category)->create([
            'size_label' => 'round-1.5',
        ]);

        // Current behaviour: "CategoryName — size_label" with dashes replaced by spaces
        $this->assertStringContainsString('Brilliant Cut', $diamond->name);
    }
}
