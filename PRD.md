# Product Requirements Document (PRD)

**Project:** Meele CRM — Diamond & Jewelry Business Management System
**Version:** 1.0
**Date:** February 25, 2026
**Author:** Full Codebase Analysis
**Scalability Target:** 100,000+ concurrent users
Assume this project will handle 100,000+ users. Evaluate scalability accordingly.
---------------------------------------------------------------------------------

## 1. Executive Summary

Meele CRM is a **proprietary, full-featured business management system** purpose-built for the diamond and jewelry trading industry. It replaces fragmented workflow tools with a unified, permission-controlled admin panel that manages the complete business lifecycle — from stone-level inventory tracking and procurement through multi-currency invoicing, sales analytics, lead management, and team collaboration.

The system is a monolithic Laravel 12 application with a Blade + Vue.js 3 hybrid frontend, MySQL database, Pusher-based real-time communication, and integrations with Google Gmail API, Meta/WhatsApp Business API, Cloudinary CDN, and 17Track shipping API. It currently serves as an internal operations platform with **35+ controllers, 50+ Eloquent models, 200+ route definitions, 90+ database migrations**, and supports **7 currencies across 7 international regions**.

The platform is **not a public-facing SaaS** — it is a closed admin panel with no public registration, designed for internal business operators, sales agents, and management.

---

## 2. Problem Statement

### The Problem

Diamond and jewelry trading businesses rely on a patchwork of disconnected tools — spreadsheets for inventory, separate email clients for communication, manual invoice generation, fragmented lead tracking across WhatsApp/Facebook/Instagram, and no centralized view of sales performance or procurement status. This results in:

- **Inventory errors**: Duplicate entries, stale availability, no real-time SKU status linking inventory to orders
- **Revenue leakage**: Missed follow-ups on leads, no SLA enforcement, no sales target visibility
- **Operational inefficiency**: Manual invoice generation per region, no auto-tracking of shipments, no draft recovery for in-progress orders
- **Compliance risk**: No audit trail for email communications, financial transactions, or permission changes
- **Communication silos**: Teams switch between Gmail, WhatsApp, internal chat tools with no unified context

### Who It's For

Internal back-office users of a diamond trading operation — **not end consumers**. Users include operations managers, sales executives, procurement staff, accountants, and super administrators.

---

## 3. Goals & Success Metrics

### Business Goals

| Goal                                | Description                                                                                                                             |
| ----------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| **Operational Consolidation** | Replace 5+ separate tools (spreadsheets, email clients, WhatsApp Web, accounting software, shipping trackers) with one unified platform |
| **Revenue Visibility**        | Real-time company-level sales dashboards with monthly targets, projections, and trend analysis                                          |
| **Compliance Readiness**      | Full audit logging for email, financial, and administrative actions                                                                     |
| **Process Automation**        | Automated shipping sync, draft reminders, overdue order alerts, lead scoring, expense linking                                           |
| **Multi-Region Operations**   | Support 7 international regions with correct currency, tax, and invoice formatting                                                      |

### User Goals

| User                         | Goal                                                                                              |
| ---------------------------- | ------------------------------------------------------------------------------------------------- |
| **Super Admin**        | Full system control: user management, permission assignment, security settings, all module access |
| **Sales Agent**        | Create/manage orders, track diamonds, view personal assigned inventory, communicate with leads    |
| **Operations Manager** | Monitor sales dashboards, review purchases, manage expenses, oversee shipping                     |
| **Accountant**         | Generate invoices, track expenses, export financial reports                                       |

### KPIs / Success Metrics

| Metric                      | Target                              | Measurement                                |
| --------------------------- | ----------------------------------- | ------------------------------------------ |
| Order Processing Time       | < 5 min per order                   | Time from create to submit                 |
| Invoice Generation Accuracy | 100% correct tax by region          | Manual audit sampling                      |
| Lead Response SLA           | < 24 hours first response           | `sla_deadline` field compliance          |
| Inventory Accuracy          | 99.5%+ SKU availability correctness | Sold-out sync delta                        |
| System Uptime               | 99.9%                               | Server monitoring (target for 100K+ users) |
| Page Load Time (P95)        | < 2 seconds                         | APM monitoring                             |
| Concurrent User Support     | 100,000+                            | Load testing under target architecture     |

---

## 4. Target Audience / User Personas

### Primary Personas

#### Persona 1: Super Administrator ("Ashish")

- **Role**: Business owner / CTO
- **Needs**: Full visibility into all operations, user/permission management, security controls, sales analytics across all companies
- **Pain Points**: Needs to grant/revoke granular access without disrupting operations
- **Access Level**: All modules, all permissions, IP whitelist management

#### Persona 2: Sales Executive ("Priya")

- **Role**: Front-line sales agent handling client inquiries and orders
- **Needs**: Quick order creation, diamond SKU lookup, client history, lead follow-ups via WhatsApp, draft auto-save
- **Pain Points**: Losing in-progress order data, not knowing which diamonds are available, missing lead follow-ups
- **Access Level**: Orders, Diamonds (view/assign), Leads, Chat, Clients

#### Persona 3: Operations Manager ("Raj")

- **Role**: Oversees procurement, shipping, factory distribution
- **Needs**: Purchase tracking, gold distribution, shipping status, expense reports, package management
- **Pain Points**: Manual tracking of shipped packages, no visibility into procurement-to-expense linkage
- **Access Level**: Purchases, Expenses, Gold Tracking, Factories, Packages, Shipping

#### Persona 4: Accountant ("Neha")

- **Role**: Financial record keeper
- **Needs**: Multi-region invoice generation, expense categorization, monthly/annual reports, CSV/PDF export
- **Pain Points**: Manual currency conversion, inconsistent tax calculations across regions
- **Access Level**: Invoices, Expenses (reports), Companies (sales dashboards, export)

### Secondary Personas

#### Persona 5: New Team Member (Onboarding)

- **Role**: Recently hired with limited initial access
- **Needs**: Restricted view, gradual permission expansion
- **Access Level**: Dashboards, chat only — expanded as they train

---

## 5. Scope

### In Scope

- Diamond inventory management (CRUD, import/export, bulk edit, assignment, restocking, barcode generation)
- Melee diamond tracking (category-based, weighted average cost per carat, stock in/out transactions)
- Gold tracking (purchases, factory distribution, returns)
- Order pipeline (rough, polished, jewelry types; multi-status; cancellation workflow; draft auto-save)
- Client management (auto-synced from orders, historical spend, export)
- Multi-region invoice generation (7 currencies, tax calculations, PDF export, amount-in-words)
- Company profiles with sales dashboards (monthly targets, projections, CSV/PDF export)
- Lead management (Kanban board, scoring, SLA, WhatsApp/Meta integration, assignment, analytics)
- Team chat (real-time via Pusher; channels, threads, file sharing, mentions, unread counts)
- Gmail integration (OAuth2, inbox sync, compose/reply/forward, attachments, audit logging)
- Meta/WhatsApp integration (webhook-based lead capture, direct reply)
- Shipping tracking (17Track API, webhook-based live status updates)
- Purchase/expense tracking (procurement workflow, expense auto-linking)
- Package handover management (issue/return tracking with overdue detection)
- Notifications (in-app bell alerts for 15+ event types)
- Role-based permission system (50+ granular permissions, super admin override)
- IP security (whitelist, access logging, GeoIP, access request workflow)
- Master data management (metal types, stone types, shapes, colors, cuts, clarities, ring sizes, setting types, closure types)
- Jewellery calculator (live gold rate fetching, cost calculation)
- Audit logging (admin actions, email compliance, financial tracking)
- Excel import/export with error reporting
- Background job processing (diamond import/export, Meta webhooks, lead scoring)

