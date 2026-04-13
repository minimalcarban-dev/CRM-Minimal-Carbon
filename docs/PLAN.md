# Controller Optimization Audit And Refactor Plan

## Summary
This codebase has 3 controller classes that are immediate production risks, 5 more that need structural refactoring, and a broad pattern of controller bloat: validation, authorization, persistence, business rules, uploads, notifications, and response shaping are repeatedly mixed in request handlers.

The most urgent issues are:
- `BaseResourceController.php` contains a real runtime bug in `destroy()` by referencing an undefined `$request`.
- `TrackingWebhookController.php` accepts unauthenticated public webhook traffic and mutates orders.
- `OrderController.php` and `ChatController.php` are god controllers with heavy business logic, long methods, duplicated flows, and synchronous work inside requests.

## 2a. Summary Table

| Controller File | Lines | Issues Found | Priority | Action |
|---|---:|---|---|---|
| `app/Http/Controllers/AdminAuthController.php` | 79 | Minor dead code risk, auth flow in controller | LOW | Keep thin, clean imports |
| `app/Http/Controllers/AdminController.php` | 550 | CRUD + role/permission orchestration, long methods | MEDIUM | Split admin management concerns |
| `app/Http/Controllers/AdminPermissionController.php` | 101 | Thin but repetitive permission wiring | LOW | Minor cleanup |
| `app/Http/Controllers/AttributeHubController.php` | 245 | Aggregation/reporting in controller, duplicated lookups | MEDIUM | Extract query service |
| `app/Http/Controllers/BaseResourceController.php` | 392 | Shared bug, SRP violation, duplicated CRUD plumbing | CRITICAL | Refactor shared base + fix destroy path |
| `app/Http/Controllers/ChatController.php` | 1845 | God controller, sync file/scan work, dead code, unsafe proxy | CRITICAL | Split into service/repository/action classes |
| `app/Http/Controllers/ClientController.php` | 130 | Minor duplication, possible unused import | LOW | Cleanup only |
| `app/Http/Controllers/ClosureTypeController.php` | 49 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/CompanyController.php` | 1024 | CRUD + dashboards + CSV exports + targets, in-memory analytics | HIGH | Split CRUD/reporting/export services |
| `app/Http/Controllers/Controller.php` | 29 | No meaningful issues | LOW | No change |
| `app/Http/Controllers/DashboardController.php` | 275 | Monolithic index, wrong sold-status constant, in-memory sums | HIGH | Extract dashboard query service |
| `app/Http/Controllers/DiamondClarityController.php` | 48 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/DiamondController.php` | 1341 | CRUD + stock state + uploads + assignment + bulk edit | HIGH | Split service/repository/validator layers |
| `app/Http/Controllers/DiamondCutController.php` | 48 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/ExpenseController.php` | 586 | CRUD + uploads + reports/exports, duplicated flows | MEDIUM | Extract shared expense actions |
| `app/Http/Controllers/FactoryController.php` | 136 | Thin CRUD/controller duplication | LOW | Minor cleanup |
| `app/Http/Controllers/GoldTrackingController.php` | 775 | Purchase/distribution/rate analysis mixed, in-memory processing | HIGH | Split workflow and reporting services |
| `app/Http/Controllers/InvoiceController.php` | 288 | Moderate controller-heavy response composition | MEDIUM | Extract invoice query helpers |
| `app/Http/Controllers/JewelleryCalculatorController.php` | 111 | Long rate assembly method | MEDIUM | Extract calculator service |
| `app/Http/Controllers/JewelleryStockController.php` | 324 | CRUD + uploads + filters + stock logic | MEDIUM | Split stock service |
| `app/Http/Controllers/LeadController.php` | 533 | Multi-responsibility controller, duplicated filters/actions | MEDIUM | Extract lead workflow services |
| `app/Http/Controllers/MeleeCategoryController.php` | 79 | Thin CRUD/controller duplication | LOW | Minor cleanup |
| `app/Http/Controllers/MeleeDiamondController.php` | 572 | Stock arithmetic and transaction logic in controller | MEDIUM | Extract transaction service |
| `app/Http/Controllers/MetalTypeController.php` | 49 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/MetaSettingsController.php` | 203 | Config orchestration in controller | MEDIUM | Extract settings service |
| `app/Http/Controllers/MetaWebhookController.php` | 297 | Public webhook, debug signature bypass, payload logging | HIGH | Harden webhook + split processor |
| `app/Http/Controllers/NotificationController.php` | 90 | Thin controller, possible unused import | LOW | Cleanup only |
| `app/Http/Controllers/OrderController.php` | 2787 | Severe god controller, long methods, sync tracking, repeated queries | CRITICAL | Major decomposition into thin controller |
| `app/Http/Controllers/OrderDraftController.php` | 319 | Mixed save/index stats logic, duplicated role checks | MEDIUM | Extract draft service |
| `app/Http/Controllers/PackageController.php` | 180 | Minor duplication, possible dead import | LOW | Cleanup only |
| `app/Http/Controllers/PartyController.php` | 143 | Thin CRUD/controller duplication | LOW | Minor cleanup |
| `app/Http/Controllers/PermissionController.php` | 129 | Inline permission gating, repeated constructor auth checks | MEDIUM | Consolidate auth/permission pattern |
| `app/Http/Controllers/PurchaseController.php` | 441 | CRUD + uploads + transition logic duplication | MEDIUM | Extract purchase service |
| `app/Http/Controllers/RingSizeController.php` | 48 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/SettingsController.php` | 425 | Mixed security, IP restriction, device trust workflows | MEDIUM | Extract IP access service |
| `app/Http/Controllers/SettingTypeController.php` | 49 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/ShopifyController.php` | 356 | Controller-heavy sync/orchestration | MEDIUM | Extract Shopify application service |
| `app/Http/Controllers/ShopifyWebhookController.php` | 182 | Public webhook, acceptable HMAC model but mixed concerns | MEDIUM | Thin controller around webhook processor |
| `app/Http/Controllers/StoneColorController.php` | 48 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/StoneShapeController.php` | 48 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/StoneTypeController.php` | 48 | Inherits base-controller risk | LOW | Stabilise via base refactor |
| `app/Http/Controllers/TrackingWebhookController.php` | 126 | Public unauthenticated mutation endpoint, monolithic handler | CRITICAL | Add webhook verification + isolate processor |

