<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration fixes the diamond_status ENUM column to have the correct values
     * that match the form options.
     */
    public function up(): void
    {
        // Alter the ENUM to include all required status values
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN diamond_status ENUM(
                'r_order_in_process',
                'r_order_shipped',
                'd_diamond_in_discuss',
                'd_diamond_in_making',
                'd_diamond_completed',
                'd_diamond_in_certificate',
                'd_order_shipped',
                'j_diamond_in_progress',
                'j_diamond_completed',
                'j_diamond_in_discuss',
                'j_cad_in_progress',
                'j_cad_done',
                'j_order_completed',
                'j_order_in_qc',
                'j_qc_done',
                'j_order_shipped',
                'j_order_hold',
                'processed',
                'completed'
            ) NULL COMMENT 'Production progress'");
        }

        // Optionally: Update old values to new ones
        // DB::statement("UPDATE orders SET diamond_status = 'r_order_in_process' WHERE diamond_status = 'processed'");
        // DB::statement("UPDATE orders SET diamond_status = 'r_order_shipped' WHERE diamond_status = 'completed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original structure if needed
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN diamond_status ENUM(
                'processed',
                'completed',
                'd_diamond_in_discuss',
                'd_diamond_in_making',
                'd_diamond_completed',
                'd_diamond_in_certificate',
                'd_order_shipped',
                'j_diamond_in_progress',
                'j_diamond_completed',
                'j_diamond_in_discuss',
                'j_cad_in_progress',
                'j_cad_done',
                'j_order_completed',
                'j_order_in_qc',
                'j_qc_done',
                'j_order_shipped',
                'j_order_hold'
            ) NULL COMMENT 'Production progress'");
        }
    }
};
