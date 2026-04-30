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
        // 1. Add discount to jewellery_stocks
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('jewellery_stocks', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('selling_price');
            }
        });

        // 2. Create the new side stones table
        if (!Schema::hasTable('jewellery_stock_side_stones')) {
            Schema::create('jewellery_stock_side_stones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jewellery_stock_id')->constrained('jewellery_stocks')->cascadeOnDelete();
                $table->foreignId('stone_type_id')->constrained('stone_types')->restrictOnDelete();
                $table->decimal('weight', 10, 3)->nullable();
                $table->integer('count')->nullable();
                $table->foreignId('stone_shape_id')->nullable()->constrained('stone_shapes')->nullOnDelete();
                $table->foreignId('stone_color_id')->nullable()->constrained('stone_colors')->nullOnDelete();
                $table->foreignId('stone_clarity_id')->nullable()->constrained('diamond_clarities')->nullOnDelete();
                $table->foreignId('stone_cut_id')->nullable()->constrained('diamond_cuts')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Migrate existing side stone data (if any)
        $stocksWithSideStones = DB::table('jewellery_stocks')
            ->whereNotNull('side_stone_type_id')
            ->get();

        foreach ($stocksWithSideStones as $stock) {
            DB::table('jewellery_stock_side_stones')->insert([
                'jewellery_stock_id' => $stock->id,
                'stone_type_id' => $stock->side_stone_type_id,
                'weight' => $stock->side_stone_weight,
                'count' => $stock->side_stone_count,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Drop old columns from jewellery_stocks
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            if (Schema::hasColumn('jewellery_stocks', 'metal_purity')) {
                $table->dropColumn('metal_purity');
            }
            if (Schema::hasColumn('jewellery_stocks', 'side_stone_type_id')) {
                $table->dropForeign(['side_stone_type_id']);
                $table->dropColumn('side_stone_type_id');
            }
            if (Schema::hasColumn('jewellery_stocks', 'side_stone_weight')) {
                $table->dropColumn('side_stone_weight');
            }
            if (Schema::hasColumn('jewellery_stocks', 'side_stone_count')) {
                $table->dropColumn('side_stone_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->string('metal_purity')->nullable();
            $table->foreignId('side_stone_type_id')->nullable()->constrained('stone_types')->nullOnDelete();
            $table->decimal('side_stone_weight', 10, 3)->nullable();
            $table->integer('side_stone_count')->nullable();
            $table->dropColumn('discount_percent');
        });

        // Try to restore old side stone data based on the first record found
        $sideStones = DB::table('jewellery_stock_side_stones')->get()->groupBy('jewellery_stock_id');
        foreach ($sideStones as $stockId => $stones) {
            $firstStone = $stones->first();
            DB::table('jewellery_stocks')->where('id', $stockId)->update([
                'side_stone_type_id' => $firstStone->stone_type_id,
                'side_stone_weight' => $firstStone->weight,
                'side_stone_count' => $firstStone->count,
            ]);
        }

        Schema::dropIfExists('jewellery_stock_side_stones');
    }
};