## Phase 1 Analysis

### CRITICAL controllers

**File:** `app/Http/Controllers/BaseResourceController.php`  
Responsibilities:
- permission checks
- validation
- model persistence
- transactions
- flash/redirect responses
- logging
- cache invalidation

Issues identified:
- `[40-332]` SRP violation: one base controller handles authorization, validation, persistence, logging, and response formatting.
- `[129-185]` `store()` exceeds 40 lines and duplicates data-preparation logic later reused in `update()`.
- `[223-282]` `update()` duplicates boolean normalization and persistence plumbing from `store()`.
- `[287-332]` `destroy()` references `$request` without declaring it. This is a real runtime bug and will fail with `Undefined variable $request`.
- `[307,324]` broken logging path prevents shared delete flow from being reliable.
- Security gap: LOW. Authorization exists, but failure handling is mixed into controller responses.
- Dead-code risk: base class is over-generalized and forces unrelated child controllers into a shared contract.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | File size | 392 lines | ~220 controller + extracted support classes |
| 2 | Delete flow | Broken shared `destroy()` | Safe, tested delete action |
| 3 | CRUD logic | Stored in controller base | Moved to resource action service |
| 4 | Permission checks | Inline | Shared guard helper |

**Proposed new file structure**
```text
app/Http/Controllers/
  BaseResourceController.php
app/Services/Resources/
  ResourceMutationService.php
  ResourcePermissionGuard.php
```

**Estimated lines after refactor:** ~320 lines across 3 files

---

**File:** `app/Http/Controllers/OrderController.php`  
Responsibilities:
- filtering/search
- dashboard metrics
- validation
- order creation/update/cancel/delete
- payment persistence
- diamond stock mutation
- file upload/removal
- discussion notifications
- tracking sync
- response shaping

Issues identified:
- `[68-370]` `index()` mixes filtering, aggregation, role branching, search, counts, and view data assembly.
- `[174,188]` uses `get()->sum()` instead of DB aggregation.
- `[191-205,344-345]` unbounded reference loads.
- `[391-684]` `store()` mixes validation, client upsert, uploads, order creation, payment creation, stock mutation.
- `[512-519]` per-diamond writes inside request handler.
- `[850-1227]` `update()` is a 378-line god method.
- `[1232-1299]` `show()` loads multiple unbounded reference datasets and edit history.
- `[1635-1688]` repeated file-normalization/removal logic.
- `[1693-1781]` `destroy()` mixes Cloudinary deletion, stock reversal, order deletion, flash handling.
- `[1779,1881]` raw exception messages exposed to users.
- `[1786-1883]` `cancel()` duplicates stock reversal and side effects.
- `[1888-1930]` `syncAllTracking()` performs long-running sync in request thread, `set_time_limit(300)`, sleeps in loop, loads all matching orders.
- `[2729-2738]` form partial helper still pulls broad lookup datasets in controller.
- SOLID violations:
  - SRP: pervasive across entire file.
  - OCP: branching by role/status repeatedly instead of policy/query objects.
  - DIP: direct ORM and infra calls everywhere.
