<?php

use App\Models\Admin;
use App\Models\Diamond;
use App\Exports\DiamondsExport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeFeatureDiamond(array $overrides = []): Diamond
{
    static $seq = 3000;
    $seq++;

    return Diamond::create(array_merge([
        'lot_no' => 'LOT-FEAT-' . $seq,
        'sku' => 'SKU-FEAT-' . $seq,
        'barcode_number' => 'BC-FEAT-' . $seq,
        'purchase_price' => 100,
        'margin' => 10,
    ], $overrides));
}

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-04-08 10:00:00', config('app.timezone', 'Asia/Kolkata')));
    Cache::put('usd_inr_rate', 1, 3600);
    Cache::put('usd_inr_rate_backup', 1, 3600);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('returns dynamic duration values in export mapping despite stale snapshots', function () {
    $admin = Admin::factory()->super()->create();
    $diamond = makeFeatureDiamond([
        'purchase_date' => '2026-03-27',
        'sold_out_date' => null,
    ]);

    DB::table('diamonds')->where('id', $diamond->id)->update([
        'duration_days' => 1,
        'duration_price' => 100.05,
    ]);

    $expectedDays = 12;
    $expectedPrice = round(100 * pow(1 + 0.0005, $expectedDays), 2);

    $export = new DiamondsExport(collect([$diamond->fresh()]), $admin->id);
    $row = $export->map($diamond->fresh());

    expect((int) $row[18])->toBe($expectedDays)
        ->and(round((float) $row[19], 2))->toBe($expectedPrice);
});

it('ignores client supplied duration fields on create', function () {
    $admin = Admin::factory()->super()->create();
    $sku = 'SKU-ROOT-NEW';

    $response = $this->actingAs($admin, 'admin')->post(route('diamond.store'), [
        'lot_no' => 'LOT-ROOT-NEW-001',
        'sku' => $sku,
        'purchase_price' => 100,
        'margin' => 10,
        'purchase_date' => '2026-03-27',
        'duration_days' => 9999,
        'duration_price' => 9999.99,
    ]);

    $response->assertRedirect(route('diamond.index'));

    $diamond = Diamond::where('sku', $sku)->firstOrFail();
    $expectedDays = 12;
    $expectedPrice = round(100 * pow(1 + 0.0005, $expectedDays), 2);

    expect((int) $diamond->getRawOriginal('duration_days'))->toBe($expectedDays)
        ->and(round((float) $diamond->getRawOriginal('duration_price'), 2))->toBe($expectedPrice);
});

it('ignores client supplied duration fields on update', function () {
    $admin = Admin::factory()->super()->create();
    $diamond = makeFeatureDiamond([
        'purchase_date' => '2026-03-27',
        'purchase_price' => 100,
    ]);

    $response = $this->actingAs($admin, 'admin')->put(route('diamond.update', $diamond), [
        'lot_no' => $diamond->lot_no,
        'sku' => $diamond->sku,
        'purchase_price' => 100,
        'margin' => 10,
        'purchase_date' => '2026-03-27',
        'sold_out_date' => '2026-03-30',
        'duration_days' => 8888,
        'duration_price' => 8888.88,
    ]);

    $response->assertRedirect(route('diamond.index'));

    $fresh = Diamond::findOrFail($diamond->id);
    $expectedDays = 3;
    $expectedPrice = round(100 * pow(1 + 0.0005, $expectedDays), 2);

    expect((int) $fresh->getRawOriginal('duration_days'))->toBe($expectedDays)
        ->and(round((float) $fresh->getRawOriginal('duration_price'), 2))->toBe($expectedPrice);
});
