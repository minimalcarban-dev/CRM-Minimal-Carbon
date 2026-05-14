# AI_RULES.md — Diamond Stock Management System (CRM-Minimal-Carbon)

# Paste this at the start of every AI session before writing any code.

# Last updated: 2026-05-13

---

## 1. WHAT THIS PROJECT IS

A Laravel-based CRM + Stock Management system for a diamond and jewellery business.

**Core domains:**

- Diamond stock (inventory, import/export, duration tracking)
- Jewellery stock (pricing, materials, side stones)
- Orders (creation, tracking, notifications)
- Leads & CRM (lead cards, activities, pipeline)
- Clients & Companies
- Invoices & Expenses
- Gold tracking (purchases, rates, distribution)
- Melee diamonds (small stone inventory)
- Permissions & Admin management
- Real-time Chat (WebSockets via Laravel Echo/Pusher)
- Gmail/Email sync
- Cloudinary file/image uploads
- Shopify product integration
- Aramex shipping integration

**Stack:** Laravel (PHP), Blade templates, JavaScript (vanilla + Vue components), MySQL, Redis (queues/cache), Laravel Echo + Pusher (WebSockets), Cloudinary, Maatwebsite Excel (imports/exports).

---

## 2. ARCHITECTURE RULES — ALWAYS FOLLOW THESE

### Controllers must be thin

- Controllers only do: validate input → call a service → return a response.
- No business logic, no DB queries, no calculations inside controllers.
- If you find yourself writing more than 30 lines of logic in a controller method, stop and move it to a Service class.

```php
// CORRECT
public function store(StoreDiamondRequest $request, DiamondService $service)
{
    $diamond = $service->create($request->validated());
    return redirect()->route('diamonds.index')->with('success', 'Diamond created.');
}

// WRONG — never do this
public function store(Request $request)
{
    $price = $request->carat * $request->rate * 0.92;
    $diamond = Diamond::create([...]);
    // 40 more lines of logic...
}
```

### Services hold all business logic

- One service class per domain: `DiamondService`, `OrderService`, `GoldRateService`, etc.
- Services talk to Models. Controllers talk to Services. Models do NOT talk to Controllers.
- If a service grows beyond 300 lines, split it.

### Models are data definitions only

- Relationships, scopes, casts, accessors/mutators — that's it.
- No HTTP logic, no business calculations, no sending notifications from inside a model (use Observers or Events instead).

### Form Requests handle all validation

- Never validate inside controllers with `$request->validate()` inline.
- Always use a dedicated `StoreXxxRequest` / `UpdateXxxRequest` class.
- Authorization (`authorize()`) must return a real check, never just `return true`.

### Policies handle all authorization

- Never check permissions inline in controllers with raw `if` statements.
- Always use `$this->authorize()` or `Gate::allows()` referencing a Policy.
- **Critical:** `hasPermission()` is already a god node with 34+ connections. Do NOT add more raw calls to it across new controllers. Route new permission checks through the existing Policy structure.

---

## 3. GOD NODES — DO NOT FEED THESE

These files are already dangerously large and connected. When working near them:

- Do NOT add new methods into them.
- Do NOT add new relationships or logic.
- If new behavior is needed, create a new Service or helper class and call it from there.

| File                      | Problem                                   |
| ------------------------- | ----------------------------------------- |
| `Diamond.php` (Model)   | 49 edges — too many responsibilities     |
| `OrderController.php`   | 40 edges — too fat                       |
| `ChatController.php`    | 37 edges — too fat                       |
| `hasPermission()`       | 37 edges — permission logic is scattered |
| `DiamondController.php` | 30 edges — too fat                       |

---

## 4. DATABASE & MIGRATION RULES