### Out of Scope

- **Public-facing storefront / e-commerce**: No customer self-service portal
- **Mobile native applications**: Web-only (responsive but no iOS/Android apps)
- **Multi-tenancy**: Single-tenant architecture; no tenant isolation for multiple businesses
- **Payment gateway integration**: No online payment processing (offline B2B transactions)
- **Automated marketing campaigns**: No email drip campaigns or marketing automation
- **AI/ML-based diamond grading or pricing**: Manual entry with formula-based calculations
- **Third-party CRM integrations** (Salesforce, HubSpot): Standalone system
- **i18n / Localization**: English-only UI (multi-currency but not multi-language)
- **Comprehensive automated test suite**: Pest framework present but test coverage is minimal

---

## 6. Features & Requirements

### 6.1 Diamond Inventory Management

**Description**: Full-lifecycle diamond tracking from procurement to sale, including SKU generation, barcode imaging, assignment to sales agents, bulk operations, and restocking of sold stones.

**User Story**: *"As a sales executive, I want to search diamonds by SKU and see real-time availability so that I don't promise sold-out stones to clients."*

**Acceptance Criteria**:

- Diamond CRUD with 40+ attributes (lot no, SKU, material, cut, clarity, color, shape, weight, prices, etc.)
- Auto-recalculation of derived fields (profit, duration price, sold status) on every save
- Barcode generation per diamond (Picqer library)
- Multi-image upload via Cloudinary
- SKU availability check endpoint for real-time order validation
- Excel import with error reporting (failed rows downloadable)
- Excel export (background job for large datasets)
- Bulk edit (multi-select, batch update fields)
- Admin assignment (many-to-many) with assignment notifications
- Restock sold diamonds (rate-limited: 30/min)
- Background job tracking for import/export operations
- Soft deletes for data recovery
- Full-text search via Laravel Scout + TNTSearch

**Priority**: P0
**Status**: ✅ Implemented

**Scalability Concerns at 100K+ Users**:

- ⚠️ **TNTSearch is file-based** — will not scale beyond a single server. Must migrate to **Elasticsearch**, **Meilisearch**, or **Algolia** for distributed full-text search.
- ⚠️ **Excel import/export** uses `maatwebsite/excel` which loads data into memory. For datasets exceeding ~50K rows, this will OOM. Needs **chunked processing** or streaming exports.
- ⚠️ **Barcode image generation** happens synchronously — should be deferred to a queue job at scale.
- ⚠️ **`recalculateDerivedFields()`** on every save adds CPU overhead. At scale, consider **event-driven recalculation** via queued jobs.

---

### 6.2 Melee Diamond Inventory

**Description**: Category-based tracking of small (melee) diamonds with weighted average cost per carat, stock-in/stock-out transaction history, and shape/size management.

**User Story**: *"As an operations manager, I want to track melee diamond stock levels by category and shape so that I know when to reorder."*

**Acceptance Criteria**:

- Category management (add/delete categories)
- Shape + size tracking per category
- Stock IN/OUT transactions with history
- Weighted average cost per carat calculation
- Transaction editing and deletion
- Search functionality
- Low stock notifications

**Priority**: P0
**Status**: ✅ Implemented

---

### 6.3 Gold Tracking Module

**Description**: Track gold purchases from suppliers, distribution to factories for manufacturing, and returns from factories — with full expense auto-linking.

**User Story**: *"As a procurement manager, I want to track gold distributed to each factory and how much was returned so I can calculate loss/wastage."*

**Acceptance Criteria**:

- Gold purchase CRUD (weight, rate, supplier, payment mode, party linkage)
- Auto-calculation of total amount (weight × rate)
- Factory distribution tracking
- Gold return tracking
- Completion workflow (mark purchase as completed)
- Invoice image uploads
- Expense auto-linking (gold_purchase_id on expenses)
- Party association for vendor tracking
- Soft deletes

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.4 Order Pipeline

**Description**: Multi-type order management supporting rough diamond, polished diamond, and jewelry orders with a comprehensive status pipeline, cancellation workflow, draft auto-save, and shipping integration.

**User Story**: *"As a sales executive, I want to create an order for a client, attach multiple diamond SKUs, auto-verify their availability, and save drafts so I don't lose work if interrupted."*

**Acceptance Criteria**:

- Order creation for 3 types: rough, polished, jewelry
- Dynamic form loading per order type (`loadFormPartial`)
- Client association (auto-complete search from client database)
- Multiple diamond SKU support with individual pricing
- Real-time SKU availability validation
- File attachments (images, PDFs)
- Company association for invoicing
- Status pipeline:
  - Rough: `r_order_submitted` → `r_order_accepted` → `r_polishing` → `r_polished` → `r_dispatched` → `r_shipped` → `r_delivered` → `r_order_cancelled`
  - Diamond: `d_order_submitted` → ... → `d_delivered` → `d_order_cancelled`
  - Jewelry: `j_order_submitted` → ... → `j_delivered` → `j_order_cancelled`
- Cancellation workflow with reason tracking, cancelled_by, cancelled_at
- Quick-view modal for order preview
- Tracking integration (tracking number, URL, carrier, status, history via 17Track)
- Audit logging (last_modified_by, submitted_by)
- Admin notes field
- Overdue order detection and notifications
- Soft deletes

**Priority**: P0
**Status**: ✅ Implemented

**Scalability Concerns at 100K+ Users**:

- ⚠️ **No database indexing strategy documented** for order queries. With millions of orders, queries filtering by `diamond_status`, `company_id`, `client_id`, `created_at` will degrade without composite indexes.
- ⚠️ **SKU availability check** (`checkSkuAvailability`) queries the diamonds table on every keystroke. Needs **Redis caching** or **debounced/batched lookups**.

---

### 6.5 Order Drafts Module

**Description**: Auto-save in-progress orders as drafts with resume, preview, and deletion capabilities. Includes draft completion reminders via notifications.

**User Story**: *"As a sales executive, I want my in-progress order to auto-save so that if I leave the page or lose connection, I can resume from where I left off."*

**Acceptance Criteria**:

- AJAX auto-save (POST to `/orders/drafts/save`)
- Draft listing page
- Draft count badge endpoint
- My-drafts endpoint for notification popup
- Resume editing from draft
- Draft preview
- Draft deletion (UI and AJAX)
- `DraftCompletionReminder` notification

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.6 Client Management

**Description**: Client profiles auto-synced from order data, with historical spend tracking, order history, and export capabilities.

**User Story**: *"As a sales manager, I want to see a client's total historical spend and all past orders in one view so I can tailor my approach."*

**Acceptance Criteria**:

- Client CRUD (name, email, address, mobile, tax_id)
- Auto-sync clients from orders (Artisan command: `SyncClientsFromOrders`)
- Server-side paginated data endpoint (`clients/data`)
- Client search endpoint (for order creation autocomplete)
- Historical spend calculation (sum of non-cancelled order gross_sell)
- Order history per client
- Client export (Excel via `ClientsExport`)
- Created-by admin tracking

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.7 Multi-Region Invoice System

**Description**: Generate PDF invoices across 7 international regions with dynamic tax calculations (IGST, CGST, SGST for India; flat rate for others), amount-in-words, express shipping surcharges, and per-region currency formatting.

