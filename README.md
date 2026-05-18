# Jewellery Admin Panel

A full-featured back-office admin panel built with **Laravel 11** for a jewellery and diamond trading business. The system handles order lifecycle management, real-time admin-to-admin chat, role-based access control, and product catalogue configuration.

---

## What This Project Is

This is an internal tool — not a customer-facing application. It is used by staff and administrators to:

- **Create and track jewellery orders** across three types: Ready to Ship, Custom Diamond, and Custom Jewellery
- **Jewellery Stock Management** with dynamic pricing matrices, gemstone details, and side-stone tracking
- **Inventory Tracking** for Melee Diamonds (by category/weight) and Gold stocks
- **Communicate in real time** via a full-featured chat system with typing indicators and threading
- **Financial Operations** including multi-currency invoicing, expense tracking, and purchase management
- **CRM & Lead Management** with SLA tracking and automated status updates
- **Integrations** with Shopify, Meta (WhatsApp), and VGL Certificate systems
- **Advanced Dashboard** providing real-time business intelligence with custom date filtering
- **Manage access control** by assigning granular permissions to individual administrators
- **Maintain product data** such as metal types, ring sizes, stone colors, setting types, and closure types

---

## Tech Stack

| Layer             | Technology                       |
| ----------------- | :------------------------------- |
| Backend Framework | Laravel 11 (PHP 8.2)             |
| Database          | MySQL 8.0                        |
| Frontend (Chat)   | Vue 3 (Composition API)          |
| Real-time         | Laravel Echo + Pusher            |
| Asset Bundling    | Vite                             |
| CSS               | Bootstrap 5 + custom styling     |
| Image Storage     | Cloudinary / Local Storage       |
| Integrations      | Meta API, GMail API, 17Track     |
| Queue             | Laravel Database Queues          |
| Search            | Laravel Scout (database driver)  |
| File Security     | ClamAV virus scanning (optional) |
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

| Type                       | Key Fields                                                                                                      |
| -------------------------- | --------------------------------------------------------------------------------------------------------------- |
| **Ready to Ship**    | Client details, jewellery/diamond details, metal type, ring size, setting type, earring type, company, shipping |
| **Custom Diamond**   | Client details, diamond specifications (4Cs), diamond status tracking, company, shipping                        |
| **Custom Jewellery** | Client details, jewellery + optional diamond details, product specs, company, shipping                          |

- Dynamic form loading via AJAX based on selected order type
- Image uploads (up to 10) and PDF uploads (up to 5) per order
- PDF compression via Ghostscript for files over 10 MB
- **Standardized Status Tracking**: Unified 15-step workflow (In Discuss → Making → ... → Repairing → Shipped) for both Custom Diamond and Custom Jewellery.
- Shipping details with tracking number and URL
- Order index header now surfaces compact clickable Total Orders and Total Shipped metrics beside the action buttons, while the stats strip focuses on Ready to Ship, Custom Diamond, Custom Jewellery, Tracking, and Today's Sales.
- Order index tracking summary card showing In Transit, Out for Delivery, and Delivered shipment totals, with each count linking to its matching tracking-status filter even when those orders are already shipped
- Searchable order list with filter by type and diamond status
- Order index status updates now feature a premium portal-based custom dropdown that provides a theme-consistent UI with icons and smooth animations. Additionally, status changes now trigger real-time notifications to other relevant administrators, ensuring team-wide synchronization.
- **Enhanced Dropdown Stability**: Implemented a global portal manager that prevents menu clipping and ensures dropdowns automatically close on scroll or when interacting with other elements, providing a rock-solid desktop-like experience.
- **Notification Testing**: Verified the automated status update notification pipeline with dedicated feature tests (`tests/Feature/OrderStatusNotificationTest.php`) ensuring reliable delivery to administrators.
- **Standardized Button Aesthetics**: Unified the visual style of "Edit Shipping", "Go to Investigation", and "Start Investigation" buttons with consistent full-width alignment and centering to ensure a premium, balanced UI layout across all tracking cells.
- Manual shipping edits, manual status changes, tracker syncs, and tracking webhooks all append to the existing `audit_logs`-backed order history timeline
- **⚡ Order Index Performance Optimization**: Reduced page load from 200+ SQL queries (~250ms) to ~10 SQL queries (~35ms) through:
    - **Conditional Aggregation**: Replaced 12 separate `COUNT` queries with a single `SUM(CASE WHEN...)` dashboard stats query.
    - **Bulk Company Stats**: Replaced 131 per-company accessor queries with 2 bulk aggregate SQL queries + cache (5-min TTL).
    - **N+1 Elimination**: Replaced expensive `payment_status_label` and `amount_received_total` model accessors (which re-queried `order_payments` per row) with direct column reads from the stored `payment_status`, `amount_received`, and `amount_due` columns.
    - **Targeted Database Indexes**: Added composite indexes on `(deleted_at, diamond_status, dispatch_date)`, `tracking_status`, `created_at`, and `payment_status`.
