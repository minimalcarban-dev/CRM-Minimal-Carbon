# Project Documentation: CRM-Minimal-Carbon

**Version:** 1.0  
**Date:** 2025-12-12

---

## 1. Project Overview

### 1.1. Introduction

CRM-Minimal-Carbon is a bespoke, robust, and scalable Customer Relationship Management (CRM) system built on the Laravel framework. It is specifically tailored for the jewelry industry, with a focus on managing a diamond inventory, customer orders, and internal communications.

The application provides a secure administrative backend, a real-time chat system for team collaboration, and powerful tools for bulk data management, making it a central hub for all business operations.

### 1.2. Business Objective

The primary goal of this project is to streamline and centralize the key business processes of a jewelry company. By providing a single platform for inventory management, order tracking, and communication, the application aims to:

-   **Increase Operational Efficiency:** Automate and simplify tasks like inventory updates and data entry.
-   **Enhance Team Collaboration:** Provide a real-time chat platform for seamless internal communication.
-   **Improve Data Accuracy:** Ensure a single source of truth for diamond inventory and order information.
-   **Provide Business Insights:** Lay the groundwork for future reporting and analytics features.

---

## 2. Major Features Implemented

This application comes with a rich set of features designed to meet the demands of a modern jewelry business.

### 2.1. Secure Admin & User Management

-   **Role-Based Access Control:** A sophisticated permission system allows for granular control over what admins can see and do.
-   **Admin Authentication:** Separate, secure login and session management for administrators.
-   **Super Admin Role:** A top-level admin with the ability to manage other admin accounts and their permissions.

### 2.2. Real-time Chat

-   **Internal Communication:** A built-in chat system allows admins to communicate in real-time.
-   **Channels:** Conversations can be organized into different channels.
-   **File Attachments & Mentions:** Supports sending attachments and mentioning other users to notify them.
-   **Real-time Notifications:** Utilizes WebSockets for instant message delivery and read receipts.

### 2.3. Diamond Inventory Management

-   **CRUD Operations:** Full capabilities to Create, Read, Update, and Delete diamond records.
-   **Detailed Attributes:** Tracks numerous properties for each diamond, including shape, cut, clarity, color, and more.
-   **Asynchronous Import/Export:**
    -   **Excel Import:** Admins can upload an Excel file to bulk-add or update diamond records. The import process runs in the background to handle large files without tying up the user interface.
    -   **Excel Export:** The entire diamond inventory can be exported to an Excel file, also as a background process.
-   **Job Tracking:** The progress of import/export jobs is tracked, and admins are notified upon completion.

### 2.4. Order Management

-   **Order Tracking:** The system can manage customer orders, linking them to specific inventory items.
-   **Custom Attributes:** Supports various jewelry-specific properties like metal type, ring size, etc.

### 2.5. Audit & Logging

-   **Audit Trails:** The application keeps a log of important events, providing an audit trail for key actions taken within the system. This is crucial for accountability and security.

### 2.6. Notifications

-   **In-App and External Notifications:** A system for notifying users about important events, such as being assigned a diamond or mentioned in a chat.

---

## 3. System Architecture

The application is built using a modern technology stack, ensuring performance, scalability, and maintainability.

-   **Backend:** **Laravel (PHP)** - A robust and elegant PHP framework that provides the core structure (MVC), routing, ORM (Eloquent), and other essential features.
-   **Frontend:** **Vue.js & Blade** - The frontend is a mix of traditional Laravel Blade templates and dynamic Vue.js components for interactive features like the chat.
-   **Database:** Assumed to be **MySQL/PostgreSQL**, managed via Laravel's migration system.
-   **Real-time Communication:** **Laravel Echo, Pusher, and WebSockets** are used to power the real-time features of the application, such as the chat.
-   **Job Queues:** **Laravel Queues** are used to handle long-running tasks like importing and exporting large data files, ensuring the application remains responsive.

