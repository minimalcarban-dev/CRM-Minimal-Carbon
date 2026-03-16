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
        Schema::create('jewellery_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->enum('type', ['ring', 'earrings', 'tennis_bracelet', 'other'])->default('other');
            $table->string('name');
            $table->foreignId('metal_type_id')->constrained('metal_types')->restrictOnDelete();
            $table->foreignId('ring_size_id')->nullable()->constrained('ring_sizes')->nullOnDelete();
            $table->decimal('weight', 10, 3)->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->enum('status', ['in_stock', 'low_stock', 'out_of_stock'])->default('out_of_stock');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jewellery_stocks');
    }
};
