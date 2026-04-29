<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jewellery_stocks')) {
            return;
        }

        $this->ensureFlexibleTypeColumn();

        Schema::table('jewellery_stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('jewellery_stocks', 'metal_purity')) {
                $table->string('metal_purity')->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'closure_type_id')) {
                $table->foreignId('closure_type_id')->nullable()->constrained('closure_types')->nullOnDelete();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'length')) {
                $table->decimal('length', 8, 2)->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'width')) {
                $table->decimal('width', 8, 2)->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'diameter')) {
                $table->decimal('diameter', 8, 2)->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'bale_size')) {
                $table->decimal('bale_size', 8, 2)->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'primary_stone_type_id')) {
                $table->foreignId('primary_stone_type_id')->nullable()->constrained('stone_types')->nullOnDelete();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'primary_stone_weight')) {
                $table->decimal('primary_stone_weight', 10, 3)->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'primary_stone_count')) {
                $table->integer('primary_stone_count')->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'primary_stone_shape_id')) {
                $table->foreignId('primary_stone_shape_id')->nullable()->constrained('stone_shapes')->nullOnDelete();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'primary_stone_color_id')) {
                $table->foreignId('primary_stone_color_id')->nullable()->constrained('stone_colors')->nullOnDelete();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'primary_stone_clarity_id')) {
                $table->foreignId('primary_stone_clarity_id')->nullable()->constrained('diamond_clarities')->nullOnDelete();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'primary_stone_cut_id')) {
                $table->foreignId('primary_stone_cut_id')->nullable()->constrained('diamond_cuts')->nullOnDelete();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'side_stone_type_id')) {
                $table->foreignId('side_stone_type_id')->nullable()->constrained('stone_types')->nullOnDelete();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'side_stone_weight')) {
                $table->decimal('side_stone_weight', 10, 3)->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'side_stone_count')) {
                $table->integer('side_stone_count')->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'certificate_number')) {
                $table->string('certificate_number')->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'certificate_type')) {
                $table->string('certificate_type', 100)->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'certificate_url')) {
                $table->text('certificate_url')->nullable();
            }

            if (!Schema::hasColumn('jewellery_stocks', 'images')) {
                $table->json('images')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Repair migration: keep columns to avoid data loss on rollback.
    }

    private function ensureFlexibleTypeColumn(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE jewellery_stocks MODIFY type VARCHAR(255) NOT NULL DEFAULT 'other'");
        }
    }
};
