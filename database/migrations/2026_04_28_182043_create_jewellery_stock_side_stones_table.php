<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jewellery_stock_side_stones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jewellery_stock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stone_type_id')->nullable()->constrained('stone_types')->nullOnDelete();
            $table->foreignId('stone_shape_id')->nullable()->constrained('stone_shapes')->nullOnDelete();
            $table->foreignId('stone_color_id')->nullable()->constrained('stone_colors')->nullOnDelete();
            $table->foreignId('stone_clarity_id')->nullable()->constrained('diamond_clarities')->nullOnDelete();
            $table->foreignId('stone_cut_id')->nullable()->constrained('diamond_cuts')->nullOnDelete();
            $table->decimal('carat_weight', 10, 3)->nullable()->comment('Weight per stone');
            $table->integer('count')->nullable()->comment('Number of stones');
            $table->decimal('total_weight', 10, 3)->nullable()->comment('Total weight of these stones');
            $table->timestamps();
        });

        // Migrate existing data
        $stocksWithSideStones = DB::table('jewellery_stocks')->whereNotNull('side_stone_type_id')->get();
        foreach ($stocksWithSideStones as $stock) {
            DB::table('jewellery_stock_side_stones')->insert([
                'jewellery_stock_id' => $stock->id,
                'stone_type_id' => $stock->side_stone_type_id,
                'stone_shape_id' => $stock->side_stone_shape_id ?? null,
                'stone_color_id' => $stock->side_stone_color_id ?? null,
                'stone_clarity_id' => $stock->side_stone_clarity_id ?? null,
                'stone_cut_id' => $stock->side_stone_cut_id ?? null,
                'carat_weight' => $stock->side_stone_carat_weight ?? null,
                'count' => $stock->side_stone_count,
                'total_weight' => $stock->side_stone_weight,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->dropForeign(['side_stone_type_id']);
            $table->dropForeign(['side_stone_shape_id']);
            $table->dropForeign(['side_stone_color_id']);
            $table->dropForeign(['side_stone_clarity_id']);
            $table->dropForeign(['side_stone_cut_id']);
            
            $table->dropColumn([
                'side_stone_type_id',
                'side_stone_weight',
                'side_stone_count',
                'side_stone_carat_weight',
                'side_stone_shape_id',
                'side_stone_color_id',
                'side_stone_clarity_id',
                'side_stone_cut_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->foreignId('side_stone_type_id')->nullable()->constrained('stone_types')->nullOnDelete();
            $table->decimal('side_stone_weight', 10, 3)->nullable();
            $table->integer('side_stone_count')->nullable();
            $table->decimal('side_stone_carat_weight', 10, 3)->nullable();
            $table->foreignId('side_stone_shape_id')->nullable()->constrained('stone_shapes')->nullOnDelete();
            $table->foreignId('side_stone_color_id')->nullable()->constrained('stone_colors')->nullOnDelete();
            $table->foreignId('side_stone_clarity_id')->nullable()->constrained('diamond_clarities')->nullOnDelete();
            $table->foreignId('side_stone_cut_id')->nullable()->constrained('diamond_cuts')->nullOnDelete();
        });

        // Restore data (best effort, only one side stone per item)
        $sideStones = DB::table('jewellery_stock_side_stones')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('jewellery_stock_id');
            
        foreach ($sideStones as $stockId => $stones) {
            $firstStone = $stones->first();
            DB::table('jewellery_stocks')->where('id', $stockId)->update([
                'side_stone_type_id' => $firstStone->stone_type_id,
                'side_stone_weight' => $firstStone->total_weight,
                'side_stone_count' => $firstStone->count,
                'side_stone_carat_weight' => $firstStone->carat_weight,
                'side_stone_shape_id' => $firstStone->stone_shape_id,
                'side_stone_color_id' => $firstStone->stone_color_id,
                'side_stone_clarity_id' => $firstStone->stone_clarity_id,
                'side_stone_cut_id' => $firstStone->stone_cut_id,
            ]);
        }

        Schema::dropIfExists('jewellery_stock_side_stones');
    }
};
