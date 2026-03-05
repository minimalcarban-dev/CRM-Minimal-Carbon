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
        Schema::table('shopify_products', function (Blueprint $table) {
            $table->text('tags')->nullable()->change();
            $table->text('title')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            $table->string('tags', 255)->nullable()->change();
            $table->string('title', 255)->nullable()->change();
        });
    }
};
