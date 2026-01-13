<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Creates order_drafts table for storing incomplete/failed orders.
     */
    public function up(): void
    {
        Schema::create('order_drafts', function (Blueprint $table) {
            $table->id();

            // Who created this draft
            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')
                ->references('id')
                ->on('admins')
                ->onDelete('cascade');

            // Order type (same as orders table)
            $table->enum('order_type', ['ready_to_ship', 'custom_diamond', 'custom_jewellery'])
                ->nullable()
                ->comment('Type of order being drafted');

            // All form data stored as JSON
            $table->json('form_data')->comment('Complete form data as JSON');

            // Error message if draft was created due to error
            $table->text('error_message')->nullable()->comment('Error that caused draft creation');

            // How was this draft created
            $table->enum('source', ['auto_save', 'error', 'manual'])
                ->default('auto_save')
                ->comment('How the draft was created');

            // Last step the user was on
            $table->string('last_step', 100)->nullable()->comment('Last form section being filled');

            // Client name for quick display (extracted from form_data)
            $table->string('client_name', 191)->nullable()->comment('Client name for display');

            // Company ID for reference
            $table->unsignedBigInteger('company_id')->nullable();

            // Expiry date (90 days from creation by default)
            $table->timestamp('expires_at')->nullable()->comment('Auto-delete after this date');

            $table->timestamps();

            // Indexes for common queries
            $table->index('admin_id', 'idx_drafts_admin');
            $table->index('expires_at', 'idx_drafts_expires');
            $table->index(['admin_id', 'order_type'], 'idx_drafts_admin_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_drafts');
    }
};
