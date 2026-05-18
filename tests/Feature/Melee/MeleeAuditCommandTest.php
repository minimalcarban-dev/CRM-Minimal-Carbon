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
 * Audit Command Tests — Sprint 1 Safety Foundation
 *
 * Verifies the melee:audit command is strictly read-only and outputs expected metrics.
 */
class MeleeAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function audit_command_runs_without_errors(): void
    {
        $this->artisan('melee:audit')
            ->assertExitCode(0);
    }

    #[Test]
    public function audit_command_displays_header(): void
    {
        $this->artisan('melee:audit')
            ->expectsOutputToContain('MELEE STOCK AUDIT')
            ->assertExitCode(0);
    }

    #[Test]
    public function audit_command_displays_summary_table_with_correct_counts(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();
        $category = MeleeCategory::factory()->create();

        MeleeDiamond::factory()->count(3)->forCategory($category)->create([
            'available_pieces' => 50,
        ]);

        MeleeDiamond::factory()->forCategory($category)->create([
            'available_pieces' => -5,
        ]);

        $this->artisan('melee:audit')
            ->expectsOutputToContain('Total melee diamond records')
            ->expectsOutputToContain('4')
            ->expectsOutputToContain('Negative stock records')
            ->assertExitCode(0);
    }

    #[Test]
    public function audit_command_displays_audit_complete_footer(): void
    {
        $this->artisan('melee:audit')
            ->expectsOutputToContain('AUDIT COMPLETE')
            ->assertExitCode(0);
    }

    #[Test]
    public function audit_command_is_read_only(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();
        $category = MeleeCategory::factory()->create();

        $diamond = MeleeDiamond::factory()->forCategory($category)->create([
            'total_pieces'     => 100,
            'available_pieces' => 80,
        ]);

        MeleeTransaction::factory()->stockIn()->create([
            'melee_diamond_id' => $diamond->id,
            'pieces'           => 20,
            'created_by'       => $admin->id,
        ]);

        // Snapshot state before audit
        $diamondCountBefore = MeleeDiamond::count();
        $transactionCountBefore = MeleeTransaction::count();
        $categoryCountBefore = MeleeCategory::count();
        $diamondBefore = $diamond->fresh()->toArray();

        // Run audit
        $this->artisan('melee:audit')->assertExitCode(0);

        // Verify nothing changed — audit is read-only
        $this->assertEquals($diamondCountBefore, MeleeDiamond::count(), 'Diamond count changed during audit');
        $this->assertEquals($transactionCountBefore, MeleeTransaction::count(), 'Transaction count changed during audit');
        $this->assertEquals($categoryCountBefore, MeleeCategory::count(), 'Category count changed during audit');

        $diamondAfter = $diamond->fresh()->toArray();
        $this->assertEquals($diamondBefore, $diamondAfter, 'Diamond record was mutated during audit');
    }

    #[Test]
    public function audit_command_detects_stock_drift(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();
        $category = MeleeCategory::factory()->create();

        // Create a diamond with values that DON'T match its transaction ledger
        // This simulates drift — diamond says 100 total but ledger only has 80
        $diamond = MeleeDiamond::factory()->forCategory($category)->create([
            'total_pieces'     => 100,
            'available_pieces' => 80,
        ]);

        // Add a transaction for only 50 pieces (creating a drift of 50 in total_pieces)
        MeleeTransaction::factory()->stockIn()->create([
            'melee_diamond_id' => $diamond->id,
            'pieces'           => 50,
            'created_by'       => $admin->id,
        ]);

        $this->artisan('melee:audit')
            ->expectsOutputToContain('STOCK DRIFT CHECK')
            ->assertExitCode(0);
    }
}