---

## 4. UI Flow & Wireframes (Text-based)

This section provides a textual representation of the user interface and flow for key features.

### 4.1. Admin Login Flow

```
/login
+--------------------------------------+
|                                      |
|          CRM LOGIN                   |
|                                      |
|  Email:    [__________________]      |
|  Password: [__________________]      |
|                                      |
|            [  LOGIN  ]               |
|                                      |
+--------------------------------------+
        |
        | (Successful Login)
        V
/admin/dashboard
+--------------------------------------------------+
|  NAV: Dashboard | Diamonds | Orders | Chat       |
+--------------------------------------------------+
|                                                  |
|              Welcome, [Admin Name]!              |
|                                                  |
|   +----------------+   +----------------+        |
|   | Recent Orders  |   | Chat Activity  |        |
|   +----------------+   +----------------+        |
|                                                  |
+--------------------------------------------------+
```

### 4.2. Chat Interface

```
/admin/chat
+-------------------------------------------------------------------+
|  NAV: Dashboard | Diamonds | Orders | Chat (Active)                |
+-------------------------------------------------------------------+
| CHANNELS        |  Channel: #general                              |
|-----------------|-------------------------------------------------|
| #general        |  User1: Hi team, any updates?   [10:00 AM]      |
| #sales          |                                                 |
| #support        |  You: I'm working on the new order. [10:01 AM]  |
|                 |                                                 |
|                 |  User2: @You can you check the diamond? [10:02 AM] |
|                 |                                                 |
|                 |                                                 |
|                 |                                                 |
|                 |                                                 |
|-----------------|-------------------------------------------------|
|                 |  Message #general: [____________________] [Send] |
+-------------------------------------------------------------------+
```

### 4.3. Diamond Management Dashboard

```
/admin/diamonds
+---------------------------------------------------------------------------------+
|  NAV: Dashboard | Diamonds (Active) | Orders | Chat                             |
+---------------------------------------------------------------------------------+
|                                                                                 |
|  [ Import Diamonds ] [ Export Diamonds ] [ Add New Diamond ]                    |
|                                                                                 |
|  Filter: [Shape v] [Color v] [Clarity v] [ Search by Barcode... ] [Apply]        |
|                                                                                 |
|---------------------------------------------------------------------------------|
| Barcode | Shape | Cut   | Color | Clarity | Price      | Status    | Actions     |
|---------|-------|-------|-------|---------|------------|-----------|-------------|
| 12345   | Round | Excel | G     | VS1     | $5,000     | Available | [View][Edit]|
| 12346   | Pear  | Good  | D     | SI2     | $4,200     | Sold      | [View][Edit]|
| ...     | ...   | ...   | ...   | ...     | ...        | ...       | ...         |
+---------------------------------------------------------------------------------+
```

---

## 5. Project File Structure (Simplified)

```
CRM-Minimal-Carbon/
├── app/
│   ├── Console/         # Artisan commands
│   ├── Events/          # Real-time events (e.g., MessageSent)
│   ├── Exports/         # Classes for exporting data (e.g., DiamondsExport)
│   ├── Http/
│   │   ├── Controllers/ # Application controllers (DiamondController, ChatController)
│   │   └── Middleware/  # Request middleware
│   ├── Imports/         # Classes for importing data (e.g., DiamondsImport)
│   ├── Jobs/            # Background jobs (ProcessDiamondImport)
│   ├── Models/          # Eloquent models (Diamond, Order, User, Message)
│   ├── Notifications/   # Notification classes
│   └── Providers/       # Service providers
├── config/              # Application configuration files
├── database/
│   ├── factories/       # Model factories for testing
│   ├── migrations/      # Database schema migrations
│   └── seeders/         # Database seeders
├── public/              # Publicly accessible files
├── resources/
│   ├── js/              # JavaScript source files (including Vue components)
│   └── views/           # Blade templates
├── routes/
│   ├── web.php          # Web routes
│   └── channels.php     # Broadcast channel routes
├── storage/             # Storage for logs, cache, and uploaded files
├── tests/               # Application tests
├── composer.json        # Backend dependencies
└── package.json         # Frontend dependencies
```

