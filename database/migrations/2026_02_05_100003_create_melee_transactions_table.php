<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('melee_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('melee_diamond_id')->constrained()->onDelete('cascade');

            // Transaction Details
            $table->enum('transaction_type', ['in', 'out', 'adjustment']);
            $table->integer('pieces'); // Positive for IN, Negative for OUT/Sale
            $table->decimal('carat_weight', 10, 3)->default(0);

            // Reference (e.g., Order ID or manual)
            $table->string('reference_type')->default('manual'); // 'manual', 'order'
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();

            // Metadata
            $table->foreignId('created_by')->constrained('admins');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('melee_transactions');
    }
};
