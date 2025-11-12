<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (! Schema::hasTable('messages')) {
			return;
		}

		// Try to add a FULLTEXT index on body if MySQL; otherwise add a normal index as fallback
		try {
			$driver = DB::getDriverName();
			if ($driver === 'mysql') {
				DB::statement('ALTER TABLE `messages` ADD FULLTEXT INDEX `ft_messages_body` (`body`)');
			} else {
				Schema::table('messages', function (Blueprint $table) {
					$table->index('body', 'idx_messages_body');
				});
			}
		} catch (\Throwable $e) {
			// ignore if index already exists or engine doesn't support it
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if (! Schema::hasTable('messages')) {
			return;
		}

		try {
			$driver = DB::getDriverName();
			if ($driver === 'mysql') {
				DB::statement('ALTER TABLE `messages` DROP INDEX `ft_messages_body`');
			} else {
				Schema::table('messages', function (Blueprint $table) {
					$table->dropIndex('idx_messages_body');
				});
			}
		} catch (\Throwable $e) {
			// ignore
		}
	}
};

