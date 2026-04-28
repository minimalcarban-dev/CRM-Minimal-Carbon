<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds VGL push tracking columns to the orders table.
     * These columns track whether an order has been pushed to VGL,
     * when it was pushed, and the VGL-side crm_order record ID for reconciliation.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('vgl_pushed_at')->nullable()->after('cancelled_by');
            $table->string('vgl_push_status', 20)->nullable()->after('vgl_pushed_at'); // 'success', 'failed'
            $table->unsignedBigInteger('vgl_crm_order_id')->nullable()->after('vgl_push_status'); // ID returned from VGL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['vgl_pushed_at', 'vgl_push_status', 'vgl_crm_order_id']);
        });
    }
};
