<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Creates the gold_purchases table for Gold Tracking module.
     * Tracks gold purchases with payment details and expense integration.
     * All gold is assumed to be 24K purity (no purity field needed).
     * Payment modes: cash, bank_transfer only (no UPI).
     */
    public function up(): void
    {
        Schema::create('gold_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('purchase_date');
            $table->decimal('weight_grams', 10, 3); // e.g., 40.500 grams
            $table->decimal('rate_per_gram', 12, 2); // e.g., 6500.00 INR
            $table->decimal('total_amount', 15, 2); // Auto-calculated: weight × rate

            // Supplier details
            $table->string('supplier_name');
            $table->string('supplier_mobile', 20)->nullable();
            $table->string('invoice_number')->nullable();

            // Status: pending (no payment yet) or completed (payment done)
            $table->enum('status', ['pending', 'completed'])->default('completed');

            // Payment details - only cash or bank_transfer (no UPI)
            $table->enum('payment_mode', ['cash', 'bank_transfer'])->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_ifsc', 20)->nullable();

            $table->text('notes')->nullable();

            // Relationships
            $table->foreignId('admin_id')->constrained('admins')->onDelete('restrict');
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('purchase_date');
            $table->index('status');
            $table->index(['purchase_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_purchases');
    }
};