**User Story**: *"As an accountant, I want to generate an invoice for a UK client that automatically shows GBP currency, UK tax rules, and prints the amount in words in Pounds and Pence."*

**Acceptance Criteria**:

- Invoice CRUD with region selection (IN, US, UK, EU, CA, AU, AE)
- Line items management (InvoiceItem)
- Dynamic tax calculation:
  - India: IGST or CGST+SGST based on place of supply vs. state code
  - International: Configurable per region
- Currency formatting from centralized `config/currencies.php`
- Amount-in-words generation (currency + cents name from config)
- PDF generation via `barryvdh/laravel-dompdf`
- Express shipping option
- Copy type support (Original/Duplicate/Triplicate)
- Status tracking (done/pending)
- Company bank details on invoice
- Billed-to / Shipped-to party association
- Invoice number generation
- Invoice policy (authorization)
- Soft deletes

**Priority**: P0
**Status**: ✅ Implemented

**Scalability Concerns at 100K+ Users**:

- ⚠️ **PDF generation is synchronous and CPU-intensive**. At scale, DomPDF blocks the request thread. Must be moved to **queued background jobs** with download-when-ready pattern.
- ⚠️ **Currency config is centralized** (good) but **exchange rates are not dynamic** — only symbols and formatting. No live exchange rate API integration exists.

---

### 6.8 Company Management & Sales Dashboards

**Description**: Company profiles with bank details (multi-region), logo uploads, and comprehensive sales analytics including monthly targets, daily tracking, projections, ring charts, and CSV/PDF export.

**User Story**: *"As a business owner, I want to set a monthly sales target for each company and see a visual dashboard showing actual vs. projected performance with exportable reports."*

**Acceptance Criteria**:

- Company CRUD with multi-region bank details (India: IFSC/AD Code; US: ABA routing; International: SWIFT/IBAN)
- Logo upload via Cloudinary
- Per-company sales dashboard:
  - Monthly target setting
  - Month-to-date actual sales
  - Projection calculation
  - Daily sales history
  - Ring/donut chart visualization
  - CSV export, PDF export
- All-company consolidated dashboard (`allSalesDashboard`)
- Global monthly targets (`GlobalMonthlyTarget`)
- Cancellation-excluded sales calculations
- Company daily sales archival (Artisan command: `ArchiveDailySales`)
- Soft deletes

**Priority**: P0
**Status**: ✅ Implemented

---

### 6.9 Lead Management (Kanban CRM)

**Description**: Kanban-style lead pipeline with scoring, SLA enforcement, WhatsApp/Meta integration, assignment, analytics, notes, and bulk operations.

**User Story**: *"As a sales agent, I want to see all my assigned leads on a Kanban board, respond to WhatsApp messages directly from the CRM, and get alerted when an SLA is about to breach."*

**Acceptance Criteria**:

- Kanban board with drag-and-drop status updates
- Lead statuses: `new` → `in_process` → `contacted` → `qualified` → `proposal` → `negotiation` → `completed` → `lost`
- Lead scoring (0-100) based on: contact info, message engagement, recency, priority
- SLA deadline management (configurable default: 24 hours)
- Auto-assignment strategies: round_robin, load_balanced, random
- Admin assignment
- Note-taking per lead
- WhatsApp/Meta direct messaging (rate-limited)
- Lead activity logging (all actions tracked)
- Analytics dashboard (conversion rates, pipeline velocity)
- Bulk actions (multi-select, batch status change, batch assignment)
- Platform tracking (facebook, instagram, whatsapp, manual)
- Tags support (JSON array)
- Soft deletes
- Background job: `UpdateLeadScore`

**Priority**: P0
**Status**: ✅ Implemented

**Scalability Concerns at 100K+ Users**:

- ⚠️ **Lead scoring is calculated per-lead with N+1 query potential** (messages count per lead). At scale, needs **materialized score caching** or **batch recalculation via scheduled jobs**.
- ⚠️ **Meta API rate limiting** is per-account (200 calls/hour). With 100K+ users generating leads, the `SendMetaMessage` job queue needs **proper rate-limiting and backoff**.

---

### 6.10 Team Chat (Real-Time Messaging)

**Description**: Slack-like team messaging with channels, direct messages, threads, file sharing, mentions, unread counts, and real-time delivery via Pusher/Laravel Echo.

**User Story**: *"As a team member, I want to create channels for project teams, send threaded replies, share files, and @mention colleagues who get instant notifications."*

**Acceptance Criteria**:

- Channel management (create, delete, member management)
- Direct messaging (1:1 channels)
- Real-time message delivery via Pusher
- Thread replies on messages
- File attachments with async processing (`ProcessChatAttachment` job)
  - MIME type whitelist (images, text, octet-stream)
  - Size limit configurable (default: 10MB)
- @mention detection with notifications (`ChatMentionNotification`)
- Unread count per channel per user
- Read receipts (mark as read)
- Message search (full-text search with MySQL FULLTEXT index)
- Channel sidebar (members, info)
- Admin list for channel creation
- Rate limiting middleware (`ChatRateLimiter`)
- Permission-gated (`chat.access`)
- Channel membership change events
- Soft deletes on channels

**Priority**: P1
**Status**: ✅ Implemented

**Scalability Concerns at 100K+ Users**:

- ⚠️ **Pusher free/startup plans have connection limits** (100-500 concurrent connections). At 100K+ users, need **self-hosted WebSocket server** (Laravel Reverb, Soketi, or dedicated Pusher enterprise plan).
- ⚠️ **Unread count calculation** queries per-user per-channel on every page load. Needs **Redis-based counter caching** or pre-computed counts.
- ⚠️ **Full-text search via MySQL FULLTEXT** degrades on large tables. Should migrate to dedicated search engine.

---

### 6.11 Gmail Integration Module

**Description**: Enterprise-grade Gmail integration via Google OAuth2 providing shared inbox functionality with compose, reply, forward, attachment handling, per-user read states, and comprehensive audit logging.

**User Story**: *"As a team member, I want to read and respond to company emails directly from the CRM without switching to Gmail, and have all my email actions logged for compliance."*

**Acceptance Criteria**:

- Google OAuth 2.0 flow (redirect → callback → token storage)
- AES-256-GCM encrypted token storage
- Automatic token refresh before expiry
- Token revocation handling
- Incremental sync via Gmail History API (3-minute cron interval)
- Email inbox with pagination (25/page)
- Filter by incoming/outgoing
- Full-text search
- Thread grouping
- Star/unstar, read/unread (per-user states)
- Compose, reply, forward (send via Gmail API)
- Save as draft
- Attachment streaming download (256KB chunks, memory-safe)
- Attachment checksum validation (SHA-256)
- Temporary signed URLs (60-min expiry)
- Role-based access (Owner, Manager, Agent, Auditor)
- Multi-user account sharing with per-user isolation
- Full audit logging (immutable, every action: login, view, send, download, delete)
- Audit export to CSV/JSON
- Soft deletes + restore
- Modular architecture (app/Modules/Email/)

**Priority**: P1
**Status**: ✅ Implemented (95% — pending task scheduler registration)

**Scalability Concerns at 100K+ Users**:

- ⚠️ **Gmail API has per-user quotas** (250 quota units/second). Syncing 100K+ accounts needs **distributed queue workers** with per-account rate limiting.
- ⚠️ **Email storage in MySQL** will grow rapidly. Need **archival strategy** (move old emails to cold storage) and **table partitioning** by date.
- ⚠️ **Cron sync every 3 minutes** across all accounts is an O(N) operation. Must implement **priority-based sync** (active accounts first) and **horizontal scaling** of queue workers.

