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
            $table->string('thickness')->nullable()->after('diameter');
            $table->string('primary_stone_measurement')->nullable()->after('primary_stone_count');
        });

        Schema::table('jewellery_stock_side_stones', function (Blueprint $table) {
            $table->string('measurement')->nullable()->after('count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jewellery_stock_side_stones', function (Blueprint $table) {
            $table->dropColumn(['measurement']);
        });

        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->dropColumn(['thickness', 'primary_stone_measurement']);
        });
    }
};
