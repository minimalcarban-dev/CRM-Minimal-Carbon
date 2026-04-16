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
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            // Re-define type to include more categories (since it's an enum, we change it to string for flexibility or update enum)
            // For now, let's keep it as enum but add more or change to string.
            // Actually, let's change it to string so it can support ANY market category.
            $table->string('type')->change();

            // Metal Details
            $table->string('metal_purity')->nullable()->after('metal_type_id');
            $table->foreignId('closure_type_id')->nullable()->after('metal_purity')->constrained('closure_types')->nullOnDelete();

            // Dimensions
            $table->decimal('length', 8, 2)->nullable()->after('ring_size_id');
            $table->decimal('width', 8, 2)->nullable()->after('length');
            $table->decimal('diameter', 8, 2)->nullable()->after('width');
            $table->decimal('bale_size', 8, 2)->nullable()->after('diameter');

            // Primary Stone
            $table->foreignId('primary_stone_type_id')->nullable()->after('description')->constrained('stone_types')->nullOnDelete();
            $table->decimal('primary_stone_weight', 10, 3)->nullable()->after('primary_stone_type_id');
            $table->integer('primary_stone_count')->nullable()->after('primary_stone_weight');
            $table->foreignId('primary_stone_shape_id')->nullable()->after('primary_stone_count')->constrained('stone_shapes')->nullOnDelete();
            $table->foreignId('primary_stone_color_id')->nullable()->after('primary_stone_shape_id')->constrained('stone_colors')->nullOnDelete();
            $table->foreignId('primary_stone_clarity_id')->nullable()->after('primary_stone_color_id')->constrained('diamond_clarities')->nullOnDelete();
            $table->foreignId('primary_stone_cut_id')->nullable()->after('primary_stone_clarity_id')->constrained('diamond_cuts')->nullOnDelete();

            // Side Stones
            $table->foreignId('side_stone_type_id')->nullable()->after('primary_stone_cut_id')->constrained('stone_types')->nullOnDelete();
            $table->decimal('side_stone_weight', 10, 3)->nullable()->after('side_stone_type_id');
            $table->integer('side_stone_count')->nullable()->after('side_stone_weight');

            // Certificates
            $table->string('certificate_number')->nullable()->after('side_stone_count');
            $table->string('certificate_type')->nullable()->after('certificate_number'); // GIA, IGI, etc.
            $table->text('certificate_url')->nullable()->after('certificate_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jewellery_stocks', function (Blueprint $table) {
            $table->dropForeign(['closure_type_id']);
            $table->dropForeign(['primary_stone_type_id']);
            $table->dropForeign(['primary_stone_shape_id']);
            $table->dropForeign(['primary_stone_color_id']);
            $table->dropForeign(['primary_stone_clarity_id']);
            $table->dropForeign(['primary_stone_cut_id']);
            $table->dropForeign(['side_stone_type_id']);

            $table->dropColumn([
                'metal_purity',
                'closure_type_id',
                'length',
                'width',
                'diameter',
                'bale_size',
                'primary_stone_type_id',
                'primary_stone_weight',
                'primary_stone_count',
                'primary_stone_shape_id',
                'primary_stone_color_id',
                'primary_stone_clarity_id',
                'primary_stone_cut_id',
                'side_stone_type_id',
                'side_stone_weight',
                'side_stone_count',
                'certificate_number',
                'certificate_type',
                'certificate_url'
            ]);

            // Revert type to enum if possible, but usually change back to string is safer or just leave as string.
        });
    }
};
