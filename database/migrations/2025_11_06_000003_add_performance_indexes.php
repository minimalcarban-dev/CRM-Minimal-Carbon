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
		// Add some commonly useful indexes to speed up queries; safe operations (wrapped in try/catch)
		try {
			if (Schema::hasTable('messages')) {
				Schema::table('messages', function (Blueprint $table) {
					if (! \Illuminate\Support\Facades\Schema::hasColumn('messages', 'sender_id')) return;
					$table->index('sender_id', 'idx_messages_sender_id');
					$table->index('channel_id', 'idx_messages_channel_id');
				});
			}
		} catch (\Throwable $e) {
			// ignore
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		try {
			if (Schema::hasTable('messages')) {
				Schema::table('messages', function (Blueprint $table) {
					try { $table->dropIndex('idx_messages_sender_id'); } catch (\Throwable $e) {}
					try { $table->dropIndex('idx_messages_channel_id'); } catch (\Throwable $e) {}
				});
			}
		} catch (\Throwable $e) {
			// ignore
		}
	}
};

