<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shopify_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopify_product_id')->unique();
            $table->unsignedBigInteger('shopify_variant_id')->nullable();
            $table->string('title');
            $table->string('handle')->nullable();
            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('status')->default('active'); // active, draft, archived
            $table->longText('description_html')->nullable();
            $table->json('images')->nullable();
            $table->string('tags')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->integer('inventory_quantity')->default(0);

            // ── Custom Metafields (Jewellery-specific) ──
            $table->string('metal_purity')->nullable();
            $table->string('metal')->nullable();
            $table->string('resizable')->nullable();
            $table->string('comfort_fit')->nullable();
            $table->string('ring_height_1')->nullable();
            $table->string('ring_width_1')->nullable();
            $table->string('product_video')->nullable(); // Shopify Files CDN URL
            $table->string('stone_measurement')->nullable();
            $table->string('stone_clarity')->nullable();
            $table->string('stone_carat_weight')->nullable();
            $table->string('stone_color')->nullable();
            $table->string('stone_shape')->nullable();
            $table->string('stone_type')->nullable();
            $table->string('side_stone_type')->nullable();
            $table->string('side_shape')->nullable();
            $table->string('side_color')->nullable();
            $table->string('side_carat_weight')->nullable();
            $table->string('side_measurement')->nullable();
            $table->string('side_clarity')->nullable();
            $table->string('melee_size')->nullable();

            // ── Relations ──
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_products');
    }
};
