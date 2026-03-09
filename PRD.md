# Product Requirements Document (PRD)

<div align="center">
  <img src="public/images/Luxurious-Logo.png" width="120" alt="Meele CRM Logo">
  <h1>💎 Meele CRM</h1>
  <p><strong>Diamond & Jewelry Business Management System</strong></p>
  <p>🚀 <em>Scalable, Secure, and Proprietary Internal Operations Platform</em></p>
</div>

---

## 📝 Document Information

| Attribute | Value |
| :--- | :--- |
| **Project** | Meele CRM — v3.0 (March 2026 Edition) |
| **Status** | 🟢 Production Ready |
| **Target Scale** | 100,000+ Concurrent Users |
| **Last Updated** | March 9, 2026 |
| **Author** | System Architecture Review |

> **What's New in v3.0?** 
> *   🛍️ **Shopify Integration**: Bi-directional sync for products, collections, and orders.
> *   🌙 **Adaptive UI**: Full Dark Mode support with system-level theme switching.
> *   📱 **Mobile First**: Complete responsive redesign for field sales agents.
> *   📈 **Consolidated Analytics**: All-company sales dashboard with global targets.
> *   🛡️ **Enhanced Security**: IP-based access control with GeoIP logging in the navbar.
> *   🤖 **Agent Skills**: Integrated AI-agent capabilities for automated workflow management.

---

## 1. Executive Summary

Meele CRM is a **proprietary, end-to-end business management system** engineered specifically for the diamond and jewelry trading industry. It centralizes stone-level inventory tracking, procurement, multi-currency invoicing, sales analytics, lead management, and team collaboration into a single, high-performance admin panel.

### Core Architecture
- **Backend**: Laravel 12 (Monolithic with Modular Extensions)
- **Frontend**: Blade + Vue.js 3 Hybrid (Optimized for P95 speed)
- **Database**: MySQL 8.0+ with Redis caching layer
- **Integrations**: Google Gmail API, Meta/WhatsApp Business API, Shopify Admin API, 17Track, Cloudinary.

---

## 2. Problem Statement

Diamond trading operations traditionally suffer from **"Tool Fragmentation"**:
1.  **Inventory Lag**: Spreadsheets fail to track real-time stone availability.
2.  **Revenue Leaks**: Unmanaged leads across WhatsApp/Instagram lead to missed closures.
3.  **Manual Toil**: Per-region invoicing and shipping tracking consume hours of staff time.
4.  **Security Blindspots**: Internal communications lack audit trails and IP-based restriction.

**The Solution**: A unified "Command Center" that connects every facet of the business—from the factory floor to the global sales desk.

---

## 3. Core Modules & Features

### 🛍️ 3.1 Shopify Integration (New)
*   **Product Sync**: Bi-directional SKU/Barcode matching between CRM and Shopify.
*   **Metafield Mapping**: Automatic extraction of "Metal Purity", "Stone Clarity", and "Carat Weight" from descriptions to Shopify metafields.
*   **Draft Orders**: Automatic creation of Shopify draft products/orders upon CRM sales.
*   **Webhooks**: Real-time listeners for Shopify order and product updates.

### 🌙 3.2 Adaptive UI & UX (New)
*   **Dark Mode**: High-contrast dark theme with CSS variable tokens (`data-theme="dark"`).
*   **Responsive Layout**: Fluid sidebar and grid system optimized for tablets and smartphones.
*   **Interactive Navbar**: Live ticking clock, time-based greeting chips, and quick-access IP security controls.
*   **Visual Flair**: Premium 3-stop gradients with category-specific colored glow shadows.

### 📈 3.3 Sales & Consolidated Analytics
*   **Global Dashboard**: Real-time aggregation of sales across all registered companies.
*   **Target Management**: Monthly goal setting with projected vs. actual progress ring charts.
*   **Automated Snapshots**: Daily sales archival via Artisan scheduler for historical trend analysis.

### 💎 3.4 Diamond & Melee Inventory
*   **Single Stone Tracking**: 40+ attributes per diamond, auto-calculated profits, and Picqer-powered barcodes.
*   **Melee Tracking**: Category-based tracking with weighted average cost per carat.
*   **Bulk Operations**: Background job-powered Excel imports/exports and mass attribute editing.

### 📧 3.5 Enterprise Communication
*   **Gmail Shared Inbox**: Multi-user Google OAuth2 sync with incremental history fetching.
*   **WhatsApp CRM**: Webhook-based lead capture and direct messaging via Meta Graph API.
*   **Real-time Chat**: Slack-like channels, threads, and @mentions via Pusher/Echo.

### 💰 3.6 Financial & Procurement
*   **Multi-Region Invoicing**: Automated tax (GST/IGST) and currency formatting for 7 international regions.
*   **Gold Tracker**: Tracks procurement, factory distribution, and return-wastage metrics.
*   **Expense Manager**: Auto-links procurement actions to office cash flow logs with Cloudinary invoice storage.

---

## 4. Technical Requirements

### 4.1 Tech Stack Detail

| Layer | Technology | Version | Purpose |
| :--- | :--- | :--- | :--- |
| **Framework** | Laravel | 12.x | Core Application Engine |
| **Runtime** | PHP | 8.2+ | Server-side Logic |
| **Frontend** | Vue.js | 3.x | Reactive Components |
| **CSS** | TailwindCSS | 4.x | Utility-first Styling |
| **Real-time** | Pusher | 7.x | WebSocket Messaging |
| **CDN** | Cloudinary | 3.x | Media & Document Hosting |
| **Worker** | PM2 | 6.x | Process & Queue Management |

### 4.2 Scalability Strategy (100K+ Users)
*   **Search**: Move from file-based TNTSearch to **Meilisearch** cluster.
*   **Cache**: Dedicated **Redis** cluster for sessions, permissions, and counters.
*   **PDF**: Transition DomPDF generation to asynchronous **Queue Workers**.
*   **Database**: Implement **Table Partitioning** for `audit_logs` and `emails`.

---

## 5. Security & Compliance

*   **IP Security**: Whitelist-based access with GeoIP logging and automated access request workflows. Quick-access shield in navbar.
*   **Encryption**: AES-256-GCM for all OAuth tokens and sensitive master data.
*   **Audit Trail**: Immutable logging of every administrative action and email interaction.
*   **RBAC**: custom homegrown Permission system with 50+ granular slugs and role caching.

---

## 6. Roadmap & Future Vision

- [ ] **Phase 1**: S3 Migration for all local file storage (Storage Facade).
- [ ] **Phase 2**: Laravel Reverb integration for self-hosted WebSockets.
- [ ] **Phase 3**: AI-powered diamond pricing suggestions based on historical trends.
- [ ] **Phase 4**: Full REST API exposure for native Mobile App support.

---

<div align="right">
  <p><em>Generated by Gemini CLI Agent</em></p>
  <p>March 2026</p>
</div>
