# Admin Chat Feature — Implementation Plan

Goal
----
Provide a secure, maintainable chat system inside the Admin panel so admins can chat with each other and share files. Deliver an MVP fast (text chat + attachments + real-time), then expand for presence, notifications, search, moderation and scale.

Project status summary
----------------------
What we've already added to this project (high level):

- Authentication & admin guard: a dedicated `admin` guard and provider in `config/auth.php` and an `Admin` model that extends `Authenticatable` (remember token support added).
- Guard-based login/logout with rate limiting and session regeneration in `app/Http/Controllers/AdminAuthController.php`.
- Middleware: `EnsureAdminAuthenticated` and `EnsureAdminHasPermission` now use the `admin` guard and are registered as middleware aliases in `bootstrap/app.php`.
- Permission model & seeder: `PermissionSeeder` seeds permissions; `AdminPermissionController` and UI page `resources/views/admins/permissions.blade.php` exist for managing permissions.
- Permission caching: per-admin permission caching helpers and `clearPermissionCache()` implemented in `App\Models\Admin`.
- Storage & uploads: switched admin document uploads to use the `Storage` facade and the `public` disk; added `storage:link` guidance to `README_admin_panel.md`.
- File cleanup: controllers now delete/recycle previous files through `Storage::disk('public')`.
- Routes & route protection: admin routes updated to use `admin.auth` and `admin.permission:*` middleware.
- Session & environment hardening: `.env.example` updated with secure defaults for sessions and example bootstrap super-admin credentials; session migration added.
- Tests & CI: feature tests (auth, permissions, uploads) were added under `tests/Feature` and a GitHub Actions workflow `/.github/workflows/phpunit.yml` is present to run tests.
- README & TODO: `README_admin_panel.md` and `TODO.md` updated to reflect many of the above changes and provide local setup steps.

What we're currently adding (in-progress):

- Chat architecture & plan doc (this file) — completed and expanded.
- Documentation updates (this `docs/ADMIN_CHAT_PLAN.md` is being expanded with project status and operational suggestions).
- Project task tracking: updated project todo list to include chat work and phase items (see `TODO.md` and in-repo todo list).

Future plans (next phases / backlog):

- Phase 1 (MVP): implement chat DB schema (channels, messages, attachments), basic message API, broadcasting events, and a simple chat UI inside the admin panel.
- Phase 2: presence, typing indicators, read receipts, channel membership management and search.
- Phase 3: attachments processing (S3 signed uploads, queued thumbnailing, virus scanning), notifications (push), and audit logs.
- Phase 4: moderation tools, reporting, retention/archival policies, and scaling the WebSocket infra.

Suggestion: Admin permission & order management system (concise recommended approach)
-----------------------------------------------------------------------
1) Keep the current custom permission table for now, but standardize and group permissions by category:
  - Add a `category` column to `permissions` and group seeds (e.g., `admins`, `permissions`, `orders`, `metal_types`, `stone_types`, ...).
  - Render grouped permissions in the UI with category-level "Select all" and tri-state behavior.

2) Add role templates & bulk-assign:
  - Create `role_templates` (name + permission_ids JSON) and seed common sets (Viewer, Editor, Admin, OrdersManager).
  - Allow applying templates in the permissions UI with a preview and an "apply as union" vs "replace" option.

3) Secure assignment & audit:
  - Only allow `admins.assign_permissions` or `is_super` to assign permissions.
  - Log permission changes (who changed what, before/after) to an audit table and surface recent changes in the admin UI.

4) Performance & caching:
  - Keep per-admin permission caching (we added this). Use Redis in production and short TTLs; clear cache on updates.

5) Authorization best practices for Orders & other resources:
  - Use permission checks in controllers and Blade via $currentAdmin->hasPermission('orders.view') or Laravel Gates/Policies where suitable.
  - For orders specifically: create fine-grained permissions (orders.view, orders.create, orders.edit, orders.delete) and consider `orders.manage_assigned` if you have owner-based assignments.
  - Add event-driven notifications (e.g., broadcast an OrderCreated event) and ensure controllers dispatch such events.

6) Tests & CI:
  - Add feature tests that seed permissions and assert both allowed/forbidden behavior for order routes and permission management paths.

7) Consider spatie/laravel-permission only after the above is stable:
  - If you need role hierarchies, teams, or advanced features, migrate to spatie. Plan a migration window and map existing `permissions` to spatie tables.

Why this approach is recommended
--------------------------------
- It minimizes large migrations and risk: we keep the current work (tests, caching, permission UI) and extend incrementally.
- It prioritizes security/auditability (permission logging), UX (grouping and templates), and performance (caching) — the highest ROI for admins.
- Spatie is powerful but larger to integrate; adopt it later if you need its role features.

Phases (high level)
-------------------
- Phase 0 — Design & decision
  - Choose realtime provider (self-hosted WebSockets via beyondcode/laravel-websockets or Pusher), queue backend (Redis), and storage (S3 for prod / public disk for dev).
  - Decide UI stack (Alpine, Vue or React). Keep it lightweight; Alpine + Blade is fine for MVP.

- Phase 1 — MVP (deliverable in ~1 week)
  - DB schema + models for channels, channel_user, messages, message_attachments, message_reads.
  - REST API endpoints: create channel, post message, get history (paginated).
  - Save messages to DB and attachments to disk/S3.
  - Setup broadcasting (events) and WebSocket server (laravel-websockets) + laravel-echo on client.
  - Simple chat UI (list + composer) inside admin panel, real-time updates via Echo.
  - Tests: message creation, file upload (Storage::fake) and simple API tests.

