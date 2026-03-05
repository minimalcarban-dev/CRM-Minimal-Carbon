<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shopify_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_url');
            $table->text('access_token'); // Encrypted via model cast
            $table->string('api_version')->default('2024-01');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_settings');
    }
};
