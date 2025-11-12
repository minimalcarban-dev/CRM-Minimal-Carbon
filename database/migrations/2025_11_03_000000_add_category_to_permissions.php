<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('category')->after('slug')->nullable();
            $table->index('category');
        });

        // Update existing permissions with categories
        DB::table('permissions')->whereRaw("slug LIKE '%orders%'")->update(['category' => 'orders']);
        DB::table('permissions')->whereRaw("slug LIKE '%admins%'")->update(['category' => 'admins']);
        DB::table('permissions')->whereRaw("slug LIKE '%metal_types%'")->update(['category' => 'metal_types']);
        DB::table('permissions')->whereRaw("slug LIKE '%setting_types%'")->update(['category' => 'setting_types']);
        DB::table('permissions')->whereRaw("slug LIKE '%closure_types%'")->update(['category' => 'closure_types']);
        DB::table('permissions')->whereRaw("slug LIKE '%ring_sizes%'")->update(['category' => 'ring_sizes']);
        DB::table('permissions')->whereRaw("slug LIKE '%stone_colors%'")->update(['category' => 'stone_colors']);
        DB::table('permissions')->whereRaw("slug LIKE '%stone_shapes%'")->update(['category' => 'stone_shapes']);
        DB::table('permissions')->whereRaw("slug LIKE '%stone_types%'")->update(['category' => 'stone_types']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};