- **Never use `->change()` on a column in production without checking existing data first.**
- **Never drop a column** unless you've confirmed it's unused across the entire codebase (search before deleting).
- **Never use `DB::statement('DROP TABLE...')` or `Schema::drop()` in a migration** without a confirmed backup.
- Every migration must have a working `down()` method.
- If a migration adds a non-nullable column to an existing table, it must have a `->default()` value.
- Never run raw `DB::unprepared()` for data migrations in the same file as schema changes. Separate them.

---

## 5. QUEUE & ASYNC RULES

Active queued jobs in this project: `ProcessDiamondExport`, `ProcessDiamondImport`, `ExportProductToShopifyJob`, `ImportShopifyProductsJob`, `ArchiveDailySales`, `SyncAllOrdersTracking`, `CheckStalledShipments`.

Rules:

- Every job class must implement `ShouldQueue` and use the `Queueable` trait.
- Every job must have a `$tries` property set (default: 3) and a `backoff` or `retryAfter` defined.
- **Never put a queued job inside a database transaction.** Dispatch after the transaction commits.
- Jobs must not have side effects that can't be safely retried (make them idempotent).
- Never dispatch a job inside a loop without batching. Use `Bus::batch()` for bulk operations.

```php
// WRONG
foreach ($diamonds as $diamond) {
    ProcessDiamondExport::dispatch($diamond); // N jobs, no control
}

// CORRECT
$batch = Bus::batch(
    collect($diamonds)->map(fn($d) => new ProcessDiamondExport($d))->all()
)->dispatch();
```

---

## 6. WEBSOCKET / REAL-TIME RULES

This project uses Laravel Echo + Pusher for real-time chat, notifications, and typing indicators.

- **Never broadcast directly from a Controller.** Use Events (`DiamondAssignedEvent`, `MessagePinned`, etc.) and let the Event handle broadcasting.
- Every broadcasted Event must implement `ShouldBroadcast` and define `broadcastOn()` clearly.
- **Never broadcast inside a database transaction.** Same rule as jobs — broadcast after commit.
- Client-side: always unsubscribe from channels when a component is destroyed/navigated away. Stale WebSocket subscriptions cause memory leaks and ghost listeners.
- Chat state (unread counts, typing indicators) must never be the source of truth for business logic. It's UI state only.

---

## 7. CLOUDINARY UPLOAD RULES

- All file uploads go through `CloudinaryUploadService`. Never call Cloudinary SDK directly in a controller.
- Always validate file type and size in the Form Request before it reaches the service.
- Store the Cloudinary public_id + url in the database, never just the url alone (you need the public_id to delete).
- Cloudinary uploads must happen in a job if the file is large or part of a bulk operation.

---

## 8. EMAIL / GMAIL SYNC RULES

Active classes: `GmailAuthService`, `EmailRepository`, `EmailComposeService`, `EmailPolicy`, `EmailAuditLog`.

- All Gmail API calls go through `GmailAuthService`. Never call Google API directly in a controller.
- Email sending goes through `EmailComposeService`. Never use `Mail::send()` raw in a controller.
- Every email action (send, archive, delete) must be logged via `EmailAuditLog`.
- Never store Gmail OAuth tokens in session. They belong in the database against the `EmailAccount` model.

---

## 9. SHOPIFY INTEGRATION RULES

- All Shopify API calls go through dedicated Job classes (`ExportProductToShopifyJob`, `ImportShopifyProductsJob`).
- Never make synchronous Shopify API calls in a web request. Always queue them.
- Handle Shopify rate limits explicitly — add retry logic with exponential backoff.
- Product sync must be idempotent: running it twice must not create duplicates.

---

## 10. ARAMEX SHIPPING RULES

