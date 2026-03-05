<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shopify_product_id')->nullable()->after('id');
            $table->unsignedBigInteger('shopify_variant_id')->nullable()->after('shopify_product_id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shopify_product_id', 'shopify_variant_id', 'last_synced_at']);
        });
    }
};
