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
        if (Schema::hasTable('message_attachments') && ! Schema::hasColumn('message_attachments', 'status')) {
            Schema::table('message_attachments', function (Blueprint $table) {
                $table->enum('status', ['pending', 'processed', 'infected'])->nullable()->after('metadata')->comment('Processing status for attachments');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('message_attachments') && Schema::hasColumn('message_attachments', 'status')) {
            Schema::table('message_attachments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
