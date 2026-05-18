<?php

namespace Tests\Feature\Melee;

use App\Events\MeleeCreated;
use App\Events\MeleeDeleted;
use App\Events\MeleeStatusChanged;
use App\Models\MeleeDiamond;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Observer Tests — MeleeObserver (Sprint 6)
 *
 * Verifies that MeleeObserver dispatches the correct events at the
 * correct lifecycle points on MeleeDiamond, including the guard that
 * prevents MeleeStatusChanged from firing when status did not change.
 *
 * Strategy:
 *   - Event::fake() intercepts dispatched events without real listeners.
 *   - For 'updated' tests: create the diamond BEFORE Event::fake() so the
 *     'created' event fires for real and does not pollute assertions.
 *   - Notification::fake() prevents any incidental notification dispatch.
 */
class MeleeObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    // =========================================================================
    // created → MeleeCreated
    // =========================================================================

    #[Test]
    public function created_fires_melee_created_event(): void
    {
        Event::fake([MeleeCreated::class]);

        MeleeDiamond::factory()->create([
            'total_pieces'     => 100,
            'available_pieces' => 80,
        ]);

        Event::assertDispatched(MeleeCreated::class, function (MeleeCreated $event): bool {
            return $event->diamond instanceof MeleeDiamond;
        });
    }

    // =========================================================================
    // updated WITH status change → MeleeStatusChanged
    // =========================================================================

    #[Test]
    public function updated_with_status_change_fires_melee_status_changed(): void
    {
        // Create diamond in_stock (available=100, threshold=10)
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'        => 100,
            'available_pieces'    => 100,
            'low_stock_threshold' => 10,
        ]);

        // Start intercepting only MeleeStatusChanged — Eloquent model events still propagate
        Event::fake([MeleeStatusChanged::class]);

        // Drive available_pieces to 0 → saving() hook sets status='out_of_stock'
        $diamond->available_pieces = 0;
        $diamond->save();

        Event::assertDispatched(
            MeleeStatusChanged::class,
            function (MeleeStatusChanged $event) use ($diamond): bool {
                return $event->diamond->is($diamond)
                    && $event->oldStatus === 'in_stock'
                    && $event->newStatus === 'out_of_stock';
            }
        );
    }

    // =========================================================================
    // updated WITHOUT status change → MeleeStatusChanged NOT fired
    // =========================================================================

    #[Test]
    public function updated_without_status_change_does_not_fire_melee_status_changed(): void
    {
        // Create diamond well above threshold — status = in_stock
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'        => 200,
            'available_pieces'    => 150,
            'low_stock_threshold' => 10,
            'shape'               => 'Round',
        ]);

        // Start intercepting — shape update does not cross any stock boundary
        Event::fake([MeleeStatusChanged::class]);

        $diamond->shape = 'Oval';
        $diamond->save();

        // Status remains 'in_stock' → observer guard returns early → no event
        Event::assertNotDispatched(MeleeStatusChanged::class);
    }

    // =========================================================================
    // deleted → MeleeDeleted
    // =========================================================================

    #[Test]
    public function deleted_fires_melee_deleted_event(): void
    {
        $diamond = MeleeDiamond::factory()->create([
            'total_pieces'     => 50,
            'available_pieces' => 50,
        ]);
        Event::fake([MeleeDeleted::class]);

        $diamond->delete(); // soft delete

        Event::assertDispatched(MeleeDeleted::class, function (MeleeDeleted $event) use ($diamond): bool {
            return $event->diamond->id === $diamond->id;
        });
    }
}
