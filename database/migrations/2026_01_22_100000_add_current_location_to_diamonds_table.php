<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds current_location field to track where each diamond is physically located.
     */
    public function up(): void
    {
        Schema::table('diamonds', function (Blueprint $table) {
            $table->string('current_location', 100)->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diamonds', function (Blueprint $table) {
            $table->dropColumn('current_location');
        });
    }
};
