<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to add party_id and invoice_image to expenses table.
 * 
 * - party_id: Links to parties table (Banks + In Person category)
 * - invoice_image: JSON field to store Cloudinary upload metadata
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Add party_id foreign key (nullable for backward compatibility)
            $table->foreignId('party_id')
                ->nullable()
                ->after('paid_to_received_from')
                ->constrained('parties')
                ->nullOnDelete();
            
            // Add invoice_image JSON field for Cloudinary metadata
            $table->json('invoice_image')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['party_id']);
            $table->dropColumn(['party_id', 'invoice_image']);
        });
    }
};
