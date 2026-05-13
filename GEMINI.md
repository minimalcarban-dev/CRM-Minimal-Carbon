# AI Agent Rules & Constraints

- **CRITICAL**: NEVER run `php artisan migrate:fresh`, `migrate:refresh`, or `db:seed` without explicitly asking for my permission first.
- **CRITICAL**: NEVER run any SQL commands or scripts that drop, truncate, or wipe local database tables.
- **CRITICAL**: When running automated tests, double-check that they are strictly using the `sqlite` in-memory database and will not interact with the local MySQL database.
