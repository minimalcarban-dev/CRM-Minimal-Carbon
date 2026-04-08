<?php

use App\Exports\DiamondsExport;
use App\Models\Admin;
use App\Models\Diamond;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeDurationDiamond(array $overrides = []): Diamond
{
    static $seq = 1000;
    $seq++;

    return Diamond::create(array_merge([
        'lot_no' => 'LOT-DUR-' . $seq,
        'sku' => 'SKU-DUR-' . $seq,
        'barcode_number' => 'BC-DUR-' . $seq,
        'purchase_price' => 100,
        'margin' => 10,
        'shipping_price' => 0,
    ], $overrides));
}

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-04-08 10:00:00', config('app.timezone', 'Asia/Kolkata')));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('computes dynamic duration for in-stock diamonds even if stored snapshot is stale', function () {
    $diamond = makeDurationDiamond([
        'purchase_date' => '2026-03-27',
        'sold_out_date' => null,
    ]);

    DB::table('diamonds')->where('id', $diamond->id)->update([
        'duration_days' => 1,
        'duration_price' => 100.05,
    ]);

    $fresh = Diamond::findOrFail($diamond->id);
    $expectedDays = 12; // Mar 27 -> Apr 08 (exclusive start)
    $expectedPrice = round(100 * pow(1 + 0.0005, $expectedDays), 2);

    expect($fresh->getRawOriginal('duration_days'))->toBe(1)
        ->and((int) $fresh->duration_days)->toBe($expectedDays)
        ->and(round((float) $fresh->duration_price, 2))->toBe($expectedPrice);
});

it('keeps sold duration anchored to sold_out_date', function () {
    $diamond = makeDurationDiamond([
        'purchase_date' => '2026-03-27',
        'sold_out_date' => '2026-04-01',
    ]);

    DB::table('diamonds')->where('id', $diamond->id)->update([
        'duration_days' => 99,
        'duration_price' => 199.99,
    ]);

    $fresh = Diamond::findOrFail($diamond->id);
    $expectedDays = 5;
    $expectedPrice = round(100 * pow(1 + 0.0005, $expectedDays), 2);

    expect((int) $fresh->duration_days)->toBe($expectedDays)
        ->and(round((float) $fresh->duration_price, 2))->toBe($expectedPrice);
});

it('returns zero days for same-day purchase', function () {
    $diamond = makeDurationDiamond([
        'purchase_date' => '2026-04-08',
    ]);

    expect((int) $diamond->fresh()->duration_days)->toBe(0);
});

it('uses dynamic duration in export mapping', function () {
    $admin = Admin::factory()->super()->create();

    $diamond = makeDurationDiamond([
        'purchase_date' => '2026-03-06',
        'sold_out_date' => null,
        'purchase_price' => 200,
    ]);

    DB::table('diamonds')->where('id', $diamond->id)->update([
        'duration_days' => 2,
        'duration_price' => 200.20,
    ]);

    $export = new DiamondsExport(collect([$diamond->fresh()]), $admin->id);
    $row = $export->map($diamond->fresh());

    $expectedDays = 33; // Mar 06 -> Apr 08
    $expectedPrice = round(200 * pow(1 + 0.0005, $expectedDays), 2);

    expect((int) $row[18])->toBe($expectedDays)
        ->and(round((float) $row[19], 2))->toBe($expectedPrice);
});

