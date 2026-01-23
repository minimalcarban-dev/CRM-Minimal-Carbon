<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Creates table for archiving daily sales history per company.
     */
    public function up(): void
    {
        Schema::create('company_daily_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->date('sales_date');
            $table->integer('order_count')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->json('order_type_breakdown')->nullable(); // {"ready_to_ship": 3, "custom_diamond": 2, "custom_jewellery": 1}
            $table->timestamps();

            // Unique constraint: one record per company per day
            $table->unique(['company_id', 'sales_date'], 'company_daily_sales_unique');
            $table->index('sales_date', 'idx_sales_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_daily_sales');
    }
};
