<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('melee_diamonds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('melee_category_id')->constrained()->onDelete('cascade');

            // Specifications
            $table->string('shape'); // Round, Pear, etc.
            $table->string('color')->nullable(); // For Tambuli (D, E, F...) or null
            $table->string('sieve_size')->nullable(); // 000, +2, -7
            $table->string('size_label'); // "1.0-1.2mm" (Display Name)

            // Stock Management
            $table->integer('total_pieces')->default(0);
            $table->integer('available_pieces')->default(0);
            $table->integer('sold_pieces')->default(0); // Computed: total - available

            $table->decimal('total_carat_weight', 10, 3)->default(0);
            $table->decimal('available_carat_weight', 10, 3)->default(0);

            // Pricing
            $table->decimal('purchase_price_per_ct', 12, 2)->default(0);
            $table->decimal('listing_price_per_ct', 12, 2)->default(0);

            // Status
            $table->enum('status', ['in_stock', 'low_stock', 'out_of_stock'])->default('out_of_stock');
            $table->integer('low_stock_threshold')->default(50);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('melee_diamonds');
    }
};