- **Standardized Order Status Workflow**: Implemented a unified, 15-step production sequence across "Custom Diamond" and "Custom Jewellery" order types:
    - **Integrated Repairing Status**: Added a dedicated `Repairing` stage for both diamond and jewellery flows.
    - **Unified Logic**: Synchronized `StatusTransitionService` to enforce the new master sequence, ensuring consistent state transitions.
    - **UI Synchronization**: Updated order list and filter dropdowns with standardized color-coded badges, icons, and human-readable labels.
    - **Form Integration**: Updated create and edit order forms with the new status sequence for better operational clarity.

### Real-Time Chat

A complete messaging system for admins, embedded in the panel:

- **Group channels** — created by super admin, members managed by owner or super admin
- **Direct messages** — one-on-one channels created on demand
- **Real-time delivery** — messages broadcast via Pusher private channels using Laravel Echo
- **Typing indicators** — real-time "user is typing" feedback across channels
- **Read receipts** — per-message read tracking with `last_read_at` pivot for accurate unread counts
- **@Mentions** — inline autocomplete with member list and push notifications
- **Reply threading** — structured replies with context previews
- **File attachments** — images and documents with virus scanning (ClamAV)
- **Shared Media** — sidebar view of all images, files, and links shared in a channel
- **Message search** — indexed search across accessible channels via Laravel Scout

- **Jewellery Stock Management** — A professional inventory system for finished jewellery items:
    - **Pricing Matrix** — Dynamic calculation of selling price based on metal price (Gold 10k/14k/18k, Platinum), weight, labor rates, and profit margins.
    - **Live Rate Synchronization** — Real-time fetching of gold and silver rates with a multi-tiered fallback strategy (Custom API → Navkar Proxy → Coinbase). Includes a "Force Refresh" mechanism to bypass server-side caching (60s gold, 30m silver, 1h USD) for instant pricing accuracy.
    - **Weight Terminology Standardization** — System-wide use of "Net Weight" for physical jewellery weight (replacing legacy "Gross Weight" labels) to ensure consistency across inventory, pricing matrices, and order views.
    - **Gemstone Integration** — Detailed tracking of primary stones (carat weight, price, shape, color, clarity, cut).
    - **Side Stones Repeater** — Manage multiple side stone types, weights, and prices per jewellery item.
    - **Auto-Pricing Sync** — Real-time calculation of total stone investment (weight + price) with automatic injection into the 'Stone Cost' field across all pricing matrix variants for accurate margin calculation.
    - **Pricing Visibility Controls** — `jewellery_stock.view_pricing` gates purchase cost, component gemstone cost, per-carat stone prices, and stone totals on jewellery detail pages; `jewellery_stock.view_profit` separately gates profit and margin output.

- **Diamond Management** — Comprehensive tracking for individual loose diamonds:
    - **Inventory Visibility** — Added detailed specifications (Cut, Clarity, Color, Shape, Measurement) to the diamond detail page for full transparency.
    - **Quick Overview** — Standardized the diamond management index with new columns for Color and Clarity, enabling faster inventory assessment.
    - **4Cs Tracking** — Integrated management of Cut, Clarity, Color, and Carat weight across the entire lifecycle from import to sale.
    - **Smart Filters** — Advanced filtering by all diamond specifications, including range filters for price and weight.

