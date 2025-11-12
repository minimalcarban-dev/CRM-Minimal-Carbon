<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\Channel;

class AdminObserver
{
    /**
     * Handle the Admin "created" event.
     */
    public function created(Admin $admin): void
    {
        // Attach new admin to default channels if they exist
        $defaults = Channel::whereIn('name', ['General', 'Public'])->get(['id']);
        if ($defaults->isNotEmpty()) {
            $admin->channels()->syncWithoutDetaching($defaults->pluck('id')->all());
        }
    }
}
