<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('admins');
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->cascadeOnDelete(); // Self-referencing for threads
            $table->integer('thread_count')->default(0); // Cache for number of replies
            // Body can be null for attachment-only messages
            $table->text('body')->nullable();
            $table->string('type')->default('text');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['channel_id', 'reply_to_id']); // Performance index
        });

        Schema::create('message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            // Use user_id to match Eloquent relation in MessageRead model
            $table->foreignId('user_id')->constrained('admins');
            $table->timestamp('read_at');
            $table->timestamps();
            $table->unique(['message_id', 'user_id']);
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('thumbnail_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('messages');
    }
};