### Inventory & Financials

- **Melee Diamond Inventory** — Track small diamonds by category, shape, and weight with total value calculation.
    - Atomic stock deduction/return with net-quantity diffing during order edits (prevents duplicate transactions).
    - **Single Write Path Architecture**: Eliminated dual-write issues by migrating all Eloquent mutations to a centralized `MeleeStockService`, ensuring robust, test-verified transaction integrity and safe bridging of legacy fields during schema transitions.
    - **Policy-Based Authorization (Sprint 3)**: All melee mutations are now governed by `MeleeDiamondPolicy` registered in `AppServiceProvider`. Permission slugs follow the dot-notation convention: `melee.view`, `melee.create`, `melee.edit`, `melee.delete`. Super-admins bypass these automatically; regular admins require explicit permission assignment.
    - **Form Request Validation (Sprint 3)**: Inline `$request->validate()` calls in `addShape()` and `update()` are now replaced by dedicated `StoreMeleeRequest` and `UpdateMeleeRequest` classes (in `app/Http/Requests/`), which also enforce authorization. This centralizes validation rules for future UI/documentation use.
    - **Sprint 3 Tests**: 30 new tests added — `MeleeDiamondPolicyTest` (unit, 10 cases) and `MeleeFormRequestTest` (feature, 10 cases) — all green with zero regressions on Sprint 1+2 characterization tests.
    - **Sprint 4 — View Decomposition**: The 3,183-line monolithic `resources/views/melee/index.blade.php` has been decomposed into **8 focused partials** under `resources/views/melee/partials/`:
        - `_page_header.blade.php` — Title, Lab Grown/Natural tab switcher, Add/Use Stock buttons.
        - `_stats_cards.blade.php` — Super-admin-only stat cards (Total Diamonds, Total Value, Avg. Value).
        - `_category_sidebar.blade.php` — Lab Grown and Natural category nav lists with delete and add buttons.
        - `_diamond_panel.blade.php` — Full shapes accordion, diamond table rows, add-size and add-shape bars.
        - `_modal_history.blade.php` — Stock history modal with transaction table and diamond info header.
        - `_modal_quick_order.blade.php` — Order quick-view modal (dark gradient header, improved footer CTA).
        - `_modal_edit_melee.blade.php` — Edit diamond shape/size modal with gradient header and grouped input layout.
        - `_modal_edit_transaction.blade.php` — Edit transaction modal with indigo gradient header and larger inputs.
        - The pre-existing `transaction_modal.blade.php` (Add/Use Stock form) remains in place.
        - The orphaned `stock_table.blade.php` was deleted (it was never wired in).
        - `index.blade.php` is now **~2,644 lines** — primarily CSS + `@include` wires + inline JS (Phase 2 deferred).
        - All 51 Melee tests remain green with zero regressions. Modals received visual upgrades (gradient headers, themed icons, improved input sizing) matching the system design language.
    - **Sprint 6 — Observer, Hardened Audit & Full Test Coverage**:
        - **MeleeObserver** (`app/Observers/MeleeObserver.php`) registered in `AppServiceProvider::boot()` and wired to three application events: `MeleeCreated`, `MeleeStatusChanged` (fires **only** when `status` actually changes, guarded by `wasChanged('status')`), and `MeleeDeleted`.
        - **Hardened Audit Command** — `php artisan melee:audit` extended with 3 new read-only checks: zero-purchase-price diamonds (34 flagged), empty categories (3 flagged), and a 24-hour recent-transaction count. Scheduled daily at 06:00 via `routes/console.php`.
        - **Full Integration Tests** — `MeleeStockServiceTest` (9 tests, 36 assertions) covers all public service methods: `deductForOrder` (happy path, insufficient stock, idempotency), `returnForOrder`, `adjustForOrderDiff` (net increase, net decrease, no change), `recordManualTransaction` (stock in, stock out insufficient).
        - **Observer Tests** — `MeleeObserverTest` (4 tests) verifies all three event dispatches and the no-event guard using scoped `Event::fake([Xyz::class])` to prevent Eloquent internal model events from being intercepted.
        - **PHPUnit Attribute Migration** — All 44 `/** @test */` doc-comment annotations across 4 melee test files converted to `#[Test]` PHP 8 attribute syntax, eliminating PHPUnit 12 deprecation warnings.
        - **Total test score after Sprint 6:** 64 tests / 144 assertions — all green.
        - Architecture documentation written to `docs/melee/README.md` covering write path, row locking, idempotency, permissions, audit usage, and known drift fix plan.


