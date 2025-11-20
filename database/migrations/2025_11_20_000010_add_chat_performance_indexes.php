<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                // Compound index for retrieval in channel order
                $table->index(['channel_id','created_at'], 'idx_messages_channel_created');
                $table->index('sender_id', 'idx_messages_sender');
            });
        }
        if (Schema::hasTable('message_reads')) {
            Schema::table('message_reads', function (Blueprint $table) {
                $table->index(['message_id','user_id'], 'idx_message_reads_lookup');
            });
        }
        if (Schema::hasTable('message_attachments')) {
            Schema::table('message_attachments', function (Blueprint $table) {
                $table->index(['message_id','mime_type'], 'idx_msg_attachments_mime');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex('idx_messages_channel_created');
                $table->dropIndex('idx_messages_sender');
            });
        }
        if (Schema::hasTable('message_reads')) {
            Schema::table('message_reads', function (Blueprint $table) {
                $table->dropIndex('idx_message_reads_lookup');
            });
        }
        if (Schema::hasTable('message_attachments')) {
            Schema::table('message_attachments', function (Blueprint $table) {
                $table->dropIndex('idx_msg_attachments_mime');
            });
        }
    }
};
