# 💎 CRM — Diamond & Jewelry Business Management

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Pusher-Realtime-300D4F?style=for-the-badge&logo=pusher&logoColor=white" />
  <img src="https://img.shields.io/badge/Status-Production-brightgreen?style=for-the-badge" />
</p>

> **A full-featured, production-grade CRM system** built for the diamond and jewelry industry. Manages the complete business lifecycle — from inventory and procurement to orders, multi-currency invoicing, sales analytics, lead management, and team collaboration.

> ⚠️ **This is a proprietary project.** It is not open for public use, contribution, or redistribution.

---

## 🏢 Overview

This CRM powers the day-to-day operations of a diamond trading business, replacing fragmented tools with a unified, permission-controlled admin panel. It handles everything from stone-level inventory tracking to PDF invoice generation across 7 currencies and 7 international regions.

---

## ✨ Modules & Capabilities

📦 Inventory & Products

- **Diamond Inventory** — Full lifecycle: create, import/export (Excel), bulk edit, SKU-based availability, restock sold stones, admin assignment
- **Melee Diamonds** — Category-based melee tracking with weighted average cost per carat
- **Gold Tracking** — Gold stock management with factory distribution, returns, and purchase logging

### 🛒 Sales & Orders

- **Orders** — Multi-type pipeline (rough, polished, jewelry) with status tracking, draft auto-save, and overdue detection
- **Invoices** — Multi-region PDF invoicing with dynamic tax calculations, amount-in-words, and per-region currency formatting
- **Sales Dashboards** — Company-level analytics with monthly targets, progress tracking, projections, and PDF/CSV export

### 💱 Multi-Currency Engine

Centralized currency system supporting **7 currencies** out of the box — extensible by adding a single config entry:

| Currency               | Symbol | Region |
| ---------------------- | ------ | ------ |
| 🇮🇳 Indian Rupee      | ₹     | IN     |
| 🇺🇸 US Dollar         | $      | US     |
| 🇬🇧 British Pound     | £     | UK     |
| 🇪🇺 Euro              | €     | EU     |
| 🇨🇦 Canadian Dollar   | C$     | CA     |
| 🇦🇺 Australian Dollar | A$     | AU     |
| 🇦🇪 UAE Dirham        | د.إ  | AE     |

### 👥 CRM & Contacts

- **Companies** — Company profiles with bank details, logo uploads (Cloudinary), and sales dashboards
- **Vendors** — Vendor management with categorization for billing and supply chain
- **Shoppers** — Client profiles with associated order/invoice history
- **Lead Management** — Kanban-style pipeline with analytics, assignment, notes, and WhatsApp integration

### 💬 Communication

- **Team Chat** — Real-time messaging via Pusher with channels, threads, file sharing, and unread counts
- **Gmail Integration** — Built-in email inbox with compose, reply, and forward via Google OAuth2
- **WhatsApp/Meta** — Lead capture via Facebook/WhatsApp webhooks with direct reply capability
- **Notifications** — In-app bell alerts for overdue orders, draft reminders, and task nudges

### 🔧 Operations

- **Purchase Tracker** — Diamond and material procurement with completion workflow and expense auto-linking
- **Office Expenses** — Cash flow tracking with income/expense categorization
- **Master Data** — CRUDs for Metal Types, Stone Types, Shapes, Colors, Cuts, Clarities, Ring Sizes, Setting Types, Closure Types

### 🔐 Security & Access

- **Role-based Permissions** — Granular per-module control (view/create/edit/delete) with super admin override
- **Admin-only Auth** — No public registration; admin panel with throttled login
- **Permission Middleware** — Applied per-route for fine-grained access control

---

## 🏗 Tech Stack

| Layer                         | Technologies                    |
| ----------------------------- | ------------------------------- |
| **Backend**             | Laravel 12, PHP 8.2+            |
| **Frontend**            | Blade, Vue.js 3, Vanilla CSS/JS |
| **Build Tools**         | Vite 7, TailwindCSS 4           |
| **Database**            | MySQL                           |
| **Real-time**           | Pusher, Laravel Echo            |
| **PDF Generation**      | barryvdh/laravel-dompdf         |
| **Excel Import/Export** | maatwebsite/excel               |
| **Full-text Search**    | Laravel Scout + TNTSearch       |
| **Image Storage**       | Cloudinary                      |
| **Email**               | Google API (Gmail OAuth2)       |
| **Messaging**           | Meta/WhatsApp Business API      |
| **Queue**               | Laravel Queue (database driver) |

---

## 📊 Scale

| Metric               | Count |
| -------------------- | ----- |
| Controllers          | 32    |
| Eloquent Models      | 44    |
| View Directories     | 33+   |
| Route Definitions    | ~200+ |
| Currencies Supported | 7     |
| Invoice Regions      | 7     |
| Permission Types     | 50+   |

---

## 📸 Highlights

- 🎯 **Kanban Lead Board** — Drag-and-drop pipeline for leads with WhatsApp messaging
- 📊 **Sales Ring Charts** — Visual monthly target tracking with projected vs actual
- 📄 **PDF Invoices** — Auto-generated with amount in words, tax breakdowns, and multi-currency
- 💬 **Threaded Chat** — Slack-like messaging with channels, threads, and file attachments
- 📧 **Gmail Inbox** — Read, compose, reply, and forward directly from the CRM
- 📦 **Excel Import/Export** — Bulk diamond operations with error reporting
- 🔔 **Smart Notifications** — Overdue order alerts and draft completion reminders

---

## 📜 License

**This project is proprietary and confidential.**

This repository is private and is not licensed for public use, modification, or distribution. All rights are reserved. Unauthorized copying, redistribution, or use of this software is strictly prohibited.

---

<p align="center">
  Built with ❤️ using Laravel
</p>