---

## 6. Database Schema Overview

The database contains several key tables that model the application's domain:

-   `admins`: Stores administrator accounts and their credentials.
-   `users`: Stores standard user/customer accounts.
-   `permissions`: Defines the available permissions in the system.
-   `admin_permission`: Links admins to their assigned permissions (many-to-many).
-   `diamonds`: The main inventory table, with columns for all diamond attributes.
-   `orders`: Stores information about customer orders.
-   `channels`, `messages`, `message_attachments`: Power the real-time chat system.
-   `jobs`, `job_tracks`: Manage and track the status of background jobs.
-   `audit_logs`: Records significant actions performed by users for auditing.
-   `notifications`: Stores notifications sent to users.

---

## 7. Unsolved Issues & Future Improvements

The following is a list of known issues, security hardening tasks, and potential feature improvements that are currently pending. These are drawn from the project's `TODO.md` file.

### Critical/High Priority

-   **Security Hardening:**
    -   Implement a dedicated admin guard and strengthen session/cookie configurations.
    -   Properly register and enforce authorization middleware (`EnsureAdminHasPermission`) across all sensitive routes and controller actions.
-   **File Upload Vulnerabilities:**
    -   Replace direct `public_path` file uploads with Laravel's `Storage` facade to prevent directory traversal and other attacks.
    -   Enforce strict validation on file MIME types and sizes.

### Medium Priority

-   **Authorization System:**
    -   The `spatie/laravel-permission` package is included but may not be fully integrated. A decision is needed to either fully implement it or remove it.
    -   Permission checks are missing from several controllers and Blade views.
-   **UI/UX:**
    -   The admin layout needs to be updated to a modern sidebar navigation.
    -   Placeholder text and non-functional UI elements need to be fixed.
-   **Testing:**
    -   Test coverage is low. Feature tests for admin auth, permissions, and file uploads are needed.

### Future Improvements

-   **Reporting & Analytics:** Develop a dashboard to visualize sales trends, inventory turnover, and other KPIs.
-   **Advanced Search:** Implement a more powerful search engine (e.g., Elasticsearch) for the diamond inventory.
-   **CI/CD Pipeline:** Configure a Continuous Integration/Continuous Deployment pipeline to automate testing and deployment.
-   **API for Mobile App:** Develop a RESTful API to allow a future mobile application to connect to the system.

---

## 8. Setup and Installation Guide

Follow these steps to set up the project in a local development environment.

### Prerequisites

-   PHP (>= 8.1)
-   Composer
-   Node.js & npm
-   A database server (e.g., MySQL)

### Installation Steps

1.  **Clone the repository:**

    ```bash
    git clone <repository-url>
    cd CRM-Minimal-Carbon
    ```

2.  **Install backend dependencies:**

    ```bash
    composer install
    ```

3.  **Install frontend dependencies:**

    ```bash
    npm install
    ```

4.  **Create environment file:**

    ```bash
    cp .env.example .env
    ```

5.  **Generate application key:**

    ```bash
    php artisan key:generate
    ```

6.  **Configure `.env` file:**

    -   Set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to connect to your local database.
    -   Configure your `MAIL_` settings.
    -   Configure your broadcast driver (e.g., `PUSHER_APP_ID`, `PUSHER_APP_KEY`).

7.  **Run database migrations and seeders:**

    ```bash
    php artisan migrate --seed
    ```

8.  **Compile frontend assets:**

    ```bash
    npm run dev
    ```

9.  **Start the development server:**
    ```bash
    php artisan serve
    ```

The application should now be running, typically at `http://127.0.0.1:8000`.
