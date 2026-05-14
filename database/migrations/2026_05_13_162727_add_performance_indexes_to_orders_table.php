<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⚡ PERFORMANCE: Add missing indexes to the orders table.
 *
 * These indexes target the most frequently-queried columns in the
 * OrderController@index method, which was running 200+ SQL queries
 * and experiencing slow page loads.
 *
 * Expected impact:
 * - tracking_status: Used for tracking summary card groupBy queries
 * - created_at: Used for today's sales, month sales, date range filters
 * - payment_status: Used for payment status filter dropdown
 * - deleted_at + diamond_status: Covers the dashboard aggregate query
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Index for tracking status counts (groupBy LOWER(tracking_status))
            $table->index('tracking_status', 'idx_orders_tracking_status');

            // Index for date-based sales queries (today's/monthly sales)
            $table->index('created_at', 'idx_orders_created_at');

            // Index for payment status filter
            $table->index('payment_status', 'idx_orders_payment_status');

            // Composite index for the main dashboard aggregate query
            // Covers: WHERE deleted_at IS NULL + diamond_status conditions + dispatch_date
            $table->index(['deleted_at', 'diamond_status', 'dispatch_date'], 'idx_orders_dashboard_stats');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_tracking_status');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_payment_status');
            $table->dropIndex('idx_orders_dashboard_stats');
        });
    }
};
