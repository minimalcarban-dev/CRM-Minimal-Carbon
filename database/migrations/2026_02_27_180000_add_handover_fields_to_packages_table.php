<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('party_type', 50)->nullable()->after('slip_id');
            $table->string('company_name')->nullable()->after('party_type');
            $table->string('gst_number', 50)->nullable()->after('company_name');
            $table->string('pan_number', 20)->nullable()->after('gst_number');
            $table->string('purpose_of_handover', 500)->nullable()->after('package_description');
            $table->string('stock_id', 100)->nullable()->after('purpose_of_handover');
            $table->string('handover_location')->nullable()->after('stock_id');
            $table->string('handover_mode', 50)->nullable()->after('handover_location');
            $table->string('diamond_shape', 100)->nullable()->after('handover_mode');
            $table->string('diamond_size', 100)->nullable()->after('diamond_shape');
            $table->string('diamond_color', 50)->nullable()->after('diamond_size');
            $table->string('diamond_clarity', 50)->nullable()->after('diamond_color');
            $table->decimal('diamond_carat', 10, 3)->nullable()->after('diamond_clarity');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'party_type',
                'company_name',
                'gst_number',
                'pan_number',
                'purpose_of_handover',
                'stock_id',
                'handover_location',
                'handover_mode',
                'diamond_shape',
                'diamond_size',
                'diamond_color',
                'diamond_clarity',
                'diamond_carat',
            ]);
        });
    }
};

