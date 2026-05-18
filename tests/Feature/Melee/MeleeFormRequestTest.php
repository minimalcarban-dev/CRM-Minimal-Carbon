<?php

namespace Tests\Feature\Melee;

use App\Models\Admin;
use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Form Request Feature Tests — Sprint 3
 *
 * Tests for StoreMeleeRequest (addShape route) and UpdateMeleeRequest (update route).
 *
 * Policy note: super-admins bypass melee.create / melee.edit automatically.
 * Regular admins with no permissions will get a 403 from authorize().
 */
class MeleeFormRequestTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // StoreMeleeRequest — addShape (POST /melee/add-shape)
    // =========================================================================

    /** @test */
    public function add_shape_rejects_missing_category_id(): void
    {
        $admin = Admin::factory()->super()->create();

        $this->actingAs($admin, 'admin')
            ->postJson(route('melee.add-shape'), [
                'shape' => 'Round',
                'size'  => '1.5',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }

    /** @test */
    public function add_shape_rejects_nonexistent_category(): void
    {
        $admin = Admin::factory()->super()->create();

        $this->actingAs($admin, 'admin')
            ->postJson(route('melee.add-shape'), [
                'category_id' => 99999,
                'shape'       => 'Round',
                'size'        => '1.5',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }

    /** @test */
    public function add_shape_rejects_invalid_size_format(): void
    {
        $admin    = Admin::factory()->super()->create();
        $category = MeleeCategory::factory()->create();

        $this->actingAs($admin, 'admin')
            ->postJson(route('melee.add-shape'), [
                'category_id' => $category->id,
                'shape'       => 'Round',
                'size'        => 'invalid!size',   // letters and special chars not allowed
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['size']);
    }

    /** @test */
    public function add_shape_rejects_missing_shape(): void
    {
        $admin    = Admin::factory()->super()->create();
        $category = MeleeCategory::factory()->create();

        $this->actingAs($admin, 'admin')
            ->postJson(route('melee.add-shape'), [
                'category_id' => $category->id,
                'size'        => '1.5',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['shape']);
    }

    /** @test */
    public function add_shape_succeeds_with_valid_payload(): void
    {
        $admin    = Admin::factory()->super()->create();
        $category = MeleeCategory::factory()->create();

        $this->actingAs($admin, 'admin')
            ->postJson(route('melee.add-shape'), [
                'category_id' => $category->id,
                'shape'       => 'Oval',
                'size'        => '2.0',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function add_shape_forbidden_for_admin_without_create_permission(): void
    {
        $admin    = Admin::factory()->create(); // no permissions
        $category = MeleeCategory::factory()->create();

        $this->actingAs($admin, 'admin')
            ->postJson(route('melee.add-shape'), [
                'category_id' => $category->id,
                'shape'       => 'Round',
                'size'        => '1.5',
            ])
            ->assertForbidden();
    }

    // =========================================================================
    // UpdateMeleeRequest — update (PUT /melee/{id})
    // =========================================================================

    /** @test */
    public function update_rejects_missing_shape(): void
    {
        $admin   = Admin::factory()->super()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->actingAs($admin, 'admin')
            ->putJson(route('melee.update', $diamond->id), [
                'size' => '1.5',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['shape']);
    }

    /** @test */
    public function update_rejects_invalid_size_format(): void
    {
        $admin   = Admin::factory()->super()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->actingAs($admin, 'admin')
            ->putJson(route('melee.update', $diamond->id), [
                'shape' => 'Round',
                'size'  => 'bad!size',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['size']);
    }

    /** @test */
    public function update_rejects_negative_last_carats(): void
    {
        $admin   = Admin::factory()->super()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->actingAs($admin, 'admin')
            ->putJson(route('melee.update', $diamond->id), [
                'shape'       => 'Round',
                'size'        => '1.5',
                'last_pieces' => 10,
                'last_carats' => -0.5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['last_carats']);
    }

    /** @test */
    public function update_forbidden_for_admin_without_edit_permission(): void
    {
        $admin   = Admin::factory()->create(); // no permissions
        $diamond = MeleeDiamond::factory()->create();

        $this->actingAs($admin, 'admin')
            ->putJson(route('melee.update', $diamond->id), [
                'shape' => 'Round',
                'size'  => '1.5',
            ])
            ->assertForbidden();
    }
}
