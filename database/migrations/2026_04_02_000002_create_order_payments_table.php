<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->comment('Collected payment amount');
            $table->string('payment_method', 50)->nullable()->comment('Cash, UPI, bank transfer, etc.');
            $table->string('reference_number', 191)->nullable()->comment('Receipt / transaction reference');
            $table->text('notes')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['order_id', 'received_at'], 'idx_order_payments_order_received');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
