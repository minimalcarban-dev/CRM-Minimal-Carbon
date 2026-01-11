<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Channel;

class ChatDefaultSeeder extends Seeder
{
    /**
     * Seed default chat channels and ensure all admins are members.
     */
    public function run(): void
    {
        // Find a creator (prefer super admin)
        $creator = Admin::where('is_super', true)->first() ?? Admin::first();
        $creatorId = $creator?->id ?? 1;

        // General channel (group)
        $general = Channel::firstOrCreate(
            ['name' => 'General'],
            [
                'type' => 'group',
                'description' => 'General chat channel for all administrators',
                'created_by' => $creatorId,
            ]
        );


        // Attach all admins to both channels idempotently
        $adminIds = Admin::pluck('id')->all();
        $general->users()->syncWithoutDetaching($adminIds);
    }
}