- **Gold Tracking** — Manage gold stock levels and transactions across different purities. Features a unified transaction log with styled links to orders for consumed gold, allowing administrators to jump directly from a tracking entry to the relevant order details.
- **Multi-Currency Invoices** — Generate professional invoices with support for different regions and currency symbols
- **Expense & Purchase Tracking** — Centralized log of business expenses and inventory purchases
- **Lead CRM** — Track potential client inquiries with 24-hour SLA monitoring and status pipeline
- **Shipment Investigation Module** — Specialized workspace for identifying and resolving stalled shipments:
    - **Bento-grid Workspace** — A modern, high-density dashboard that perfectly fits the viewport without unnecessary scrolling. The layout uses optimized CSS flexbox properties to dynamically stretch content like tracking logs to perfectly fill available vertical space.
    - **Admin Timeline** — Internal audit log and communication channel for administrators. The note composer includes UX improvements such as "Enter" to submit and "Shift+Enter" for multi-line formatting.
    - **Tracking History** — Integrated scrollable view of carrier events displaying full delivery status history without pagination cuts.
    - **Workflow Automation** — Permission-gated lifecycle (Pending → In Progress → Carrier Contacted → Resolved) with automated identifications for stalled packages.
    - **Premium Aesthetics** — Optimized with hidden global scrollbars, glassmorphism headers, responsive padding constraints, and smooth transitions for a state-of-the-art native app experience.

### Advanced Dashboard

- **Business Intelligence** — Real-time stat cards for Revenue, Orders, Diamond Sales, and Lead conversion
- **Custom Date Filters** — Filter all dashboard metrics by specific date ranges
- **Smart Alerts** — Automated notifications for overdue orders, package returns, and SLA breaches
- **Activity Feed** — Live stream of administrative actions and system notifications
- **Global Toast Notifications** — Modern, high-performance "Sonner-style" toast system (using Toastify-js) providing instant feedback for CRUD actions, errors, and real-time alerts across the entire platform. Includes custom styling for success, error, and warning states with a premium desktop-like aesthetic.

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
    DashboardController.php       # Analytics, date filters, alerts
    ChatController.php            # All chat API endpoints (Typing, Mentions)
    JewelleryStockController.php  # Jewellery inventory & pricing matrix
    OrderController.php           # Order CRUD, dynamic form partials
    MeleeDiamondController.php    # Small diamond inventory management
    GoldTrackingController.php    # Gold stock and purity tracking
    InvoiceController.php         # Multi-currency invoice generation
    LeadController.php            # Lead CRM and SLA tracking
    ExpenseController.php         # Business expense management
  Models/
    Admin.php                     # Custom authenticatable, permission cache
    Channel.php                   # Chat channels with soft deletes
    Message.php                   # Chat messages with Scout search
    JewelleryStock.php            # Jewellery items with pricing variants
    MeleeDiamond.php              # Melee inventory records
    Order.php                     # Orders with JSON image/pdf columns
    Lead.php                      # CRM leads with SLA timestamps
    Invoice.php                   # Financial records with region symbols
  Services/
    CloudinaryUploadService.php   # Image hosting integration
    JewelleryPricingService.php   # Pricing matrix logic
    JewelleryMaterialRateService.php # Real-time rate fetching
    MeleeStockService.php         # Atomic melee stock operations (deduct, return, diff)
    VirusScanner.php              # ClamAV wrapper with fallback
  Console/Commands/
    RepairMeleeStock.php          # One-time stock repair tool (php artisan melee:repair)
resources/
  js/
    components/Chat.vue           # Optimized Chat SPA with Typing indicators
    bootstrap.js                  # Echo setup, global 401/403 handling