- Security gaps:
  - MEDIUM: inconsistent validation depth across actions.
  - MEDIUM: raw error exposure.
- Dead code:
  - unused import candidates and repeated magic status strings; verify during implementation.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | File size | 2787 lines | 1 thin controller + 5 focused classes |
| 2 | Order creation | Controller orchestration | `OrderService` |
| 3 | Stock mutation | Inline loops | `OrderStockService` |
| 4 | Upload handling | Inline | `OrderAttachmentService` |
| 5 | Index/filtering | Large branching method | `OrderQueryService` |
| 6 | Tracking sync | Long-running request | dedicated sync action service |

**Proposed new file structure**
```text
app/Http/Controllers/orders/
  OrderController.php
app/Services/Orders/
  OrderService.php
  OrderQueryService.php
  OrderStockService.php
  OrderAttachmentService.php
  OrderTrackingSyncService.php
app/Support/Validation/
  OrderValidator.php
```

**Estimated lines after refactor:** ~1400-1700 lines across 7 files

---

**File:** `app/Http/Controllers/ChatController.php`  
Responsibilities:
- channel creation/update
- direct-message resolution
- message send/edit/delete
- attachment handling
- virus scanning
- read receipts
- thread replies
- search/sidebar data
- order suggestion
- attachment proxying
- broadcasting

Issues identified:
- `[85-146]` `createChannel()` combines validation, member normalization, persistence, and events.
- `[218-327]` `direct()` combines routing logic, participant lookup, channel creation, and response building.
- `[534-736]` `sendMessage()` is 203 lines with attachment handling, scan workflow, persistence, link enrichment, broadcasting.
- `[653-656]` synchronous scan/storage work occurs inline.
- `[777-788]` repeated per-message `reads()->create()` loop.
- `[850-873]` sidebar performs several separate queries and shaping passes.
- `[986-1049]` `updateChannelMembers()` mixes domain logic, notification, and broadcasting.
- `[1160-1185]` reply flow duplicates send-message behavior.
- `[1821-1826]` `proxyAttachment()` disables TLS verification (`verify => false`), a HIGH security concern.
- `[151+]` commented old `getChannels()` block.
- `[350+]` commented legacy `sendMessage()` code.
- SOLID violations:
  - SRP across whole file.
  - DIP via direct infra/storage/network access in controller.
- Security gaps:
  - HIGH: outbound HTTP with TLS verification disabled.
  - MEDIUM: attachment/network logic mixed with request handling.
- Maintainability:
  - repeated attachment/message composition.
  - many long methods over 40 lines.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | File size | 1845 lines | thin controller + chat application services |
| 2 | Messaging flow | Inline | `ChatMessageService` |
| 3 | Attachments | Inline validation/store/scan | `ChatAttachmentService` |
| 4 | Sidebar/search | Inline queries | `ChatQueryService` |
| 5 | Proxying | Unsafe TLS-disabled client | secured attachment responder |

**Proposed new file structure**
```text
app/Http/Controllers/chat/
  ChatController.php
app/Services/Chat/
  ChatChannelService.php
  ChatMessageService.php
  ChatAttachmentService.php
  ChatQueryService.php
```

**Estimated lines after refactor:** ~1100-1400 lines across 5 files

---

**File:** `app/Http/Controllers/TrackingWebhookController.php`  
Responsibilities:
- webhook ingestion
- payload parsing
- order lookup
- tracking history transformation
- order mutation
- response generation

Issues identified:
- `[12-125]` single public handler does all webhook work.
- Route exposure: `routes/web.php` public `POST webhook/17track`, CSRF-exempt.
- `[108-120]` updates matching orders without verifying sender authenticity.
- `[48-86]` transforms event arrays inline with manual date formatting/sorting.
- SOLID violations:
  - SRP in single handler.
  - DIP due to direct request/model coupling.
- Performance hotspots:
  - `[88]` `->get()` loads all matching orders.
- Security gaps:
  - HIGH: no webhook signature/auth verification.
  - MEDIUM: broad trust of request payload structure.
- Maintainability:
  - entire behavior packed into one method.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | Authentication | None | explicit webhook verifier |
| 2 | Handler shape | monolithic | controller + processor + verifier |
| 3 | Order updates | inline | dedicated tracking sync service |