---

### 6.12 Meta/WhatsApp Integration

**Description**: Webhook-based lead capture from Facebook Messenger and WhatsApp Business, with direct reply capability and message template support.

**User Story**: *"As a sales agent, I want incoming WhatsApp messages to automatically create leads in the CRM so I can respond without leaving the platform."*

**Acceptance Criteria**:

- Meta webhook verification (GET) and event handling (POST)
- Webhook signature verification middleware (`VerifyMetaWebhook`)
- Automatic lead creation from incoming messages
- Meta account management (add, toggle, refresh token, delete)
- Direct reply via Meta Graph API (v18.0)
- Attachment sending support
- Message template management
- Per-account encrypted token storage
- Background processing (`ProcessMetaWebhook` job, `SendMetaMessage` job)
- Meta message logging
- Rate limiting on API calls (`throttle:meta-api`)
- Conversation threading per lead
- New lead message event (`NewLeadMessage`)
- Settings page for configuration

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.13 Shipping Tracking

**Description**: Automated package tracking via 17Track API with webhook-based status updates, carrier auto-detection, and timeline visualization.

**User Story**: *"As an operations staff, I want to sync tracking status for all shipped orders automatically so clients get accurate delivery timelines."*

**Acceptance Criteria**:

- 17Track API integration for live tracking data
- Carrier auto-detection from company name (Aramex, DHL, FedEx, UPS, USPS, India Post)
- Tracking number extraction from URL (fallback)
- Per-order tracking sync
- Bulk sync all orders (`syncAllTracking` route, `SyncAllOrdersTracking` command)
- Per-order manual sync button
- Webhook endpoint for 17Track push notifications
- Tracking history stored as JSON on order
- Last sync timestamp
- Tracking status display on order detail

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.14 Purchase Tracker

**Description**: Diamond and material procurement tracking with completion workflow and automatic expense linking.

**User Story**: *"As a procurement manager, I want to create a purchase record, mark it as complete, and have the corresponding expense automatically created."*

**Acceptance Criteria**:

- Purchase CRUD (date, amount, supplier, payment mode, bank details)
- Status workflow: pending → completed
- Completion action with expense auto-creation
- Party (vendor) association
- Invoice image uploads
- Expense linkage (purchase_id on expenses)
- Sync command (`SyncPurchaseExpenses`)
- Soft deletes

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.15 Office Expense Manager

**Description**: Cash flow tracking with income/expense categorization, monthly/annual reporting, and Excel export.

**User Story**: *"As an accountant, I want to categorize office expenses and generate monthly/annual reports with Excel export."*

**Acceptance Criteria**:

- Expense CRUD (title, category, amount, date, type: income/expense)
- Monthly report view
- Annual report view
- Excel export (monthly and annual)
- Purchase linkage (auto-created from purchase completion)
- Gold purchase linkage
- Invoice image uploads
- Party association
- Permission-gated reports (`expenses.reports`)
- Soft deletes

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.16 Factory Management

**Description**: Factory profiles for gold distribution and return tracking.

**User Story**: *"As an operations manager, I want to manage factory profiles so I can track gold distribution and returns per factory."*

**Acceptance Criteria**:

- Factory CRUD (name, contact, address, etc.)
- Association with gold distributions
- Permission-gated

**Priority**: P2
**Status**: ✅ Implemented

---

### 6.17 Package Handover & Return System

**Description**: Track physical packages issued to persons with return date enforcement and overdue detection.

**User Story**: *"As a front-desk admin, I want to log when a package is handed over to someone, set a return date, and get alerted when it's overdue."*

**Acceptance Criteria**:

- Package CRUD (slip_id, person, mobile, description, image, dates)
- Status pipeline: Issued → Returned (auto-detects Overdue)
- Return action with actual return date/time
- Overdue detection (return_date < today AND status = Issued)
- Permission-gated (view, create, return, delete)
- Notifications (`PackageIssuedNotification`)
- Soft deletes

**Priority**: P2
**Status**: ✅ Implemented

---

### 6.18 Jewellery Calculator

**Description**: Live gold rate fetching from external API and custom jewelry cost calculation tool.

**User Story**: *"As a sales agent, I want to calculate the real-time cost of a gold jewelry piece using live gold rates so I can give accurate quotes to clients."*

**Acceptance Criteria**:

- Live gold rate fetch from Navkar Gold API (HTTPS XML feed)
- Calculator UI for custom jewelry pricing
- Rate caching to reduce API calls

**Priority**: P2
**Status**: ✅ Implemented

---

### 6.19 Notification System

**Description**: In-app bell notifications for 15+ event types including order lifecycle events, diamond assignments, draft reminders, low stock alerts, and chat mentions.

**User Story**: *"As a sales agent, I want to see a bell icon with unread count and get notified when I'm assigned a diamond, an order is overdue, or someone mentions me in chat."*

**Acceptance Criteria**:

- Notification bell with unread count
- Mark as read (individual and bulk)
- Notification deletion
- 15+ notification types:
  - `OrderCreatedNotification`
  - `OrderUpdatedNotification`
  - `OrderCancelledNotification`
  - `OverdueOrderNotification`
  - `OrderProductivityReminder`
  - `DiamondAssignedNotification`
  - `DiamondReassignedNotification`
  - `DiamondSoldNotification`
  - `DraftCompletionReminder`
  - `MeleeLowStockNotification`
  - `ChatMentionNotification`
  - `ChannelAddedNotification`
  - `PackageIssuedNotification`
  - `ImportCompleted`
  - `ExportCompleted`

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.20 Permission & Admin Management

**Description**: Granular role-based permission system with 50+ permission slugs, super admin bypass, per-route middleware enforcement, and admin CRUD with document uploads.

**User Story**: *"As a super admin, I want to assign specific module permissions to each team member so that they can only access what they need."*

**Acceptance Criteria**:

- Admin CRUD (name, email, password, phone, address, country, state, city, pincode)
- Document uploads (Aadhar front/back, bank passbook)
- Super admin flag (bypasses all permission checks)
- Custom permission system (not Spatie — homegrown `admin_permission` pivot table)
- 50+ permission slugs organized by category
- Permission assignment page per admin
- Permission caching (`cachedPermissionSlugs()` with Cache facade)
- `EnsureAdminHasPermission` middleware on every sensitive route
- Graceful redirect to dashboard on permission denial (with flash message)
- Throttled login (`throttle:login`)
- Session-based authentication (database driver)

**Priority**: P0
**Status**: ✅ Implemented

**Scalability Concerns at 100K+ Users**:

- ⚠️ **Permission caching per-admin** uses the default cache driver. At 100K+ admins, cache invalidation storms on permission updates could be problematic. Need **dedicated Redis cache** with TTL-based expiry.
- ⚠️ **Session storage in database** — at 100K+ concurrent sessions, the `sessions` table becomes a bottleneck. Must migrate to **Redis-based sessions**.
- ⚠️ **Custom permission system lacks role grouping** — assigning 50+ permissions individually per admin doesn't scale organizationally. Should add **role templates** (e.g., "Sales Agent" role with pre-configured permissions).

---

### 6.21 IP Security System

**Description**: IP whitelist-based access restriction with GeoIP logging, access request workflow, and security audit dashboard.

**User Story**: *"As a super admin, I want to restrict CRM access to approved IP addresses and review access requests from blocked team members."*

**Acceptance Criteria**:

