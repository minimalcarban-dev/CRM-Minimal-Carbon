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
        Schema::table('diamonds', function (Blueprint $table) {
            $table->text('description')->nullable()->after('measurement');
            $table->unsignedBigInteger('admin_id')->nullable()->after('description');
            $table->text('note')->nullable()->after('admin_id');
            $table->string('diamond_type')->nullable()->after('note');
            $table->json('multi_img_upload')->nullable()->after('diamond_type');
            $table->unsignedBigInteger('assign_by')->nullable()->after('multi_img_upload');
            $table->timestamp('assigned_at')->nullable()->after('assign_by');
            
            // Add foreign keys
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('assign_by')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diamonds', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['assign_by']);
            $table->dropColumn([
                'description',
                'admin_id',
                'note',
                'diamond_type',
                'multi_img_upload',
                'assign_by',
                'assigned_at'
            ]);
        });
    }
};
