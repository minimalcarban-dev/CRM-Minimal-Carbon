# TODO

## Security & Authentication
- [ ] Implement dedicated admin guard with session regeneration on login and invalidation on logout.
- [ ] Register admin auth middleware globally and enforce guard-aware authorization checks.
- [ ] Harden session and cookie configuration for admin routes (same-site, secure, timeout review).

## Authorization & Permissions
- [ ] Integrate or remove spatie/laravel-permission; if kept, align models, seeders, and middleware with package usage.
- [ ] Ensure `EnsureAdminHasPermission` is registered and applied to sensitive routes.
- [ ] Add permission checks inside admin controllers and Blade views, especially for CRUD actions.

## File Handling & Storage
- [ ] Replace direct `public_path` uploads with Laravel Storage facade and validate MIME/size limits.
- [ ] Implement cleanup or archival strategy for orphaned uploads.

## UI/UX Improvements
- [ ] Convert admin layout to sidebar navigation aligned with latest design plan.
- [ ] Update admin list to use dedicated permissions management page/modal.
- [ ] Fix placeholder text and ensure actionable buttons/links behave as described.
- [ ] Add responsive tweaks and accessibility attributes to admin views.

## Documentation & Configuration
- [ ] Update `.env.example` with secure defaults and documentation notes for production.
- [ ] Refresh README files to reflect current permission workflow and deployment steps.

## Testing & Tooling
- [ ] Expand feature tests to cover admin auth, permission assignment, and attribute CRUD flows.
- [ ] Add upload handling tests (including failure scenarios) and queue/job coverage if applicable.
- [ ] Configure CI workflow or local scripts to run Pest suite consistently.

## Operational Safeguards
- [ ] Introduce logging/auditing for admin permission changes and critical actions.
- [ ] Evaluate caching strategy for permissions and invalidate cache on updates.
- [ ] Review and finalize queue/mail/storage configs for production readiness.
