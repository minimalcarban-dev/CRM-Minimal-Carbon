<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jewellery_stock_pricings', function ($table) {
            $table->json('color_weights')->nullable()->after('net_weight_grams');
        });

        $allRows = DB::table('jewellery_stock_pricings')
            ->orderBy('id')
            ->get();

        $grouped = $allRows->groupBy(function ($row) {
            return $row->jewellery_stock_id . '|' . $row->material_code;
        });

        foreach ($grouped as $rows) {
            $first = $rows->first();
            if (!$first) {
                continue;
            }

            if (!str_starts_with($first->material_code, 'gold_')) {
                continue;
            }

            $weights = ['yellow' => 0, 'white' => 0, 'rose' => 0];
            $keepId = $first->id;
            $keepWeight = (float) $first->net_weight_grams;
            $defaultRow = $rows->firstWhere('is_default_listing', 1);

            foreach ($rows as $row) {
                $color = strtolower((string) ($row->metal_color ?? ''));
                if (array_key_exists($color, $weights)) {
                    $weights[$color] = max($weights[$color], (float) $row->net_weight_grams);
                }
            }

            if ($defaultRow) {
                $keepId = $defaultRow->id;
                $keepWeight = (float) $defaultRow->net_weight_grams;
            } else {
                $keepWeight = max($keepWeight, $weights['yellow'], $weights['white'], $weights['rose']);
            }

            DB::table('jewellery_stock_pricings')
                ->where('id', $keepId)
                ->update([
                    'metal_color' => null,
                    'net_weight_grams' => $keepWeight,
                    'color_weights' => json_encode($weights),
                ]);

            DB::table('jewellery_stock_pricings')
                ->whereIn('id', $rows->pluck('id')->filter(fn ($id) => $id !== $keepId)->values())
                ->delete();
        }
    }

    public function down(): void
    {
        Schema::table('jewellery_stock_pricings', function ($table) {
            $table->dropColumn('color_weights');
        });
    }
};
