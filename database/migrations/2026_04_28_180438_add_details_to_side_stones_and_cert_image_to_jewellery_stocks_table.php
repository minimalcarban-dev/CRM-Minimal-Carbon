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
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->decimal('side_stone_carat_weight', 10, 3)->nullable()->after('side_stone_type_id');
            $table->foreignId('side_stone_shape_id')->nullable()->after('side_stone_count')->constrained('stone_shapes')->nullOnDelete();
            $table->foreignId('side_stone_color_id')->nullable()->after('side_stone_shape_id')->constrained('stone_colors')->nullOnDelete();
            $table->foreignId('side_stone_clarity_id')->nullable()->after('side_stone_color_id')->constrained('diamond_clarities')->nullOnDelete();
            $table->foreignId('side_stone_cut_id')->nullable()->after('side_stone_clarity_id')->constrained('diamond_cuts')->nullOnDelete();
            $table->text('certificate_image')->nullable()->after('certificate_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->dropForeign(['side_stone_shape_id']);
            $table->dropForeign(['side_stone_color_id']);
            $table->dropForeign(['side_stone_clarity_id']);
            $table->dropForeign(['side_stone_cut_id']);
            $table->dropColumn([
                'side_stone_carat_weight',
                'side_stone_shape_id',
                'side_stone_color_id',
                'side_stone_clarity_id',
                'side_stone_cut_id',
                'certificate_image'
            ]);
        });
    }
};