**Proposed new file structure**
```text
app/Http/Controllers/webhooks/
  TrackingWebhookController.php
app/Services/Webhooks/
  TrackingWebhookVerifier.php
  TrackingWebhookProcessor.php
```

**Estimated lines after refactor:** ~180-240 lines across 3 files

### HIGH controllers

**File:** `app/Http/Controllers/DiamondController.php`  
Issues identified:
- `[73-206]` oversized filter/index method with query branching and reference lookups.
- `[255-367]` `store()` mixes validation, conversion, barcode generation, uploads, notifications.
- `[372-523]` `update()` duplicates large parts of store flow.
- `[365,521]` raw exception messages exposed.
- `[559-630]` assignment flow duplicates admin lookup and notification wiring.
- `[1198-1293]` bulk edit logic is controller-heavy and pagination is manually bounded.
- Priority reason: stock state, uploads, notifications, and admin assignment should not stay in controller.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | File size | 1341 lines | thin controller + diamond services |
| 2 | Mutation flow | Inline | `DiamondService` |
| 3 | Assignment flow | Inline | `DiamondAssignmentService` |
| 4 | Query/filtering | Controller-built | `DiamondQueryService` |

**Proposed new file structure**
```text
app/Http/Controllers/diamonds/
  DiamondController.php
app/Services/Diamonds/
  DiamondService.php
  DiamondAssignmentService.php
  DiamondQueryService.php
```

**Estimated lines after refactor:** ~900-1100 lines across 4 files

---

**File:** `app/Http/Controllers/CompanyController.php`  
Issues identified:
- `[428-566]` `salesDashboard()` performs analytics shaping inside controller.
- `[463-467]` broad order load followed by in-memory filtering.
- `[721-892]` `allSalesDashboard()` loads full order sets and groups in PHP.
- `[843-850]` `Company::find()` inside grouped map creates N+1 pattern.
- Inherits base-controller shared delete bug.
- Responsibilities split between CRUD, targets, dashboards, and CSV exports.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | File size | 1024 lines | separate CRUD/report/export code |
| 2 | Reporting | In controller | `CompanyReportService` |
| 3 | Export logic | Inline | `CompanyExportService` |
| 4 | CRUD base | Shared broken base | stable base mutation layer |

**Proposed new file structure**
```text
app/Http/Controllers/companies/
  CompanyController.php
app/Services/Companies/
  CompanyService.php
  CompanyReportService.php
  CompanyExportService.php
```

**Estimated lines after refactor:** ~700-850 lines across 4 files

---

**File:** `app/Http/Controllers/GoldTrackingController.php`  
Issues identified:
- `[35-187]` large index method merges/filter/sorts purchase/distribution data in PHP.
- `[224-300]` `storePurchase()` and `[327-425]` `updatePurchase()` duplicate state transitions and upload logic.
- `[579-618]` suspicious-rate detection loads completed purchases and filters in memory.
- Mixed concerns: purchases, distributions, anomaly detection, reporting, upload handling.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | File size | 775 lines | workflow split by concern |
| 2 | Purchase flow | Inline | `GoldPurchaseService` |
| 3 | Rate checks | Inline | `GoldRateAuditService` |
| 4 | Index/reporting | Controller-built | `GoldTrackingQueryService` |

**Proposed new file structure**
```text
app/Http/Controllers/gold/
  GoldTrackingController.php
app/Services/Gold/
  GoldPurchaseService.php
  GoldDistributionService.php
  GoldRateAuditService.php
  GoldTrackingQueryService.php
```

**Estimated lines after refactor:** ~650-800 lines across 5 files

---

**File:** `app/Http/Controllers/DashboardController.php`  
Issues identified:
- `[35-274]` a single 240-line `index()` method handles all dashboard composition.
- `[56,68,76]` in-memory `get()->sum()` patterns.
- `[87-95]` uses `is_sold_out = 'Sold Out'`, inconsistent with actual domain values (`Sold`).
- Mixed concerns: stats, alerts, cache, recent activity formatting.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | Method shape | one large action | small controller + dashboard query service |
| 2 | Sold metrics | inconsistent status constant | shared domain constant mapping |
| 3 | Aggregates | in-memory | DB-level aggregates |

**Proposed new file structure**
```text
app/Http/Controllers/
  DashboardController.php
app/Services/Dashboard/
  DashboardQueryService.php
```

**Estimated lines after refactor:** ~220-280 lines across 2 files

---

