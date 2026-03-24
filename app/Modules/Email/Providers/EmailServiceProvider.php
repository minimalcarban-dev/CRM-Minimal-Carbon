<?php

namespace App\Modules\Email\Providers;

use App\Modules\Email\Commands\SyncGmailCommand;
use App\Modules\Email\Models\Email;
use App\Modules\Email\Models\EmailAccount;
use App\Modules\Email\Policies\EmailPolicy;
use App\Modules\Email\Services\GmailAuthService;
use App\Modules\Email\Services\GmailSyncService;
use App\Modules\Email\Services\EmailComposeService;
use App\Modules\Email\Services\AuditLogger;
use App\Modules\Email\Repositories\EmailRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Services
        $this->app->bind(GmailAuthService::class, function ($app) {
            return new GmailAuthService();
        });

        $this->app->bind(GmailSyncService::class, function ($app) {
            return new GmailSyncService($app->make(GmailAuthService::class));
        });

        $this->app->bind(EmailComposeService::class, function ($app) {
            return new EmailComposeService($app->make(GmailAuthService::class));
        });

        $this->app->singleton(AuditLogger::class, function ($app) {
            return new AuditLogger();
        });

        // Bind Repositories
        $this->app->singleton(EmailRepository::class, function ($app) {
            return new EmailRepository();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Policies
        Gate::policy(Email::class, EmailPolicy::class);
        Gate::policy(EmailAccount::class, EmailPolicy::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncGmailCommand::class,
            ]);
        }

        // Load Routes
        $this->registerRoutes();

        // Load Views
        $this->loadViewsFrom(resource_path('views/email'), 'email');
    }

    /**
     * Register the module routes.
     */
    protected function registerRoutes(): void
    {
        Route::prefix('admin/email')
            ->name('email.')
            ->middleware(['web', 'admin.auth', 'admin.permission:mail.access'])
            ->group(base_path('routes/email.php'));
    }
}
