<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shopify_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // Import, Export, Webhook, Sync
            $table->string('entity_type'); // Product, Collection, Order
            $table->string('entity_id')->nullable();
            $table->string('status'); // Success, Failed
            $table->json('request_payload')->nullable();
            $table->text('response_message')->nullable();
            $table->timestamps();

            $table->index(['action', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_sync_logs');
    }
};