**File:** `app/Http/Controllers/MetaWebhookController.php`  
Issues identified:
- Route exposure: public webhook in `routes/web.php`, CSRF-exempt.
- `[48-53]` logs raw headers, payload, and content.
- `[65-75]` invalid signature is bypassed in debug mode.
- `[120-217]` message-event processing mixes lead creation, profile fetch, conversation, message persistence, scoring, assignment.
- Unused import candidate: `ProcessMetaWebhook`.
- Security gaps:
  - HIGH: debug-mode signature bypass.
  - MEDIUM: excessive payload logging.

**Before / After**

| # | Aspect | Before | After |
|---|---|---|---|
| 1 | Signature handling | bypassable in debug | always enforced |
| 2 | Logging | raw payload logging | redacted structured audit logging |
| 3 | Event processing | controller inline | webhook processor service |

**Proposed new file structure**
```text
app/Http/Controllers/webhooks/
  MetaWebhookController.php
app/Services/Webhooks/
  MetaWebhookVerifier.php
  MetaWebhookProcessor.php
```

**Estimated lines after refactor:** ~220-300 lines across 3 files

## 2c. Cross-Cutting Recommendations
- Introduce shared controller-adjacent services for:
  - validation orchestration
  - mutation workflows
  - query/filter assembly
  - attachment upload/removal
  - webhook verification
- Shared middleware/helper candidates:
  - webhook signature verification helper
  - admin permission guard helper
  - consistent safe error responder
  - request audit logger with payload redaction
- Extract common utility patterns:
  - boolean normalization for request payloads
  - file array normalization/removal
  - paginator/query parameter normalization
  - status constant maps for orders/diamonds
- `BaseResourceController` should become thinner, not more powerful. It can stay as a thin CRUD shell, but mutation/query logic should live in focused services.
- Reporting/export logic should not coexist with CRUD in the same controller where avoidable (`CompanyController`, `GoldTrackingController`, `ExpenseController`).
- No new external packages are required for this refactor plan.

## 2d. Refactor Sequence
1. Refactor `BaseResourceController.php` first.
   Lock the shared delete bug and extract minimal resource mutation helpers because multiple low-risk controllers depend on it.
2. Refactor `DashboardController.php`.
   It is isolated, gives a fast win, and establishes shared aggregate/query patterns.
3. Refactor `TrackingWebhookController.php`.
   Public attack surface; hardening should happen early.
4. Refactor `MetaWebhookController.php`.
   Same reason: public webhook, signature/path hardening.
5. Refactor `CompanyController.php`.
   It depends on the stabilized base resource layer and benefits from established query-service patterns.
6. Refactor `GoldTrackingController.php`.
   Reuse reporting/query extraction patterns.
7. Refactor `DiamondController.php`.
   Reuse mutation/query/service separation established earlier.
8. Refactor `OrderController.php`.
   Only after shared mutation/query/upload patterns are in place.
9. Refactor `ChatController.php`.
   Leave until later because it has the broadest surface and highest merge risk.
10. Refactor MEDIUM controllers in this order:
    `ExpenseController.php` → `PurchaseController.php` → `OrderDraftController.php` → `MeleeDiamondController.php` → `JewelleryStockController.php` → `LeadController.php` → `ShopifyWebhookController.php` → `ShopifyController.php` → `SettingsController.php` → `AttributeHubController.php` → `InvoiceController.php` → `JewelleryCalculatorController.php` → remaining cleanup-only controllers.
11. Do not touch low-risk inherited CRUD controllers individually until `BaseResourceController` is stable.

## Test Plan
- Regression-test every refactored controller action against current route signatures and response shapes.
- Add focused tests for:
  - `BaseResourceController` shared destroy path
  - dashboard sold-metric correctness
  - webhook signature rejection for tracking/meta endpoints
  - order create/update/cancel/delete stock consistency
  - chat attachment send/proxy behavior
- For performance-sensitive controllers, verify:
  - pagination is preserved
  - large datasets are aggregated in SQL, not collections
  - long-running sync logic no longer blocks request handlers where extractable without changing endpoint contract

## Assumptions And Defaults
- Existing route middleware in `routes/web.php` remains the primary access-control layer for admin endpoints; controller refactors should not alter route contracts.
- “Preserve response contracts” means flash messages, JSON shapes, redirects, and rendered views stay compatible.
- The 300-line target is achievable for thin controllers, but total feature logic will move into new service/support files. Some domains (`Order`, `Chat`) will still exceed 300 total lines across their subsystem, but not in the controller file itself.
- Public webhook endpoints will be hardened without changing their URLs or payload formats.
- Any unused import or dead-code removal will be confirmed during implementation before deletion.

