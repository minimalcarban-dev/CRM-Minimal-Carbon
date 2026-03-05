<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shopify_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopify_collection_id')->unique();
            $table->string('title');
            $table->string('handle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_collections');
    }
};