- Phase 2 — UX & bulk features (2–4 days)
  - Presence (who is online), typing indicators, unread counts, read receipts.
  - Search by message text (basic DB full-text) or Meilisearch later.
  - Pagination and lazy loading of older messages.

- Phase 3 — Attachments & processing (2–4 days)
  - Signed uploads to S3 (or direct to disk), queued jobs for thumbnails and virus-scan.
  - Attachment metadata and secure URLs for downloads.

- Phase 4 — Moderation, audit & production hardening (3–7 days)
  - Audit logs for message actions and admin moderation actions.
  - Reporting, blocking users, content moderation rules, rate-limits.
  - Monitor queue workers, socket connection metrics; add retention/archival policy.

Data model (minimal)
--------------------
- channels
  - id (pk), type (direct|group), name (nullable), metadata(json), created_by, created_at

- channel_user
  - id, channel_id, admin_id, role (member|admin), joined_at

- messages
  - id, channel_id, sender_id, body (text), message_type (text|file), metadata(json), created_at

- message_attachments
  - id, message_id, disk_path, file_name, mime, size, metadata(json)

- message_reads
  - id, message_id, admin_id, read_at

API design (examples)
---------------------
- GET  /admin/channels                       -> list channels the current admin belongs to
- POST /admin/channels                       -> create channel (members list)
- GET  /admin/channels/{channel}/messages    -> paginated message history (page, per_page, before_id)
- POST /admin/channels/{channel}/messages    -> post a message (body / optional attachment id)
- POST /admin/channels/{channel}/attachments -> upload attachment (returns attachment id)
- POST /admin/channels/{channel}/read        -> mark messages read (optional: message_id)

WebSocket events (server side)
------------------------------
- MessagePosted (broadcast to channel:channels.{id})
  - payload: message id, body, sender, attachments metadata, created_at
- Presence events (if using presence channels): here, join/leave with admin id and name
- TypingIndicator events: ephemeral, no DB write

Frontend components
-------------------
- ChannelList (left column): list of channels + unread counts + search
- ChatWindow (main): header (channel name, members), messages list (virtualized), composer
- Message component: text, attachments, timestamps, read indicators
- Attachment viewer modal: image preview, download link

Attachments flow
----------------
- Option A (recommended): signed direct-to-S3 uploads (fast, scalable).
  - Client requests a signed URL from server; client uploads directly to S3; server stores metadata and associates with message.
- Option B (simpler): upload to server API (POST multipart) and server stores via Storage facade (ok for small projects).
- Always validate file type/size server-side and scan files if possible.

Security considerations
-----------------------
- Socket auth: use `Broadcast::routes(['middleware' => ['auth:admin']])` (guarded socket auth). Use signed cookies or Bearer token with short TTL.
- TLS for API + WebSockets (wss). Ensure reverse proxy / load balancer supports websockets.
- Validate all file uploads and set size limits (e.g., 10–50MB depending on needs).
- Rate limit message sends per user per minute to prevent spam.
- Permission checks: ensure only channel members can post/read. Admins with moderation roles can remove messages.

Auditing & moderation
---------------------
- Save change logs: who deleted/edited a message, when and why (optional comment).
- Provide an admin moderation UI: view flagged messages, remove or hide, and block users.

Testing strategy
----------------
- Unit tests: models and helper methods.
- Feature tests: API posting message, fetching history, uploading attachments (Storage::fake), permission checks.
- Integration: run a lightweight test that simulates broadcasting (or mock events) to assert client behavior.

Dev & deploy commands (quick)
-----------------------------
- Install websockets package (self-hosted):
```powershell
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"
```

- Install Echo client (frontend):
```powershell
npm install --save laravel-echo pusher-js
npm run dev
```

- Create storage link (for public disk):
```powershell
php artisan storage:link
```

- Example migration commands:
```powershell
php artisan make:migration create_chat_tables --create=channels
php artisan migrate
```

Operational notes
-----------------
- Use Redis for queues and caching; run Horizon for workers.
- Use a CDN for attachments (CloudFront) and signed URLs for security.
- Monitor queue latency, failed jobs, and socket connection counts.

Estimated timeline (rough)
--------------------------
- MVP: 3–7 days (DB, API, websockets, basic UI)
- Attachments + queues + scan: +2–4 days
- Presence/typing/read receipts: +2–3 days
- Moderation, search, and hardening: +1–3 weeks depending on scope

Next steps I can take (pick one)
-------------------------------
- "Start MVP": I will add migrations, models and a minimal API and a simple Blade + Alpine chat UI (Phase 1).
- "Start MVP + attachments": include attachments flow and Storage APIs in the first pass.
- "Design only": create a short architecture doc with sequence diagrams and socket auth details.

If you want me to start, tell me which option above and I'll begin implementing the files and tests.  


Files to be added/edited during Phase 1 (examples)
-------------------------------------------------
- `database/migrations/*` (channels, channel_user, messages, message_attachments, message_reads)
- `app/Models/Channel.php`, `ChannelUser.php`, `Message.php`, `MessageAttachment.php`, `MessageRead.php`
- `app/Events/MessagePosted.php`
- `app/Http/Controllers/Api/ChatController.php`
- `resources/views/admins/chat.blade.php` and small JS under `resources/js/chat.js`
- Tests under `tests/Feature/Chat*`


Contact me which option to start with and I will implement Phase 1 files and update the project's todo list accordingly.
