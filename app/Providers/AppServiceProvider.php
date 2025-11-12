<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Admin;
use App\Observers\AdminObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Attach observer to auto-enroll new admins into default channels
        Admin::observe(AdminObserver::class);

        // Share currently-logged-in admin with all views (nullable)
        View::composer('*', function ($view) {
            /** @var Admin|null $admin */
            $admin = Auth::guard('admin')->user();
            $view->with('currentAdmin', $admin instanceof Admin ? $admin : null);
        });
    }
}
