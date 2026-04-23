<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->decimal('total_stone_weight', 10, 3)->nullable()->after('side_stone_weight');
        });

        DB::table('jewellery_stocks')->update([
            'total_stone_weight' => DB::raw(
                'CASE
                    WHEN primary_stone_weight IS NULL AND side_stone_weight IS NULL THEN NULL
                    ELSE COALESCE(primary_stone_weight, 0) + COALESCE(side_stone_weight, 0)
                END'
            ),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->dropColumn('total_stone_weight');
        });
    }
};
