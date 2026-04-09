# Jewellery Admin Panel

A full-featured back-office admin panel built with **Laravel 11** for a jewellery and diamond trading business. The system handles order lifecycle management, real-time admin-to-admin chat, role-based access control, and product catalogue configuration.

---

## What This Project Is

This is an internal tool — not a customer-facing application. It is used by staff and administrators to:

- **Create and track jewellery orders** across three types: Ready to Ship, Custom Diamond, and Custom Jewellery
- **Communicate in real time** via a full-featured chat system built into the panel
- **Manage access control** by assigning granular permissions to individual administrators
- **Maintain product data** such as metal types, ring sizes, stone colors, setting types, and closure types
- **Manage client companies** associated with orders

---

## Tech Stack

| Layer             | Technology                       |
| ----------------- | -------------------------------- |
| Backend Framework | Laravel 11 (PHP 8.2)             |
| Database          | MySQL 8.0                        |
| Frontend (Chat)   | Vue 3 (Composition API)          |
| Real-time         | Laravel Echo + Pusher            |
| Asset Bundling    | Vite                             |
| CSS               | Bootstrap 5 + custom styling     |
| Queue             | Laravel Database Queues          |
| Search            | Laravel Scout (database driver)  |
| File Security     | ClamAV virus scanning (optional) |
| Image Processing  | Intervention Image               |
| Testing           | Pest PHP                         |
| CI                | GitHub Actions                   |

---

## Core Features

### Authentication

- Dedicated `admin` authentication guard separate from the default Laravel user system
- Session-based login with rate limiting (5 attempts per IP/email combination before lockout)
- Secure session configuration: encrypted, HTTP-only, SameSite=lax, optional secure cookie
- Session regeneration on login and invalidation on logout

### Role-Based Access Control (RBAC)

- Custom permission system — no third-party package dependency
- Permissions are scoped per-module using dot notation slugs: `orders.create`, `admins.edit`, `chat.access`, etc.
- Permissions are cached per-admin (10-minute TTL) and invalidated on update
- Super admin flag bypasses all permission checks
- Permission assignment UI with category grouping and select-all controls
- Automatic General channel membership when `chat.access` is granted; membership removed when revoked

### Order Management

Orders have three distinct types, each with a different form and field set:

| Type                 | Key Fields                                                                                                      |
| -------------------- | --------------------------------------------------------------------------------------------------------------- |
| **Ready to Ship**    | Client details, jewellery/diamond details, metal type, ring size, setting type, earring type, company, shipping |
| **Custom Diamond**   | Client details, diamond specifications (4Cs), diamond status tracking, company, shipping                        |
| **Custom Jewellery** | Client details, jewellery + optional diamond details, product specs, company, shipping                          |

- Dynamic form loading via AJAX based on selected order type
- Image uploads (up to 10) and PDF uploads (up to 5) per order
- PDF compression via Ghostscript for files over 10 MB
- Diamond status tracking: processed → completed → diamond_purchased → factory_making → diamond_completed
- Shipping details with tracking number and URL
- Searchable order list with filter by type and diamond status

### Real-Time Chat

A complete messaging system for admins, embedded in the panel:

- **Group channels** — created by super admin, members managed by owner or super admin
- **Direct messages** — one-on-one channels created on demand
- **Real-time delivery** — messages broadcast via Pusher private channels using Laravel Echo
- **Typing indicators** — whisper events sent while composing
- **Read receipts** — per-message read tracking with `last_read_at` pivot for accurate unread counts
- **@Mentions** — inline autocomplete with member list; mention notifications broadcast to the mentioned admin's private notification channel
- **Reply threading** — reply to any message with a preview chip; reply context stored in message metadata
- **File attachments** — images and documents with configurable MIME type allowlist and size limits
- **Virus scanning** — optional ClamAV integration for uploaded files; infected files are deleted before persisting
- **Image thumbnails** — generated asynchronously via a queued job
- **Link extraction** — URLs in messages are indexed to a separate table for efficient sidebar queries
- **Channel sidebar** — shows channel info, member list, shared media (images), shared files, and shared links
- **Message search** — search across channels the requesting admin belongs to
- **Membership events** — admins are notified in real time when added to or removed from a channel
- **Unread counts** — per-channel unread count based on `last_read_at` pivot timestamp

### Product Attribute Management

Full CRUD for catalogue data used to populate order forms:

- Metal Types
- Setting Types
- Closure Types
- Ring Sizes
- Stone Types
- Stone Shapes
- Stone Colors

All attribute tables share the same structure: `id`, `name`, `is_active`, `timestamps`. All CRUD operations are permission-gated.

### Company Management

- Create, edit, and delete client companies
- Status toggle (active/inactive)
- Companies are associated with orders at creation time
- Searchable and filterable list

### File Handling

- All uploaded files stored via Laravel's `Storage` facade on the `public` disk
- UUID-based filenames to prevent enumeration and collisions
- Magic bytes (MIME) validation in addition to extension validation for chat attachments
- Existing files deleted from disk when replaced or when the parent record is deleted
- `storage:link` required for public disk access

---

## Project Structure (Key Locations)

