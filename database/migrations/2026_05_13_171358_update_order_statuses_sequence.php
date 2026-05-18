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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN diamond_status ENUM(
                'r_order_in_process', 'r_order_shipped', 'r_order_cancelled',
                'd_diamond_in_discuss', 'd_diamond_in_making', 'd_diamond_in_certificate', 'd_diamond_repairing', 'd_diamond_completed', 'd_order_shipped', 'd_order_cancelled',
                'j_diamond_in_discuss', 'j_diamond_in_progress', 'j_diamond_completed', 'j_cad_in_progress', 'j_cad_done', 'j_order_in_making', 'j_order_repairing', 'j_order_completed', 'j_order_in_qc', 'j_qc_done', 'j_order_certificate', 'j_order_shipped', 'j_order_hold', 'j_order_cancelled'
            ) DEFAULT 'r_order_in_process'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            // Revert to the previous known state (from 2026_02_23 migration)
            DB::statement("ALTER TABLE orders MODIFY COLUMN diamond_status ENUM(
                'd_diamond_in_discuss', 'd_diamond_in_making', 'd_diamond_completed', 'd_diamond_in_certificate', 'd_order_shipped', 'd_order_cancelled',
                'j_diamond_in_progress', 'j_diamond_completed', 'j_diamond_in_discuss', 'j_cad_in_progress', 'j_cad_done', 'j_order_completed', 'j_order_in_qc', 'j_qc_done', 'j_order_shipped', 'j_order_hold', 'j_order_cancelled',
                'r_order_in_process', 'r_order_shipped', 'r_order_cancelled'
            ) DEFAULT 'r_order_in_process'");
        });
    }
};
