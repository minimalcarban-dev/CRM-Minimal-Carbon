<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register broadcasting auth endpoint under /admin with admin guard
        Broadcast::routes([
            'middleware' => ['web', 'admin.auth', 'auth:admin'],
            'prefix' => 'admin',
        ]);

        require base_path('routes/channels.php');
    }
}