<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use App\Models\Admin;
use App\Observers\AdminObserver;
use App\Events\UserMentioned;
use App\Listeners\SendChatMentionNotification;
use App\Models\Invoice;
use App\Policies\InvoicePolicy;

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

        // Register event listeners
        Event::listen(
            UserMentioned::class,
            SendChatMentionNotification::class
        );

        // Register Invoice policy
        Gate::policy(Invoice::class, InvoicePolicy::class);
    }
}
