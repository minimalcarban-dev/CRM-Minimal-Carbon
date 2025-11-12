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
        // Create table with columns first. Add foreign key constraints separately to avoid ordering issues
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // ===== CORE INFO =====
            // Use enum on MySQL (supported) but give explicit index name for predictable dropping later
            $table->enum('order_type', ['ready_to_ship', 'custom_diamond', 'custom_jewellery'])
                ->comment('Type of order');

            // ===== CLIENT & ORDER DETAILS =====
            $table->text('client_details')->comment('Client information or notes');
            $table->text('jewellery_details')->nullable()->comment('Jewellery details for relevant orders');
            $table->text('diamond_details')->nullable()->comment('Diamond details for relevant orders');

            // ===== FILES =====
            $table->json('images')->nullable()->comment('Up to 10 uploaded image paths');
            $table->json('order_pdfs')->nullable()->comment('Up to 5 uploaded PDF paths (compressed if >10MB)');

            // ===== REFERENCES / DROPDOWNS =====
            // Define foreign id columns (do not add FK constraints here to avoid dependency order problems)
            $table->unsignedBigInteger('gold_detail_id')->nullable();
            $table->unsignedBigInteger('ring_size_id')->nullable();
            $table->unsignedBigInteger('setting_type_id')->nullable();
            $table->unsignedBigInteger('earring_type_id')->nullable();
            $table->unsignedBigInteger('company_id');

            // ===== STATUS & NOTES =====
            $table->enum('note', ['priority', 'non_priority'])->nullable()->comment('Order priority');
            $table->enum('diamond_status', [
                'processed',
                'completed',
                'diamond_purchased',
                'factory_making',
                'diamond_completed'
            ])->nullable()->comment('Production progress');

            // ===== FINANCIALS =====
            $table->decimal('gross_sell', 10, 2)->default(0.00)->comment('Total sale amount');

            // ===== SHIPPING =====
            $table->string('shipping_company_name')->nullable()->comment('Courier company name');
            $table->string('tracking_number')->nullable()->comment('Tracking ID');
            $table->string('tracking_url')->nullable()->comment('Tracking URL');
            $table->date('dispatch_date')->nullable()->comment('Dispatch date');

            // ===== USER =====
            $table->unsignedBigInteger('submitted_by')->nullable()->comment('Admin who created the order');

            // ===== INDEXES =====
            $table->index(['order_type', 'diamond_status', 'company_id'], 'idx_orders_type_status_company');
            $table->index('dispatch_date', 'idx_orders_dispatch_date');

            $table->timestamps();
        });

        // Add foreign key constraints in a separate step so migrations remain robust if referenced tables exist
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreign('gold_detail_id', 'fk_orders_gold_detail')->references('id')->on('metal_types')->onDelete('SET NULL');
                $table->foreign('ring_size_id', 'fk_orders_ring_size')->references('id')->on('ring_sizes')->onDelete('SET NULL');
                $table->foreign('setting_type_id', 'fk_orders_setting_type')->references('id')->on('setting_types')->onDelete('SET NULL');
                $table->foreign('earring_type_id', 'fk_orders_earring_type')->references('id')->on('closure_types')->onDelete('SET NULL');
                $table->foreign('company_id', 'fk_orders_company')->references('id')->on('companies')->onDelete('CASCADE');
                $table->foreign('submitted_by', 'fk_orders_submitted_by')->references('id')->on('admins')->onDelete('CASCADE');
            });
        } catch (\Throwable $e) {
            // If referenced tables are not present yet (migration order), log or ignore — migration will still create columns
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign keys safely
            try {
                $table->dropForeign(['gold_detail_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['ring_size_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['setting_type_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['earring_type_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['company_id']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['submitted_by']);
            } catch (\Throwable $e) {
            }

            // Drop indexes
            $table->dropIndex(['order_type', 'diamond_status', 'company_id']);
            $table->dropIndex(['dispatch_date']);
        });

        Schema::dropIfExists('orders');
    }
};
