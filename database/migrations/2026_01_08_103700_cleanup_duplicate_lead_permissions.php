<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete duplicate lead permissions with old slug format (leads-view instead of leads.view)
        DB::table('permissions')->whereIn('slug', [
            'leads-view',
            'leads-create',
            'leads-edit',
            'leads-delete',
            'leads-assign',
            'leads-message'
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse deletion
    }
};