- Toggle IP restriction on/off (global `AppSetting`)
- IP whitelist management (add, toggle, delete)
- Automatic IP detection (`check-ip` endpoint)
- GeoIP resolution for blocked attempts
- Access request submission (rate-limited: 3 requests per 10 minutes)
- Access request approval/rejection workflow
- IP access logging with user agent + GeoIP data
- Log clearing
- Custom 403 page for blocked IPs
- Excluded paths (login, security settings, webhooks, API, health check)
- Rate-limited logging (max 1 per IP per 5 minutes to prevent log spam)

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.22 Master Data Management

**Description**: CRUD interfaces for all reference/lookup data used across the system.

**User Story**: *"As an admin, I want to manage dropdown options for stone shapes, colors, clarities, cuts, metal types, ring sizes, setting types, and closure types without developer intervention."*

**Acceptance Criteria**:

- Full CRUD for 9 master data entities:
  - Metal Types
  - Setting Types
  - Closure Types
  - Ring Sizes
  - Stone Types
  - Stone Shapes
  - Stone Colors
  - Diamond Clarities
  - Diamond Cuts
- Permission-gated per entity (view/create/edit/delete)
- Used as foreign keys/references in orders, invoices, diamonds

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.23 Vendor/Party Management

**Description**: Vendor and party profile management for procurement, invoicing, and supply chain operations.

**User Story**: *"As a procurement manager, I want to maintain vendor profiles with categories so I can associate them with purchases and invoices."*

**Acceptance Criteria**:

- Party CRUD (name, contact details)
- Category support (added via migration)
- Association with invoices (billed-to, shipped-to)
- Association with purchases and gold purchases
- Permission-gated

**Priority**: P1
**Status**: ✅ Implemented

---

### 6.24 Audit Logging

**Description**: Comprehensive audit trail for administrative actions, email operations, and financial transactions.

**User Story**: *"As a compliance officer, I want an immutable log of all system actions for regulatory audits."*

**Acceptance Criteria**:

- General audit logging (via `AuditService` and `AuditLogger`)
- Email-specific audit logging (immutable `email_audit_logs` table)
- IP + user agent tracking
- Admin observer for admin model changes (`AdminObserver`)
- Export capabilities (CSV/JSON for email audits)
- Retention policies

**Priority**: P1
**Status**: ✅ Implemented (general audit partially; email audit complete)

**Scalability Concerns at 100K+ Users**:

- ⚠️ **Audit logs in MySQL will grow unbounded**. At 100K+ users performing actions daily, the `audit_logs` and `email_audit_logs` tables will contain hundreds of millions of rows. Need **time-series database** (TimescaleDB), **log shipping** (ELK Stack), or **table partitioning + archival** strategy.

---

## 7. Technical Requirements

### 7.1 Tech Stack Overview

| Layer                      | Technology                   | Version                                     |
| -------------------------- | ---------------------------- | ------------------------------------------- |
| **Runtime**          | PHP                          | 8.2+                                        |
| **Framework**        | Laravel                      | 12.x                                        |
| **Frontend**         | Blade + Vue.js 3             | Hybrid SSR + SPA components                 |
| **CSS Framework**    | TailwindCSS                  | 4.x                                         |
| **Build Tool**       | Vite                         | 7.x                                         |
| **Database**         | MySQL                        | 8.0+ (assumed)                              |
| **Queue**            | Laravel Queue                | Database driver                             |
| **Cache**            | Laravel Cache                | File/database (default)                     |
| **Sessions**         | Laravel Session              | Database driver                             |
| **Real-time**        | Pusher + Laravel Echo        | Pusher v7, Echo v2                          |
| **Search**           | Laravel Scout + TNTSearch    | Scout v10, TNTSearch v15                    |
| **PDF**              | barryvdh/laravel-dompdf      | v3.1                                        |
| **Excel**            | maatwebsite/excel            | v3.1                                        |
| **Barcode**          | Picqer/php-barcode-generator | v3.2                                        |
| **Image Upload**     | Cloudinary                   | Via cloudinary-labs/cloudinary-laravel v3.0 |
| **Image Processing** | Intervention/Image           | v3.11                                       |
| **Email API**        | Google API Client            | v2.15                                       |
| **Process Manager**  | PM2                          | v6.x (for queue workers)                    |
| **Testing**          | Pest                         | v3.0                                        |

### 7.2 Architecture Summary

**Pattern**: Monolithic MVC with modular extensions
**Authentication**: Custom admin guard (session-based, database driver)
**Authorization**: Homegrown permission system (pivot table, cached slugs, middleware enforcement)
**File Storage**: Mixed — `public_path()` direct uploads (needs migration to Storage facade) + Cloudinary for images
**Queue Driver**: Database (MySQL `jobs` table)
**Deployment**: Inferred single-server (PM2 for queue workers, Artisan scheduler for cron)

### 7.3 APIs and Integrations

| Integration                    | Direction          | Protocol     | Purpose                             |
| ------------------------------ | ------------------ | ------------ | ----------------------------------- |
| **Google Gmail API**     | Bidirectional      | REST/OAuth2  | Email sync, compose, reply, forward |
| **Meta Graph API v18.0** | Bidirectional      | REST/Webhook | WhatsApp/FB lead capture, reply     |
| **17Track API**          | Bidirectional      | REST/Webhook | Shipping tracking sync              |
| **Pusher**               | Outbound/WebSocket | WebSocket    | Real-time chat, notifications       |
| **Cloudinary**           | Outbound           | REST         | Image upload/storage                |
| **Navkar Gold API**      | Inbound            | XML/HTTPS    | Live gold rate fetching             |

### 7.4 Performance Requirements (at 100K+ Users)

| Metric                            | Current State                                | Required for 100K+                                          |
| --------------------------------- | -------------------------------------------- | ----------------------------------------------------------- |
| **Concurrent Connections**  | Pusher (limited)                             | Self-hosted WebSocket (Reverb/Soketi) or enterprise Pusher  |
| **Database Queries/sec**    | Unoptimized (likely N+1 in many controllers) | < 50ms avg query time; connection pooling                   |
| **Queue Throughput**        | Single database worker                       | Redis queue + multiple Horizon workers                      |
| **Session Storage**         | MySQL `sessions` table                     | Redis sessions                                              |
| **Full-text Search**        | TNTSearch (file-based)                       | Elasticsearch/Meilisearch cluster                           |
| **Cache Layer**             | File/database cache                          | Dedicated Redis cluster                                     |
| **File Storage**            | Local `public_path()` + Cloudinary         | S3/CDN for all files                                        |
| **PDF Generation**          | Synchronous DomPDF                           | Async queue-based generation                                |
| **API Response Time (P95)** | Unknown                                      | < 500ms                                                     |
| **Database Size**           | Unknown                                      | Table partitioning for orders, diamonds, audit logs, emails |

### 7.5 Security Requirements