```
app/
  Http/Controllers/
    AdminAuthController.php       # Login, logout, rate limiting
    AdminController.php           # Admin CRUD, document uploads
    AdminPermissionController.php # Permission assignment, chat.access sync
    ChatController.php            # All chat API endpoints
    OrderController.php           # Order CRUD, dynamic form partials
    PermissionController.php      # Permission CRUD
    [Attribute]Controller.php     # MetalType, RingSize, etc.
  Models/
    Admin.php                     # Custom authenticatable, permission cache
    Channel.php                   # Chat channels with soft deletes
    Message.php                   # Chat messages with Scout search
    MessageAttachment.php         # File attachments, auto-deletes on model delete
    MessageLink.php               # Indexed URLs extracted from message bodies
    Order.php                     # Orders with JSON image/pdf columns
    Permission.php                # Permission model with group cache
  Events/
    MessageSent.php               # Broadcast on new message
    MessagesRead.php              # Broadcast on read receipt
    UserMentioned.php             # Broadcast to mentioned admin
    ChannelMembershipChanged.php  # Broadcast on add/remove from channel
  Jobs/
    ProcessChatAttachment.php     # Async thumbnail generation + re-scan
  Services/
    VirusScanner.php              # ClamAV wrapper with fallback
resources/
  js/
    components/Chat.vue           # Full chat SPA component (Vue 3)
    bootstrap.js                  # Echo setup, global 401/403 handling
routes/
  web.php                         # All admin routes with permission middleware
  channels.php                    # Broadcast channel authorization
database/
  migrations/                     # 20+ migrations including chat tables
  seeders/
    PermissionSeeder.php          # Seeds all permission slugs
    SuperAdminSeeder.php          # Bootstrap super admin from env
    ChatDefaultSeeder.php         # Creates General and Public channels
```

---

## Local Setup

### Requirements

- PHP 8.2+
- MySQL 8.0+
- Node.js 18+
- Composer
- A Pusher account (or compatible WebSocket server)

### Steps

```bash
# 1. Clone and install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure .env
# Set DB_*, PUSHER_*, and VITE_PUSHER_* values
# Set SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD

# 4. Database
php artisan migrate
php artisan db:seed

# 5. Storage
php artisan storage:link

# 6. Build assets
npm run dev        # development
npm run build      # production

# 7. Queue worker (required for chat attachments and broadcasting)
php artisan queue:work --queue=default --tries=3
```

### Optional: Virus Scanning

Set `CHAT_VIRUS_SCAN=true` in `.env` and ensure `clamscan` is available on your PATH. If ClamAV is not found but scanning is enabled, the system logs a warning and allows the file through.

---

## Environment Variables Reference

| Variable                                                 | Purpose                                       | Default                                                                |
| -------------------------------------------------------- | --------------------------------------------- | ---------------------------------------------------------------------- |
| `DB_*`                                                   | Database connection                           | —                                                                      |
| `BROADCAST_CONNECTION`                                   | Broadcasting driver (`pusher`, `log`, `null`) | `log`                                                                  |
| `PUSHER_APP_KEY` / `PUSHER_APP_SECRET` / `PUSHER_APP_ID` | Pusher credentials                            | —                                                                      |
| `VITE_PUSHER_APP_KEY` / `VITE_PUSHER_APP_CLUSTER`        | Client-side Pusher config                     | —                                                                      |
| `QUEUE_CONNECTION`                                       | Queue driver                                  | `database`                                                             |
| `CHAT_MAX_UPLOAD_MB`                                     | Max attachment size per file                  | `10`                                                                   |
| `CHAT_ALLOWED_MIMES`                                     | Comma-separated MIME allowlist                | `image/jpeg,image/png,image/gif,image/webp,application/pdf,text/plain` |
| `CHAT_VIRUS_SCAN`                                        | Enable ClamAV scanning                        | `false`                                                                |
| `SUPER_ADMIN_EMAIL`                                      | Bootstrap super admin email                   | `superadmin@example.com`                                               |
| `SUPER_ADMIN_PASSWORD`                                   | Bootstrap super admin password                | `ChangeMe!123!`                                                        |
| `SESSION_SECURE_COOKIE`                                  | Require HTTPS for session cookie              | `true`                                                                 |

---

## Running Tests

```bash
php artisan test
```

Tests use `RefreshDatabase` and cover:

- Admin login/logout with rate limiting
- Permission assignment (super admin grants, normal admin denied)
- Document upload via `Storage::fake`
- Chat endpoints: channel access, message send, attachment upload, read receipts, broadcast auth

---

## Broadcast Architecture

```
Admin sends message
       │
ChatController::sendMessage()
       │
  Message saved to DB
       │
MessageSent event dispatched (afterCommit = true)
       │
Queue worker picks up broadcast job
       │
Pusher pushes to private-chat.channel.{id}
       │
All connected members receive via Echo listener
       │
Vue component updates messages array and scrolls
```

Channel authorization happens at `/admin/broadcasting/auth`, protected by the `admin` guard and channel membership check in `routes/channels.php`.

---

## Known Limitations

- Broadcasting requires a Pusher account or a compatible self-hosted server (e.g., Soketi). The `log` driver is set by default, which means real-time features are disabled until Pusher is configured.
- PDF compression uses `exec()` to call Ghostscript, which must be installed on the server.
- Image thumbnail generation uses Intervention Image v2 API. Ensure Intervention Image v2 is installed (`intervention/image ^2.0`).
- The queue worker must be running for file processing jobs and broadcast delivery. In production, run it under Supervisor or a similar process manager.
- `SESSION_SECURE_COOKIE=true` in `.env.example` will prevent login on HTTP (non-HTTPS) local development. Set it to `false` locally.

---

## Security Notes

- Admin sessions are encrypted and HTTP-only
- Login is rate-limited per IP+email (5 attempts, then temporary lockout)
- All file uploads validate MIME type via magic bytes, not just file extension
- Chat attachments are optionally scanned by ClamAV before being persisted
- Channel membership is verified server-side for every message read/send and for broadcast auth
- Non-member channel requests return 404 (not 403) to avoid leaking channel existence
- 403 responses on admin routes redirect to the dashboard rather than showing an error page, preventing information disclosure about route structure

---

## License

Private/proprietary. Not for redistribution.
