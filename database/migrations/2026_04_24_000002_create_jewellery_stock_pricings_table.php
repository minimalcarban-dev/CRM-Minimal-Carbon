<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jewellery_stock_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jewellery_stock_id')->constrained('jewellery_stocks')->cascadeOnDelete();
            $table->string('material_code', 30);
            $table->string('metal_color', 20)->nullable();
            $table->decimal('net_weight_grams', 10, 3)->default(0);
            $table->decimal('purity_percent', 6, 2)->default(0);
            $table->decimal('base_rate_usd_per_gram', 12, 4)->default(0);
            $table->decimal('material_value', 12, 2)->default(0);
            $table->decimal('labor_rate_usd_per_gram', 10, 2)->default(0);
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->decimal('stone_cost', 12, 2)->default(0);
            $table->decimal('extra_cost', 12, 2)->default(0);
            $table->decimal('subtotal_cost', 12, 2)->default(0);
            $table->decimal('commission_percent', 6, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('profit_percent', 6, 2)->default(0);
            $table->decimal('profit_amount', 12, 2)->default(0);
            $table->decimal('sales_markup_percent', 6, 2)->default(0);
            $table->decimal('sales_markup_amount', 12, 2)->default(0);
            $table->decimal('listing_price', 12, 2)->default(0);
            $table->string('rate_source')->nullable();
            $table->timestamp('rate_fetched_at')->nullable();
            $table->boolean('is_default_listing')->default(false);
            $table->timestamps();

            $table->unique(['jewellery_stock_id', 'material_code', 'metal_color'], 'jsp_stock_material_color_unique');
            $table->index(['jewellery_stock_id', 'is_default_listing'], 'jsp_stock_default_index');
            $table->index(['material_code', 'metal_color'], 'jsp_material_color_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jewellery_stock_pricings');
    }
};
