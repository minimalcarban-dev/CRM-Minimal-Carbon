<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained('email_accounts')->onDelete('cascade');
            $table->string('message_id')->index(); // Gmail Message ID
            $table->string('thread_id')->index();  // Gmail Thread ID
            $table->string('subject')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->index();
            $table->text('to_recipients')->nullable();
            $table->text('cc_recipients')->nullable();
            $table->text('bcc_recipients')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_plain')->nullable();
            $table->timestamp('received_at')->index();
            $table->boolean('has_attachments')->default(false);
            $table->integer('size_bytes')->default(0);
            $table->json('labels')->nullable();
            $table->json('headers')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['email_account_id', 'message_id']);
            $table->index(['email_account_id', 'thread_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
