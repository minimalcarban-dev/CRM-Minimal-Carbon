<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Meta Accounts - Stores connected Facebook/Instagram accounts
        Schema::create('meta_accounts', function (Blueprint $table) {
            $table->id();

            $table->enum('platform', ['facebook', 'instagram'])->default('facebook');
            $table->string('account_id')->unique();
            $table->string('account_name');
            $table->string('page_id')->nullable(); // For Facebook Pages

            // Access tokens (encrypted)
            $table->text('access_token');
            $table->timestamp('token_expires_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('platform');
            $table->index('is_active');
        });

        // Meta Conversations - Each conversation thread
        Schema::create('meta_conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meta_account_id')->constrained()->cascadeOnDelete();

            $table->string('conversation_id')->unique(); // Meta's conversation ID
            $table->enum('platform', ['facebook', 'instagram'])->default('facebook');

            $table->timestamp('last_message_at')->nullable();
            $table->boolean('is_read')->default(false);

            $table->timestamps();

            $table->index('last_message_at');
            $table->index('is_read');
        });

        // Meta Messages - Individual messages in conversations
        Schema::create('meta_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meta_conversation_id')->constrained()->cascadeOnDelete();

            $table->string('message_id')->unique(); // Meta's message ID
            $table->enum('direction', ['incoming', 'outgoing'])->default('incoming');

            $table->text('content')->nullable();
            $table->json('attachments')->nullable(); // Images, videos, files

            // Delivery status
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->timestamp('read_at')->nullable();

            // Sender info for incoming messages
            $table->string('sender_id')->nullable();
            $table->string('sender_name')->nullable();

            $table->timestamps();

            $table->index('direction');
            $table->index('status');
            $table->index('created_at');
        });

        // Meta Message Logs - Delivery tracking and retry attempts
        Schema::create('meta_message_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meta_message_id')->constrained()->cascadeOnDelete();

            $table->string('event_type'); // sent, delivered, read, failed
            $table->text('api_response')->nullable();
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_message_logs');
        Schema::dropIfExists('meta_messages');
        Schema::dropIfExists('meta_conversations');
        Schema::dropIfExists('meta_accounts');
    }
};
