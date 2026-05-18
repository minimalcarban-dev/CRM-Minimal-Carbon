<?php

namespace Tests\Feature\Melee;

use App\Models\Admin;
use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use App\Services\MeleeStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Integration Tests — MeleeStockService (Sprint 6)
 *
 * Each test exercises a real DB transaction against the SQLite
 * in-memory database. Notification::fake() is used where the service
 * calls notifyLowStockIfNeeded() so no actual mail is dispatched.
 */
class MeleeStockServiceTest extends TestCase
{
    use RefreshDatabase;

    private MeleeStockService $service;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MeleeStockService::class);
        $this->admin   = Admin::factory()->create();

        // Prevent real notification dispatch in every test
        Notification::fake();
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function makeDiamond(array $overrides = []): MeleeDiamond
    {
        return MeleeDiamond::factory()->create(array_merge([
            'total_pieces'           => 200,
            'available_pieces'       => 100,
            'total_carat_weight'     => 10.000,
            'available_carat_weight' => 5.000,
            'purchase_price_per_ct'  => 20.00,
            'low_stock_threshold'    => 10,
        ], $overrides));
    }

    private function entry(MeleeDiamond $diamond, int $pieces, float $avgCarat = 0.05): array
    {
        return [
            'melee_diamond_id'  => $diamond->id,
            'pieces'            => $pieces,
            'avg_carat_per_piece' => $avgCarat,
        ];
    }

    // =========================================================================
    // deductForOrder — happy path
    // =========================================================================

    #[Test]
    public function deduct_for_order_deducts_available_pieces_and_creates_out_transaction(): void
    {
        $diamond = $this->makeDiamond(['available_pieces' => 100]);
        $orderId = 9001;

        $result = $this->service->deductForOrder($orderId, [$this->entry($diamond, 30)]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('deducted', $result['message']);

        // Available pieces reduced
        $this->assertEquals(70, $diamond->fresh()->available_pieces);

        // One 'out' transaction created
        $this->assertDatabaseHas('melee_transactions', [
            'melee_diamond_id' => $diamond->id,
            'transaction_type' => 'out',
            'pieces'           => 30,
            'reference_type'   => 'order',
            'reference_id'     => $orderId,
        ]);
    }

    // =========================================================================
    // deductForOrder — insufficient stock
    // =========================================================================

    #[Test]
    public function deduct_for_order_returns_failure_when_insufficient_stock(): void
    {
        $diamond = $this->makeDiamond(['available_pieces' => 5]);
        $orderId = 9002;

        $result = $this->service->deductForOrder($orderId, [$this->entry($diamond, 50)]);

        $this->assertFalse($result['success']);
        $this->assertEquals(422, $result['status']);
        $this->assertStringContainsString('Stock low', $result['message']);

        // No transaction should exist
        $this->assertDatabaseMissing('melee_transactions', [
            'reference_id'   => $orderId,
            'reference_type' => 'order',
        ]);

        // Stock unchanged
        $this->assertEquals(5, $diamond->fresh()->available_pieces);
    }

    // =========================================================================
    // deductForOrder — idempotency (second call skipped)
    // =========================================================================

    #[Test]
    public function deduct_for_order_is_idempotent_and_skips_if_already_deducted(): void
    {
        $diamond = $this->makeDiamond(['available_pieces' => 100]);
        $orderId = 9003;

        // First call
        $first = $this->service->deductForOrder($orderId, [$this->entry($diamond, 20)]);
        $this->assertTrue($first['success']);

        $afterFirst = $diamond->fresh()->available_pieces; // 80

        // Second call — must be skipped
        $second = $this->service->deductForOrder($orderId, [$this->entry($diamond, 20)]);
        $this->assertTrue($second['success']);
        $this->assertStringContainsString('already deducted', $second['message']);

        // Stock must NOT have changed from after-first value
        $this->assertEquals($afterFirst, $diamond->fresh()->available_pieces);

        // Only one 'out' transaction in the ledger
        $outCount = MeleeTransaction::where('reference_type', 'order')
            ->where('reference_id', $orderId)
            ->where('transaction_type', 'out')
            ->count();
        $this->assertEquals(1, $outCount);
    }

    // =========================================================================
    // returnForOrder — happy path
    // =========================================================================

    #[Test]
    public function return_for_order_restores_available_pieces_and_creates_in_transaction(): void
    {
        $diamond = $this->makeDiamond(['available_pieces' => 60]);
        $orderId = 9004;

        $result = $this->service->returnForOrder($orderId, [$this->entry($diamond, 15)]);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('returned', $result['message']);

        // Available pieces increased
        $this->assertEquals(75, $diamond->fresh()->available_pieces);

        // One 'in' transaction created with reference_type = order
        $this->assertDatabaseHas('melee_transactions', [
            'melee_diamond_id' => $diamond->id,
            'transaction_type' => 'in',
            'pieces'           => 15,
            'reference_type'   => 'order',
            'reference_id'     => $orderId,
        ]);
    }

    // =========================================================================
    // adjustForOrderDiff — net increase (need MORE stock)
    // =========================================================================

    #[Test]
    public function adjust_for_order_diff_deducts_on_net_increase(): void
    {
        $diamond = $this->makeDiamond(['available_pieces' => 100]);
        $orderId = 9005;

        $old = [$this->entry($diamond, 10)]; // previously used 10
        $new = [$this->entry($diamond, 30)]; // now needs 30 → net +20

        $result = $this->service->adjustForOrderDiff($orderId, $old, $new);

        $this->assertTrue($result['success']);

        // Net deduction of 20 pieces
        $this->assertEquals(80, $diamond->fresh()->available_pieces);

        // An 'out' diff transaction created
        $this->assertDatabaseHas('melee_transactions', [
            'melee_diamond_id' => $diamond->id,
            'transaction_type' => 'out',
            'pieces'           => 20,
            'reference_type'   => 'order',
            'reference_id'     => $orderId,
        ]);
    }

    // =========================================================================
    // adjustForOrderDiff — net decrease (return excess stock)
    // =========================================================================

    #[Test]
    public function adjust_for_order_diff_returns_on_net_decrease(): void
    {
        $diamond = $this->makeDiamond(['available_pieces' => 60]);
        $orderId = 9006;

        $old = [$this->entry($diamond, 40)]; // previously used 40
        $new = [$this->entry($diamond, 25)]; // now needs 25 → net -15 (return 15)

        $result = $this->service->adjustForOrderDiff($orderId, $old, $new);

        $this->assertTrue($result['success']);

        // 15 pieces returned
        $this->assertEquals(75, $diamond->fresh()->available_pieces);

        // An 'in' diff transaction created
        $this->assertDatabaseHas('melee_transactions', [
            'melee_diamond_id' => $diamond->id,
            'transaction_type' => 'in',
            'pieces'           => 15,
            'reference_type'   => 'order',
            'reference_id'     => $orderId,
        ]);
    }

    // =========================================================================
    // adjustForOrderDiff — no change (skip entirely)
    // =========================================================================

    #[Test]
    public function adjust_for_order_diff_skips_when_no_net_change(): void
    {
        $diamond = $this->makeDiamond(['available_pieces' => 80]);
        $orderId = 9007;

        $old = [$this->entry($diamond, 20)];
        $new = [$this->entry($diamond, 20)]; // identical — zero delta

        $result = $this->service->adjustForOrderDiff($orderId, $old, $new);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('No net stock change', $result['message']);

        // Stock untouched
        $this->assertEquals(80, $diamond->fresh()->available_pieces);

        // No transaction written
        $this->assertDatabaseMissing('melee_transactions', [
            'reference_id'   => $orderId,
            'reference_type' => 'order',
        ]);
    }

    // =========================================================================
    // recordManualTransaction — stock in
    // =========================================================================

    #[Test]
    public function record_manual_transaction_adds_stock_for_in_type(): void
    {
        $diamond = $this->makeDiamond([
            'available_pieces'       => 50,
            'available_carat_weight' => 2.500,
        ]);

        $this->actingAs($this->admin, 'admin');

        $result = $this->service->recordManualTransaction([
            'melee_diamond_id' => $diamond->id,
            'transaction_type' => 'in',
            'pieces'           => 20,
            'carat_weight'     => 1.000,
            'notes'            => 'Purchase batch #7',
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);

        // Transaction in ledger
        $this->assertDatabaseHas('melee_transactions', [
            'id'               => $result['transaction_id'],
            'melee_diamond_id' => $diamond->id,
            'transaction_type' => 'in',
            'pieces'           => 20,
            'reference_type'   => 'manual',
        ]);
    }

    // =========================================================================
    // recordManualTransaction — stock out insufficient
    // =========================================================================

    #[Test]
    public function record_manual_transaction_rejects_out_when_insufficient_stock(): void
    {
        $diamond = $this->makeDiamond(['available_pieces' => 5]);

        $this->actingAs($this->admin, 'admin');

        $result = $this->service->recordManualTransaction([
            'melee_diamond_id' => $diamond->id,
            'transaction_type' => 'out',
            'pieces'           => 50,
            'carat_weight'     => 2.500,
            'notes'            => 'Should fail',
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals(422, $result['status']);
        $this->assertStringContainsString('Stock low', $result['message']);

        // Stock unchanged
        $this->assertEquals(5, $diamond->fresh()->available_pieces);

        // No transaction written
        $this->assertDatabaseMissing('melee_transactions', [
            'melee_diamond_id' => $diamond->id,
            'reference_type'   => 'manual',
        ]);
    }
}