routes/
  web.php                         # All admin routes with permission middleware
  api.php                         # API endpoints for Chat and dynamic lookups
  channels.php                    # Broadcast channel authorization
database/
  migrations/                     # 40+ migrations including stock and lead tables
  seeders/
    PermissionSeeder.php          # Seeds all permission slugs
    SuperAdminSeeder.php          # Bootstrap super admin from env
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
# Set GOD_ADMIN_EMAIL

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

| Variable                    | Purpose                                       | Default                    |
| --------------------------- | --------------------------------------------- | -------------------------- |
| `DB_*`                    | Database connection                           | —                         |
| `BROADCAST_CONNECTION`    | Broadcasting driver (`pusher`, `log`)     | `log`                    |
| `PUSHER_*`                | Pusher credentials                            | —                         |
| `QUEUE_CONNECTION`        | Queue driver                                  | `database`               |
| `CLOUDINARY_URL`          | Cloudinary image hosting URL                  | —                         |
| `META_APP_SECRET`         | Meta API secret for WhatsApp/Webhooks         | —                         |
| `GMAIL_CLIENT_ID`         | Google API client ID for GMail integration    | —                         |
| `JEWELLERY_PLATINUM_RATE` | Overrides dynamic platinum pricing (per gram) | —                         |
| `VGL_API_KEY`             | VGL Certificate system API key                | —                         |
| `CHAT_MAX_UPLOAD_MB`      | Max attachment size per file                  | `10`                     |
| `SESSION_SECURE_COOKIE`   | Require HTTPS for session cookie              | `true`                   |

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

## Maintenance Commands

### Inventory & Stock Management
| Command | Purpose |
|---------|----------|
| `php artisan melee:audit` | **Read-only** daily audit (scheduled 06:00). Reports drift, duplicates, zero-price lots, empty categories, and recent activity. |
| `php artisan melee:repair` | Clean duplicate transactions and recalibrate melee stock levels. |
| `php artisan melee:repair --dry-run` | Preview stock repairs without applying changes. |
| `php artisan melee:recalculate` | Re-sum all transactions to fix drift in `available_weight`. |
| `php artisan diamonds:recalculate-profits` | Recalculate profit margins for all diamond stock based on latest purchase/sell data. |
| `php artisan diamonds:sync-duration` | Update the `days_in_stock` metric for all active inventory. |

### Logistics & Orders
| Command | Purpose |
|---------|----------|
| `php artisan orders:sync-tracking` | Sync tracking status for a specific order via 17Track/Meta. |
| `php artisan tracking:sync-all` | Batch sync tracking for all orders in 'shipped' status. |
| `php artisan orders:reminders` | Send automated WhatsApp/Email reminders for pending client actions. |
| `php artisan clients:sync` | Refresh client metadata and order history counts. |
| Order history timeline | Uses `audit_logs` as the single source of truth for order-status, shipping, and tracking activity history. |

### Financials & Sales
| Command | Purpose |
|---------|----------|
| `php artisan sync:purchase-expenses` | Ensure every Diamond Purchase has a matching Expense record. |
| `php artisan archive:daily-sales` | Aggregate daily totals into the `sales_archives` table for dashboard performance. |

### Security & System
| Command | Purpose |
|---------|----------|
| `php artisan device:approve {email}` | Emergency: Generate a trust token for an admin locked out by IP/Device restriction. |
| `php artisan ip:reset` | Clear all IP restriction logs (useful during office network migrations). |
| `graphify update .` | Update the local knowledge graph (Graphify) after major code changes. |

### Standard Lifecycle
| Command | Purpose |
|---------|----------|
| `composer install` | Install PHP dependencies. |
| `npm install` | Install Node.js dependencies. |
| `php artisan migrate --seed` | Refresh database schema and seed essential data. |
| `php artisan storage:link` | Create symbolic link for public file access (Required for images). |
| `php artisan queue:work` | Start the background worker for chat, notifications, and PDF processing. |
| `php artisan test` | Run the full Pest test suite. |
| `npm run build` | Compile production assets (Vite). |

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
