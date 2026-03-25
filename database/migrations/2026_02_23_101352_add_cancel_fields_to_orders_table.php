<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('note');
            $table->timestamp('cancelled_at')->nullable()->after('cancel_reason');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->foreign('cancelled_by')->references('id')->on('admins')->nullOnDelete();
        });

        // Modify the ENUM using raw statement to add the cancel statuses
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders MODIFY COLUMN diamond_status ENUM(
                'r_order_in_process',
                'r_order_shipped',
                'r_order_cancelled',
                'd_diamond_in_discuss',
                'd_diamond_in_making',
                'd_diamond_completed',
                'd_diamond_in_certificate',
                'd_order_shipped',
                'd_order_cancelled',
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
                'j_order_cancelled',
                'processed',
                'completed'
            ) NULL COMMENT 'Production progress'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        // Update any cancelled orders to a safe status before removing ENUM values
        \Illuminate\Support\Facades\DB::statement("
            UPDATE orders 
            SET diamond_status = 'processed' 
            WHERE diamond_status IN ('r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled')
        ");

        // Revert the ENUM first
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders MODIFY COLUMN diamond_status ENUM(
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

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancel_reason', 'cancelled_at', 'cancelled_by']);
        });
    }
};
