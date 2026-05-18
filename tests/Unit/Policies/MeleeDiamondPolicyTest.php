<?php

namespace Tests\Unit\Policies;

use App\Models\Admin;
use App\Models\MeleeDiamond;
use App\Policies\MeleeDiamondPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Policy Unit Tests — Sprint 3
 *
 * These tests verify that MeleeDiamondPolicy delegates correctly to
 * Admin::hasPermission() for each gate action.
 *
 * IMPORTANT: This project uses Admin (not User) as the auth model.
 * Super-admins bypass melee.* (non-strict prefix) automatically.
 */
class MeleeDiamondPolicyTest extends TestCase
{
    use RefreshDatabase;

    private MeleeDiamondPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new MeleeDiamondPolicy();
    }

    // =========================================================================
    // viewAny
    // =========================================================================

    #[Test]
    public function super_admin_can_view_any_melee(): void
    {
        $admin = Admin::factory()->super()->create();

        $this->assertTrue($this->policy->viewAny($admin));
    }

    #[Test]
    public function admin_without_view_permission_cannot_view_any(): void
    {
        // Regular factory admin has no permissions by default
        $admin = Admin::factory()->create();

        $this->assertFalse($this->policy->viewAny($admin));
    }

    // =========================================================================
    // view (single record)
    // =========================================================================

    #[Test]
    public function super_admin_can_view_any_specific_diamond(): void
    {
        $admin   = Admin::factory()->super()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->assertTrue($this->policy->view($admin, $diamond));
    }

    #[Test]
    public function admin_without_view_permission_cannot_view_specific_diamond(): void
    {
        $admin   = Admin::factory()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->assertFalse($this->policy->view($admin, $diamond));
    }

    // =========================================================================
    // create
    // =========================================================================

    #[Test]
    public function super_admin_can_create_melee(): void
    {
        $admin = Admin::factory()->super()->create();

        $this->assertTrue($this->policy->create($admin));
    }

    #[Test]
    public function admin_without_create_permission_cannot_create(): void
    {
        $admin = Admin::factory()->create();

        $this->assertFalse($this->policy->create($admin));
    }

    // =========================================================================
    // update
    // =========================================================================

    #[Test]
    public function super_admin_can_update_any_diamond(): void
    {
        $admin   = Admin::factory()->super()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->assertTrue($this->policy->update($admin, $diamond));
    }

    #[Test]
    public function admin_without_edit_permission_cannot_update(): void
    {
        $admin   = Admin::factory()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->assertFalse($this->policy->update($admin, $diamond));
    }

    // =========================================================================
    // delete
    // =========================================================================

    #[Test]
    public function super_admin_can_delete_any_diamond(): void
    {
        $admin   = Admin::factory()->super()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->assertTrue($this->policy->delete($admin, $diamond));
    }

    #[Test]
    public function admin_without_delete_permission_cannot_delete(): void
    {
        $admin   = Admin::factory()->create();
        $diamond = MeleeDiamond::factory()->create();

        $this->assertFalse($this->policy->delete($admin, $diamond));
    }
}
