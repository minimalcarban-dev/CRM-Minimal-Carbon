# Realtime and Permissions — Ops Notes

This project uses Laravel Echo + Pusher for realtime chat and a centralized 403 handling policy for admin routes.

## Realtime (Broadcasting/Queues)

Root fixes implemented:
- Events broadcast only after DB commit to prevent stale model serialization.
  - Files: `app/Events/MessageSent.php`, `app/Events/MessagesRead.php`
  - Change: `public $afterCommit = true;` on broadcast events.
- Admin-guarded broadcasting auth endpoint.
  - Files: `app/Providers/BroadcastServiceProvider.php`, `routes/channels.php`
  - Change: `Broadcast::routes([... 'prefix' => 'admin', 'middleware' => ['web','admin.auth','auth:admin']])` and membership check.

Required runtime setup:
- .env
  - `BROADCAST_DRIVER=pusher`
  - `QUEUE_CONNECTION=database`
  - Pusher keys for both server and Vite client: `PUSHER_*` and `VITE_PUSHER_*` matching the same app.
- Queues
  - Start a worker to deliver broadcast jobs:

    ```powershell
    php artisan queue:work --queue=default --tries=3
    ```

  - Production: run under a supervisor service. Example Supervisor program (Linux):

    ```ini
    [program:laravel-queue]
    process_name=%(program_name)s_%(process_num)02d
    command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
    autostart=true
    autorestart=true
    numprocs=1
    redirect_stderr=true
    stdout_logfile=/var/log/supervisor/laravel-queue.log
    ```

## Global 403 Handling (Admin)

Root fixes implemented:
- Server-side transform of admin 403s to a friendly redirect for HTML, and JSON responses with a redirect hint for XHR.
  - File: `bootstrap/app.php`
  - Behavior: AuthorizationException and 403 HttpException under `/admin/*` redirect to `/admin/dashboard` (or set `X-Redirect` header + `{ redirect: "/admin/dashboard" }` in JSON).
- Frontend honors redirect hints and shows a countdown modal before navigation.
  - File: `resources/js/bootstrap.js`
  - Behavior: Axios interceptor displays a modal for 5s then redirects to the hinted location.

## Channel Access Hygiene

- Channels list only returns channels the current admin is a member of.
  - File: `app/Http/Controllers/ChatController.php@getChannels`
  - Behavior: `whereHas('users', ...)` filters by membership, includes unread counts and management flags.
- If a channel becomes inaccessible (e.g., membership removed) while open, the UI gracefully informs the user and reverts selection.
  - File: `resources/js/components/Chat.vue` (loadMessages error handling)

## Verification quick checks

- Run `php artisan route:list | Select-String chat` to confirm endpoints.
- Ensure there are no failed jobs: `php artisan queue:failed`.
- Trigger a test message in two admin sessions to verify live updates.