| Requirement                       | Current State                                             | Status                                                       |
| --------------------------------- | --------------------------------------------------------- | ------------------------------------------------------------ |
| **Authentication**          | Custom admin guard, throttled login                       | ✅ Implemented                                               |
| **Authorization**           | Per-route permission middleware                           | ✅ Implemented                                               |
| **Session Security**        | Database sessions, configurable lifetime                  | ⚠️ Needs `same-site`, `secure`, `httpOnly` hardening |
| **CSRF Protection**         | Laravel default (excluded for webhooks)                   | ✅ Implemented                                               |
| **IP Restriction**          | Whitelist + GeoIP + access requests                       | ✅ Implemented                                               |
| **Token Encryption**        | AES-256-GCM for Gmail/Meta tokens                         | ✅ Implemented                                               |
| **Content Security Policy** | CSP middleware exists                                     | ✅ Implemented                                               |
| **Input Validation**        | Present but completeness unverified                       | ⚠️ Needs audit                                             |
| **File Upload Security**    | MIME whitelist for chat; direct `public_path` elsewhere | ⚠️ Inconsistent — needs Storage facade migration          |
| **Rate Limiting**           | Login, imports, exports, restocks, Meta API               | ✅ Implemented                                               |
| **Audit Logging**           | Email audit complete; general audit partial               | ⚠️ Needs expansion                                         |
| **SQL Injection**           | Laravel Eloquent ORM (parameterized)                      | ✅ Protected                                                 |
| **XSS Prevention**          | Blade escaping + DOMPurify (frontend)                     | ✅ Protected                                                 |
| **Virus Scanning**          | `VirusScanner` service exists                           | ⚠️ Status unknown                                          |

---

## 8. Non-Functional Requirements

### 8.1 Scalability (Critical — 100K+ User Target)

The current architecture is **single-server monolithic** and will **not scale to 100K+ concurrent users** without significant infrastructure changes:

#### Database Layer

| Issue                    | Impact                                    | Recommendation                                                                             |
| ------------------------ | ----------------------------------------- | ------------------------------------------------------------------------------------------ |
| MySQL as sole datastore  | Write bottleneck, session contention      | **Read replicas** for reporting queries; **Redis** for sessions, cache, queues |
| No table partitioning    | Audit logs, orders, emails grow unbounded | **Partition by date** for audit_logs, emails; **archival** to cold storage     |
| Database-backed queue    | Queue table contention under load         | Migrate to**Redis queue with Laravel Horizon**                                       |
| Database-backed sessions | Session table lock contention             | Migrate to**Redis sessions**                                                         |
| No connection pooling    | Connection exhaustion at scale            | Use**PgBouncer** (if migrating to Postgres) or MySQL connection pooling              |

#### Application Layer

| Issue                                  | Impact                                | Recommendation                                                                                                                    |
| -------------------------------------- | ------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| Monolithic deployment                  | Cannot scale modules independently    | Extract chat, email, lead scoring into**microservices** or use **horizontal scaling** behind load balancer            |
| Synchronous PDF generation             | Blocks request threads                | Move to**queued generation** with download notification                                                                     |
| TNTSearch (file-based)                 | Single-server limitation              | Migrate to**Meilisearch** or **Elasticsearch**                                                                        |
| No caching strategy                    | Redundant DB queries on every request | Implement**Redis caching** for: static data (master tables), permission slugs, dashboard aggregations, diamond availability |
| Direct file uploads to `public_path` | Not CDN-friendly, disk I/O bottleneck | Migrate all uploads to**S3 + CloudFront** or Cloudinary                                                                     |

#### Real-time Layer

| Issue                              | Impact                                 | Recommendation                                             |
| ---------------------------------- | -------------------------------------- | ---------------------------------------------------------- |
| Pusher dependency                  | Connection limits, cost at scale       | Self-host with**Laravel Reverb** or **Soketi** |
| No WebSocket connection management | Memory leaks, no graceful reconnection | Implement connection pooling and heartbeat                 |

#### Infrastructure

| Recommendation                                                 | Priority |
| -------------------------------------------------------------- | -------- |
| **Load Balancer** (NGINX/AWS ALB) + multiple app servers | P0       |
| **Redis cluster** (sessions, cache, queue)               | P0       |
| **MySQL read replicas** for reporting                    | P1       |
| **CDN** (CloudFront/Cloudflare) for static assets        | P1       |
| **Laravel Horizon** for queue monitoring and scaling     | P0       |
| **Container orchestration** (Docker + K8s or ECS)        | P1       |
| **APM** (New Relic, Datadog) for performance monitoring  | P0       |
| **Log aggregation** (ELK Stack or CloudWatch)            | P1       |

### 8.2 Accessibility

| Requirement               | Status                                                               |
| ------------------------- | -------------------------------------------------------------------- |
| Keyboard navigation       | ⚠️ Not verified — TODO mentions "accessibility attributes" needed |
| Screen reader support     | ⚠️ Not verified                                                    |
| Color contrast compliance | ⚠️ Not verified                                                    |
| ARIA labels               | ⚠️ TODO item exists                                                |
| Responsive design         | ⚠️ Partially — TODO mentions "responsive tweaks" needed           |

### 8.3 Compliance / Legal

| Requirement                                         | Status                                                |
| --------------------------------------------------- | ----------------------------------------------------- |
| GDPR compliance (data portability, right to delete) | ⚠️ Soft deletes exist but no explicit GDPR workflow |
| Financial audit readiness                           | ✅ Email audit logs, expense tracking                 |
| Data encryption at rest (tokens)                    | ✅ AES-256-GCM for OAuth tokens                       |
| Data retention policies                             | ⚠️ No documented retention/purge schedule           |
| Backup strategy                                     | ⚠️ Not documented in codebase                       |
| GST/Tax compliance (India)                          | ✅ IGST/CGST/SGST calculation in invoices             |

---

## 9. User Flows

### 9.1 Diamond Lifecycle Flow

```
Admin creates diamond (manual or Excel import)
  → System generates barcode + SKU
  → Diamond enters "IN Stock" status
  → Super admin assigns diamond to sales agent
  → Agent receives DiamondAssignedNotification
  → Agent creates order referencing diamond SKU(s)
  → System validates SKU availability in real-time
  → Upon order completion, diamond status → "Sold"
  → System auto-calculates profit, duration, duration_price
  → Diamond can be "restocked" (reverts to IN Stock)
```

### 9.2 Order Creation Flow

```
Sales agent opens Create Order page
  → Selects order type (rough/polished/jewelry)
  → Dynamic form loads via AJAX
  → Searches for client (autocomplete from clients DB)
  → Enters diamond SKU(s) — real-time availability check
  → Fills order details (images, PDFs, notes)
  → System auto-saves draft every N seconds
  → Agent submits order
  → OrderCreatedNotification sent to relevant admins
  → Order enters status pipeline
  → Agent adds tracking number → System syncs via 17Track
  → When dispatched/shipped → status updates flow
  → If cancelled → cancellation reason captured, status updated
```

### 9.3 Invoice Generation Flow

```
Accountant opens Create Invoice page
  → Selects company + invoice region (IN/US/UK/EU/CA/AU/AE)
  → System auto-loads company bank details for region
  → Selects billed-to and shipped-to parties
  → Adds line items
  → System auto-calculates taxes:
    - India: IGST or CGST+SGST based on state code
    - International: Flat/configurable
  → System formats amounts in region currency
  → Accountant saves invoice
  → Generates PDF with amount-in-words
  → Downloads or shares PDF
```

### 9.4 Lead Capture & Follow-up Flow

```
Customer messages company on WhatsApp/Facebook/Instagram
  → Meta webhook fires → Laravel processes async (ProcessMetaWebhook job)
  → System creates or updates Lead
  → Lead appears on Kanban board in "New" column
  → Auto-assignment runs (round_robin/load_balanced/random)
  → Assigned agent receives notification
  → Agent views lead details, reads message history
  → Agent replies directly from CRM (via Meta Graph API)
  → System tracks SLA deadline (24h default)
  → Agent moves lead through pipeline stages
  → Lead score auto-updates based on engagement
  → Analytics dashboard shows conversion metrics
```

### 9.5 Team Communication Flow

