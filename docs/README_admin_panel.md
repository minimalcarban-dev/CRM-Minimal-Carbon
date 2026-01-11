Admin Panel Setup Notes

This repository now contains a hardened Admin Management panel (CRUD + secure guard-based auth + storage-backed file uploads).

Key points:

- Migration: a migration file was added: database/migrations/0001_01_01_000100_create_admins_table.php
  - Run: php artisan migrate

- Authentication: dedicated `admin` guard with session-backed authentication.
  - Seed at least one admin record (`is_super = true`) to bootstrap access.
  - Login page: /admin/login
  - Sessions are regenerated on login/logout and rate limited (5 attempts per minute per IP/email).

- Uploads: documents are written to the `public` storage disk (`storage/app/public/admins`).
  - Run `php artisan storage:link` to publish the `storage` symlink for local development.
  - Ensure the web-server can write to `storage/app/public`.
  - Allowed types: jpg, jpeg, png. Max size: 2MB.

- Routes:
  - Admin login: GET /admin/login, POST /admin/login
  - Admin CRUD (protected): prefix /admin/admins

- Password UX:
  - Realtime strength indicator and a simple password suggestion button are implemented in the create/edit forms.

Security notes & next steps:

- Guard authentication uses hashed passwords via the `admins` table; configure your provisioning flow accordingly.
- Middleware aliases `admin.auth` and `admin.permission` are registered in `bootstrap/app.php` and attached to routes.
- Sessions default to encrypted, secure cookies; review `.env.example` before going live.
- Add tests and apply more thorough sanitization/CSRF protections (Laravel provides CSRF by default for forms using @csrf).

How to try it locally:

1. Set ADMIN_EMAIL and ADMIN_PASSWORD in .env
2. Run migrations: php artisan migrate
3. Start the server: php artisan serve
4. Visit http://127.0.0.1:8000/admin/login and login with the credentials from .env

Additional setup for storage & tests:

- Run `php artisan storage:link` to publish the `storage` symlink (uploads are written to the `public` disk).
- To run tests locally:
  - Copy `.env.example` to `.env` and configure your DB.
  - Run `composer install` then `php artisan key:generate` and `php artisan migrate`.
  - Run `./vendor/bin/pest` or `php artisan test`.

