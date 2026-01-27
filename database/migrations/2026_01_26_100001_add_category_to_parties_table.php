<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to add category field to parties table.
 * 
 * Categories:
 * - gold_metal: Gold Metal suppliers
 * - jewelry_mfg: Jewelry Manufacturing
 * - diamond_gemstone: Diamond & Gemstone vendors
 * - banks: Bank accounts
 * - in_person: In-person/cash transactions
 * 
 * Using VARCHAR instead of ENUM for flexibility and future extensibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            // Add category column after 'name' - nullable initially for existing records
            $table->string('category', 50)->nullable()->after('name');
            
            // Add index for performance (frequent filtering by category)
            $table->index('category', 'parties_category_index');
        });

        // Set default category for existing records
        DB::table('parties')->whereNull('category')->update(['category' => 'in_person']);
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropIndex('parties_category_index');
            $table->dropColumn('category');
        });
    }
};