```
Admin opens Chat module
  → Sees list of channels (group + direct)
  → Selects or creates a channel
  → Types message → real-time delivery via Pusher
  → Can @mention teammates → ChatMentionNotification sent
  → Can reply in thread (nested discussion)
  → Can attach files (processed async, MIME-validated)
  → Unread badges update in real-time
  → Search across all messages
```

### 9.6 Gold Procurement & Distribution Flow

```
Procurement manager creates Gold Purchase (weight, rate, supplier)
  → System auto-calculates total amount
  → Manager marks as "Completed"
  → System auto-creates linked Expense record
  → Manager distributes gold to Factory (weight allocation)
  → Factory processes gold into jewelry
  → Factory returns remaining/waste gold
  → System tracks net gold in/out per factory
```

---

## 10. Open Questions & Assumptions

### Open Questions

| #  | Question                                                                         | Impact                                                          |
| -- | -------------------------------------------------------------------------------- | --------------------------------------------------------------- |
| 1  | **What is the expected geographical distribution of users?**               | Determines CDN placement, database region, latency requirements |
| 2  | **Are there concurrent multi-company tenants or is this single-business?** | Impacts data isolation strategy and scaling model               |
| 3  | **What are the data retention requirements for audit logs?**               | Determines archival strategy and storage costs                  |
| 4  | **Is there a disaster recovery requirement (RTO/RPO)?**                    | Determines backup frequency, multi-AZ deployment                |
| 5  | **What is the expected ratio of read:write operations?**                   | Determines read replica sizing                                  |
| 6  | **Is Spatie/laravel-permission being used or replaced?**                   | TODO mentions "Integrate or remove" — decision pending         |
| 7  | **Why is `VirusScanner` service present but usage unclear?**             | Is it actively scanning uploads?                                |
| 8  | **What happens when Gmail API quota is exhausted?**                        | Need graceful degradation strategy                              |
| 9  | **Are there offline/intermittent connectivity scenarios?**                 | Would require PWA/service worker consideration                  |
| 10 | **Is the 17Track API key hardcoded in service (`016E04...`)?**           | Security risk — should be env-only                             |

### Assumptions

| # | Assumption                                                                                                            |
| - | --------------------------------------------------------------------------------------------------------------------- |
| 1 | The system is single-tenant (one diamond business) — not a multi-tenant SaaS                                         |
| 2 | "100K+ users" refers to total registered admins/agents, not simultaneous concurrent users (simultaneous likely 1-10K) |
| 3 | The primary deployment target is a cloud VPS (AWS/GCP/Azure) behind a reverse proxy                                   |
| 4 | All financial data is in a prepaid model (order amount counts as revenue at creation, not delivery)                   |
| 5 | The Gmail module is designed to be opt-in per company (not all companies need email integration)                      |
| 6 | The application currently runs on a single server with PM2 managing queue workers                                     |
| 7 | Exchange rates are set manually; there is no live forex rate integration                                              |
| 8 | The `User` (standard Laravel) model is unused; `Admin` is the sole authenticatable entity                         |

---

## 11. Risks & Dependencies

### High-Risk Items

| Risk                                         | Probability | Impact   | Mitigation                                                                             |
| -------------------------------------------- | ----------- | -------- | -------------------------------------------------------------------------------------- |
| **Database bottleneck at scale**       | High        | Critical | Migrate sessions/cache/queue to Redis; add read replicas; implement connection pooling |
| **Pusher connection limits**           | High        | High     | Migrate to self-hosted WebSocket server (Reverb/Soketi)                                |
| **TNTSearch single-server limitation** | High        | High     | Migrate to distributed search engine (Meilisearch/Elasticsearch)                       |
| **Gmail API quota exhaustion**         | Medium      | High     | Implement per-account rate limiting, priority-based sync, quota monitoring             |
| **Meta API rate limits**               | Medium      | Medium   | Backoff strategy, queue throttling                                                     |
| **File storage disk I/O**              | High        | Medium   | Migrate all uploads from `public_path()` to S3/CDN                                   |
| **No automated test coverage**         | High        | High     | Critical path tests needed before refactoring for scale                                |
| **Hardcoded API keys in services**     | Medium      | Critical | Audit all services for env-only credential usage                                       |
| **No health monitoring/APM**           | High        | High     | Cannot detect degradation proactively                                                  |
| **DomPDF memory usage at scale**       | Medium      | Medium   | Queue-based PDF generation                                                             |

### Dependencies

| Dependency                        | Type                          | Risk Level                                      |
| --------------------------------- | ----------------------------- | ----------------------------------------------- |
| **Google Cloud Platform**   | OAuth credentials + Gmail API | Medium (quotas, policy changes)                 |
| **Meta Business Platform**  | WhatsApp/FB API access        | Medium (API deprecation, policy changes)        |
| **Pusher**                  | Real-time messaging           | High (cost scaling, connection limits)          |
| **17Track**                 | Shipping tracking             | Low (degradable — manual tracking as fallback) |
| **Cloudinary**              | Image storage                 | Low (CDN reliability is high)                   |
| **Navkar Gold API**         | Live gold rates               | Low (degradable — cached rates as fallback)    |
| **MySQL**                   | Primary datastore             | Critical (single point of failure without HA)   |
| **Picqer Barcode**          | Barcode generation            | Low (stable library)                            |
| **maatwebsite/excel**       | Import/export                 | Medium (memory limits on large datasets)        |
| **barryvdh/laravel-dompdf** | PDF generation                | Medium (performance at scale)                   |

---

## 12. Timeline & Milestones

Based on the current project state, here is a suggested phased roadmap to reach production-grade at 100K+ user scale:

### Phase 1: Foundation Hardening (Weeks 1-4)

| Milestone                  | Tasks                                                                                                    | Priority |
| -------------------------- | -------------------------------------------------------------------------------------------------------- | -------- |
| **Security Audit**   | Fix hardcoded API keys; migrate uploads to Storage facade; harden session config; audit input validation | P0       |
| **Test Coverage**    | Write critical path tests (auth, orders, diamonds, invoices) using Pest; target 60% coverage             | P0       |
| **TODO Cleanup**     | Address all items in `TODO.md` (Spatie decision, session hardening, responsive fixes)                  | P0       |
| **Monitoring Setup** | Deploy APM (Datadog/New Relic); structured logging; error tracking (Sentry)                              | P0       |

### Phase 2: Infrastructure Scaling (Weeks 5-10)

| Milestone                         | Tasks                                                                          | Priority |
| --------------------------------- | ------------------------------------------------------------------------------ | -------- |
| **Redis Migration**         | Move sessions, cache, and queue from database to Redis; deploy Laravel Horizon | P0       |
| **Search Engine Migration** | Replace TNTSearch with Meilisearch or Elasticsearch                            | P0       |
| **WebSocket Migration**     | Replace Pusher with Laravel Reverb or Soketi for self-hosted real-time         | P0       |
| **File Storage Migration**  | Move all uploads from `public_path()` to S3 with CloudFront CDN              | P1       |
| **Database Optimization**   | Add composite indexes; implement query caching; set up read replica            | P0       |

### Phase 3: Architecture Optimization (Weeks 11-16)

| Milestone                       | Tasks                                                                                     | Priority |
| ------------------------------- | ----------------------------------------------------------------------------------------- | -------- |
| **Async PDF Generation**  | Move invoice PDF generation to queue-based workflow                                       | P1       |
| **Database Partitioning** | Partition audit_logs, emails, orders by date range                                        | P1       |
| **Containerization**      | Dockerize application; create docker-compose for local dev; Kubernetes/ECS for production | P1       |
| **Load Testing**          | Run load tests simulating 100K+ users; identify and fix bottlenecks                       | P0       |
| **Role Templates**        | Add role-based permission templates (Sales Agent, Manager, Accountant)                    | P2       |

