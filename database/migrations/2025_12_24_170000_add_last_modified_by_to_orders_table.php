<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Adds last_modified_by column to track who last edited the order.
     * This keeps submitted_by (original creator) unchanged when order is edited.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('last_modified_by')
                ->nullable()
                ->after('submitted_by')
                ->comment('Admin who last modified the order');
        });

        // Add foreign key constraint
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('last_modified_by', 'fk_orders_last_modified_by')
                    ->references('id')
                    ->on('admins')
                    ->onDelete('SET NULL');
            });
        } catch (\Throwable $e) {
            // If admins table doesn't exist yet, skip FK - column still gets created
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->dropForeign('fk_orders_last_modified_by');
            } catch (\Throwable $e) {
            }
            $table->dropColumn('last_modified_by');
        });
    }
};
