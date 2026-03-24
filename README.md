# 💎 CRM — Diamond & Jewelry Business Management

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Pusher-Realtime-300D4F?style=for-the-badge&logo=pusher&logoColor=white" />
  <img src="https://img.shields.io/badge/Status-Production-brightgreen?style=for-the-badge" />
</p>

> **A full-featured, production-grade CRM system** built specifically for the diamond and jewelry industry. Manages the complete business lifecycle — from stone-level inventory and factory procurement to multi-currency invoicing, real-time team collaboration, and automated Shopify synchronization.

---

## 🚀 What's New in 2026?

We've recently upgraded the system with several heavy-weight features to improve security, automation, and team efficiency:

- 🛡️ **Adaptive Device Trust** — Replaced static IP whitelisting with a secure, cookie-based session trust system. Includes emergency CLI bypass and GeoIP logging.
- 💍 **Jewellery Stock Module** — Dedicated tracking for finished jewelry items with SKU-based inventory, weight/metal categorization, and automated low-stock alerts.
- 🔔 **Order Discussion Notifications** — Real-time Pusher alerts for specific order threads, featuring sidebar unread count badges for instant team awareness.
- 💬 **Enhanced Chat Experience** — Professional communication tools including Message Reactions, Pinned Threads, and personal Saved Messages for capturing important stone details.
- 📦 **17Track Integration** — Automated shipping timeline extraction with live status updates directly within the order pipeline.

---

## 🏢 Overview

This CRM powers the daily operations of a global diamond trading business. It replaces fragmented spreadsheets with a unified, permission-controlled ecosystem that handles stone-level tracking, factory distributions, and PDF invoice generation across **7 currencies** and **7 international regions**.

---

## ✨ Modules & Capabilities

### 📦 Inventory & Products

- **Diamond Inventory** — Full stone lifecycle: bulk import/export (Excel), SKU-based availability, and admin stone assignment.
- **Melee Diamonds** — Category-based tracking with **Weighted Average Cost (WAC)** per carat.
- **Jewellery Stock** — [NEW] Track finished rings, necklaces, and findings with automated "Low Stock" and "In Stock" status triggers.
- **Gold Tracking** — Factory-wide gold distribution, returns tracking, and purchase logging.
- **Shopify Connected** — Bi-directional sync with automated metafield extraction for stone specs.

### 🛒 Sales & Orders

- **Multi-Pipeline Orders** — Track Rough, Polished, and Jewelry orders with granular status stages and draft auto-save.
- **Shipping Tracker** — Integrated with **17Track API** for live package timelines and delivery confirmation.
- **Order Cancellation** — Robust workflow for cancelled stones with read-only history and reason tracking.
- **Invoices** — Dynamic PDF generation with per-region tax rules, amount-in-words, and multi-currency formatting.

### 🌐 Multi-Currency Engine

Centralized system supporting **7 global currencies** with extensible configuration:
`INR (₹)`, `USD ($)`, `GBP (£)`, `EUR (€)`, `CAD (C$)`, `AUD (A$)`, `AED (د.إ)`

### 👥 CRM & Lead Management

- **Lead Pipeline** — Kanban-style board for lead tracking with assignment analytics and WhatsApp integration.
- **Shoppers & Vendors** — Unified profiles for clients and suppliers with detailed transaction histories.
- **Company Profiles** — Multi-entity support with regional bank details and Cloudinary logo storage.

### 💬 Communication & Teams

- **Real-time Team Chat** — Slack-like experience with channels, threads, file sharing, reactions, and pinned messages.
- **Gmail Integration** — Full OAuth2 inbox (read/reply/forward/remove) integrated directly into the admin panel.
- **WhatsApp API** — Direct lead capture and reply capability via Meta webhooks.

### 🔐 Security & Access [UPDATED]

- **Device Trust Management** — Secure session-based authentication. Manage trusted browsers from the "Security & Device" panel.
- **Granular Permissions** — 50+ permission types controlling View/Create/Edit/Delete access per module.
- **Audit Logs** — Every sensitive stone or price change is logged with the admin's ID and timestamp.

---

## 🏗 Tech Stack

| Layer                    | Technologies                                                             |
| :----------------------- | :----------------------------------------------------------------------- |
| **Backend**        | Laravel 12, PHP 8.2+                                                     |
| **Frontend**       | Vue.js 3, Blade, Vanilla CSS/JS                                          |
| **Build Tools**    | Vite 7, TailwindCSS 4                                                    |
| **Database**       | MySQL (Search: Laravel Scout + TNTSearch)                                |
| **Real-time**      | Pusher (Channels & Notifications)                                        |
| **Images/Storage** | Cloudinary & Local Storage                                               |
| **Integrations**   | Shopify Admin API, 17Track, Google Workspace (OAuth2), Meta/WhatsApp API |

---

## 🛠 Developer & Admin Commands

Use these custom Artisan commands for system maintenance and emergency recovery:

| Command                           | Description                                                               |
| :-------------------------------- | :------------------------------------------------------------------------ |
| `php artisan device:approve`    | **Emergency** grant trust to an admin device via phone/email token. |
| `php artisan ip:reset`          | Flush security whitelists and reset access to default.                    |
| `php artisan melee:recalculate` | Re-sync inventory weights from transaction logs.                          |
| `php artisan orders:remind`     | Batch send notifications for overdue石 (stones) or unpaid invoices.       |
| `php artisan tracking:sync-all` | Trigger a global sync with the 17Track API for all active shipments.      |

---

## 📊 Scale

| Metric            | Count |
| :---------------- | :---- |
| Eloquent Models   | 58+   |
| Controllers       | 41+   |
| Route Definitions | ~250+ |
| Permission Types  | 50+   |
| Supported Regions | 7     |

---

## 📜 License

**Proprietary and Confidential.**
This repository is private. Unauthorized copying, redistribution, or use of this software is strictly prohibited. All rights reserved.

<p align="center">
  Built with ❤️ for the Diamond Industry
</p>
