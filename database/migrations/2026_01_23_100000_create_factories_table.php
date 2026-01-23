<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Adds contact_person and contact_phone columns to existing factories table.
     * The factories table already exists with: id, name, code, location, notes, is_active, created_by, timestamps, soft_deletes.
     */
    public function up(): void
    {
        Schema::table('factories', function (Blueprint $table) {
            // Add contact fields if they don't exist
            if (!Schema::hasColumn('factories', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('code');
            }
            if (!Schema::hasColumn('factories', 'contact_phone')) {
                $table->string('contact_phone', 20)->nullable()->after('contact_person');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factories', function (Blueprint $table) {
            if (Schema::hasColumn('factories', 'contact_person')) {
                $table->dropColumn('contact_person');
            }
            if (Schema::hasColumn('factories', 'contact_phone')) {
                $table->dropColumn('contact_phone');
            }
        });
    }
};