### Phase 4: Feature Maturity (Weeks 17-24)

| Milestone                          | Tasks                                                                  | Priority |
| ---------------------------------- | ---------------------------------------------------------------------- | -------- |
| **Accessibility Compliance** | WCAG 2.1 AA compliance audit and fixes                                 | P2       |
| **GDPR Workflow**            | Data export, right to deletion, consent management                     | P2       |
| **Live Exchange Rates**      | Integrate forex rate API for real-time currency conversion             | P2       |
| **Advanced Analytics**       | Business intelligence dashboards, custom report builder                | P2       |
| **Mobile PWA**               | Progressive Web App for field sales agents                             | P2       |
| **API Layer**                | Build RESTful API for potential mobile app or third-party integrations | P2       |

---

## Appendix A: Database Schema Summary

**Total Migrations**: 91
**Total Models**: 50

| Entity Group             | Tables                                                                                              | Key Relationships                              |
| ------------------------ | --------------------------------------------------------------------------------------------------- | ---------------------------------------------- |
| **Auth & Admin**   | admins, permissions, admin_permission, sessions                                                     | Admin ↔ Permission (M:M)                      |
| **Diamonds**       | diamonds, diamond_admin, diamond_clarities, diamond_cuts                                            | Diamond ↔ Admin (M:M)                         |
| **Melee**          | melee_categories, melee_diamonds, melee_transactions                                                | Category → Diamond → Transaction             |
| **Orders**         | orders, order_drafts, clients                                                                       | Order → Client, Company, Admin                |
| **Invoices**       | invoices, invoice_items                                                                             | Invoice → Company, Party (billed/shipped)     |
| **Companies**      | companies, company_monthly_targets, company_daily_sales, global_monthly_targets                     | Company → Orders, Targets                     |
| **Leads**          | leads, lead_activities                                                                              | Lead → Admin, Activities                      |
| **Meta**           | meta_accounts, meta_conversations, meta_messages, meta_message_logs, message_templates              | MetaAccount → Conversations → Messages       |
| **Chat**           | channels, channel_user, messages, message_reads, message_attachments, message_links                 | Channel ↔ Admin (M:M); Channel → Messages    |
| **Gold**           | gold_purchases, gold_distributions                                                                  | GoldPurchase → Expenses, Factory              |
| **Procurement**    | purchases, expenses                                                                                 | Purchase → Expense (auto-link)                |
| **Email**          | email_accounts, email_account_users, emails, email_attachments, email_user_states, email_audit_logs | EmailAccount ↔ Admin (M:M)                    |
| **Master Data**    | metal_types, setting_types, closure_types, ring_sizes, stone_types, stone_shapes, stone_colors      | Referenced by Orders, Diamonds                 |
| **Operations**     | factories, packages                                                                                 | Factory → GoldDistributions; Package → Admin |
| **Security**       | allowed_ips, app_settings, ip_access_logs, ip_access_requests                                       | Standalone                                     |
| **Infrastructure** | users, cache, jobs, job_tracks, notifications, audit_logs                                           | System tables                                  |

---

## Appendix B: Artisan Commands

| Command                    | Purpose                                  | Schedule        |
| -------------------------- | ---------------------------------------- | --------------- |
| `email:sync`             | Sync Gmail inboxes incrementally         | Every 3 minutes |
| `email:verify-integrity` | Verify email attachment checksums        | Daily at 02:00  |
| `SendOrderReminders`     | Notify about overdue/pending orders      | Configurable    |
| `SyncAllOrdersTracking`  | Bulk sync all order tracking via 17Track | Configurable    |
| `SyncClientsFromOrders`  | Create Client records from Order data    | On-demand       |
| `SyncPurchaseExpenses`   | Link purchases to expenses               | On-demand       |
| `ArchiveDailySales`      | Snapshot daily sales to history table    | Daily           |
| `SyncOrderTracking`      | Sync individual order tracking           | On-demand       |
| `IpResetCommand`         | Reset IP restriction settings            | Emergency       |

---

## Appendix C: Background Jobs

| Job                       | Queue   | Purpose                                |
| ------------------------- | ------- | -------------------------------------- |
| `ProcessDiamondImport`  | default | Async Excel import processing          |
| `ProcessDiamondExport`  | default | Async Excel export generation          |
| `ProcessChatAttachment` | default | Async file processing for chat uploads |
| `ProcessMetaWebhook`    | default | Async Meta/WhatsApp webhook processing |
| `SendMetaMessage`       | default | Async Meta Graph API message sending   |
| `UpdateLeadScore`       | default | Async lead score recalculation         |

---

## Appendix D: Key Inconsistencies & Gaps Detected

| #  | Issue                                                                                                                     | Severity | Location                                             |
| -- | ------------------------------------------------------------------------------------------------------------------------- | -------- | ---------------------------------------------------- |
| 1  | **17Track API key appears hardcoded** in `ShippingTrackingService.php` (`'016E049ACA...'` as default)           | Critical | `app/Services/ShippingTrackingService.php`         |
| 2  | **Spatie/laravel-permission listed in TODO** as "integrate or remove" — currently not used; custom system in place | Low      | `TODO.md`                                          |
| 3  | **`public_path()` direct uploads** used in multiple controllers instead of Storage facade                         | Medium   | Multiple controllers                                 |
| 4  | **Task scheduler registration missing** for Gmail sync command                                                      | Medium   | `IMPLEMENTATION_CHECKLIST.md`                      |
| 5  | **README says Laravel 12, BUILD_SUMMARY says Laravel 10/11** — version inconsistency                               | Low      | `README.md` vs `BUILD_SUMMARY.md`                |
| 6  | **No `.env.example`** visible in project root (only `.env.email.example` mentioned)                             | Medium   | Project root                                         |
| 7  | **`User` model exists** but is unused (Admin is the sole authenticatable) — dead code                            | Low      | `app/Models/User.php`                              |
| 8  | **No database seeder** for initial admin user or permissions                                                        | Medium   | `database/seeders/`                                |
| 9  | **Chat MIME whitelist excludes PDF** (commented out in config) — likely intentional but undocumented               | Low      | `config/chat.php`                                  |
| 10 | **No health check endpoint** (`/up` referenced in IP middleware but not defined in routes)                        | Low      | Routes                                               |
| 11 | **Hindi comments** in `EnsureAdminHasPermission` middleware — should be English for team consistency             | Low      | `app/Http/Middleware/EnsureAdminHasPermission.php` |
| 12 | **Debug routes exist** (`/debug-gold`, `/test-blade`, `/test-broadcast`) — must be removed in production     | Medium   | `routes/web.php`                                   |
| 13 | **No CORS configuration** for API routes (config exists but no API routes defined)                                  | Low      | `routes/api.php` is empty                          |
| 14 | **Expense title and category made nullable** (migration `2026_02_24_142238`) — may indicate data quality issue   | Low      | Migrations                                           |
| 15 | **No backup/restore strategy** documented or implemented                                                            | High     | Infrastructure                                       |

---

*This PRD was generated through automated analysis of 91 migrations, 50 models, 37 controllers, 10 service classes, 6 jobs, 6 events, 15 notifications, 6 middleware, 7 Artisan commands, and ~1,000 route definitions. All feature names, routes, and terminology reflect actual codebase artifacts.*