- Aramex API calls belong in a dedicated `AramexService` (if it doesn't exist, create it — do not put Aramex logic in `OrderController`).
- Shipping status sync happens via `SyncAllOrdersTracking` — do not duplicate this logic elsewhere.
- Always handle Aramex API failures gracefully with a try/catch and log the failure. Never let a shipping API failure break an order update.

---

## 11. PERMISSION SYSTEM RULES

This is the highest-risk area. Follow strictly.

- The permission system uses `hasPermission()`, `EnsureAdminHasPermission`, and Policy classes.
- **Every new controller action that modifies data must have a permission check.** No exceptions.
- Do not add new raw `hasPermission()` calls scattered in blade files or controllers. Route through Policies.
- Admin vs regular user access must always be explicit — never assume a logged-in user has access.
- When adding a new feature, define its permission in the permissions table first, then reference it. Do not hardcode permission strings in multiple places.
- Never write: $admin->is_super || $admin->hasPermission(...)
  hasPermission() already handles super admin logic. Adding is_super || bypasses strict prefix rules.
- Never add early is_super returns in middleware before calling hasPermission().
- Strict prefixes (purchases., expenses., gold_tracking., factories., sales.) must be enforced
  through hasPermission() only — not worked around.

---

## 12. FRONTEND / BLADE RULES

- Blade views are for display only. No business logic, no DB queries in blade files.
- Never use `@php` blocks in blade for anything beyond simple variable assignment.
- CSS goes in dedicated partial files (`partials.attribute-styles`, etc.) — not inline `<style>` tags scattered in views.
- JavaScript that handles real-time (chat, notifications) lives in dedicated JS files — not in `<script>` tags inside blade.
- For any new UI that requires state management, use the existing Vue component pattern (`useChatEditor`, `useChatMessages`, etc.) — do not invent a new pattern.

---

## 13. TESTING RULES

- Every new Service method must have at least one feature test.
- Use the existing factory pattern: `makeDiamondFor()`, `makeTestOrder()`, `makeAdminWithChatAccess()` — do not create raw model instances in tests.
- Tests must not depend on a specific database state. Use `RefreshDatabase` or `DatabaseTransactions`.
- Never write a test that calls external APIs (Shopify, Gmail, Cloudinary, Aramex). Mock them.

---

## 14. WHAT TO DO WHEN AI GENERATES BAD CODE

If AI-generated code does any of the following, reject it and ask again with the specific rule:

| AI does this                                      | Tell AI this                                                                   |
| ------------------------------------------------- | ------------------------------------------------------------------------------ |
| Puts DB queries in controller                     | "Move all DB logic to a Service class. Controller must only call the service." |
| Validates with inline `$request->validate()`    | "Create a Form Request class for this validation."                             |
| Calls `hasPermission()` directly in controller  | "Use a Policy class and `$this->authorize()` instead."                       |
| Adds logic to `Diamond.php` model               | "Do not add logic to Diamond model. Create a DiamondService method instead."   |
| Makes Shopify/Gmail/Cloudinary call in controller | "This must go through the existing service class or a queued job."             |
| Skips `down()` in migration                     | "Add a complete `down()` method that reverses the migration."                |
| Dispatches jobs inside a loop                     | "Use Bus::batch() for bulk job dispatch."                                      |

---

## 15. QUICK REFERENCE — EXISTING PATTERNS TO FOLLOW

When you need to... use this as your reference:

| Task                 | Look at this existing class                                  |
| -------------------- | ------------------------------------------------------------ |
| New stock feature    | `DiamondController` + `DiamondService` pattern           |
| New permission check | `AdminPermissionController` + `EnsureAdminHasPermission` |
| New file upload      | `CloudinaryUploadService`                                  |
| New export           | `ClientsExport`, `FailedDiamondsExport`                  |
| New import           | `DiamondsImport`                                           |
| New background job   | `ProcessDiamondImport`                                     |
| New real-time event  | `DiamondAssignedEvent` + `DiamondAssignedNotification`   |
| New email            | `EmailComposeService`                                      |
| New gold rate logic  | `GoldRateService`                                          |
| New pricing logic    | `JewelleryPricingService`                                  |

---

_This file is a living document. Update it when you establish new patterns or discover new anti-patterns in this codebase._
