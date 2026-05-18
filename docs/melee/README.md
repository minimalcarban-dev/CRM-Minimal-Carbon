# Melee Diamond Inventory System

> **Sprint coverage:** Sprint 1–6 complete  
> **Last updated:** 2026-05-18  
> **Auth guard:** `admin` (uses `Admin` model — NOT `User`)

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Write Path Explanation](#2-write-path-explanation)
3. [Permissions System](#3-permissions-system)
4. [How to Run the Audit](#4-how-to-run-the-audit)
5. [Known Drift Issue & Fix Plan](#5-known-drift-issue--fix-plan)

---

## 1. Architecture Overview

### Models

| Model | Table | Notes |
|-------|-------|-------|
| `MeleeCategory` | `melee_categories` | Groups diamonds by type (lab grown / natural), cut, colour |
| `MeleeDiamond` | `melee_diamonds` | Single lot row; status auto-computed in `saving()` boot hook |
| `MeleeTransaction` | `melee_transactions` | Immutable ledger — every stock movement is recorded here |

### Model relationships

```
MeleeCategory
    └── hasMany MeleeDiamond
            └── hasMany MeleeTransaction
```

### `MeleeDiamond` boot hook (`saving`)

Runs on every `INSERT` / `UPDATE` before the query executes:

```
sold_pieces  = max(0, total_pieces - available_pieces)
total_price  = available_carat_weight × purchase_price_per_ct
status       = 'out_of_stock'  if available_pieces ≤ 0
             = 'low_stock'     if available_pieces ≤ low_stock_threshold
             = 'in_stock'      otherwise
```

### Service

`App\Services\MeleeStockService` is the **single authoritative write path** for all stock mutations. Controllers delegate to it; nothing writes directly to `MeleeDiamond` or `MeleeTransaction` outside the service.

### Observer & Events (Sprint 6)

`App\Observers\MeleeObserver` is registered in `AppServiceProvider::boot()` and dispatches:

| Eloquent lifecycle | Event dispatched | Condition |
|--------------------|-----------------|-----------|
| `created` | `App\Events\MeleeCreated` | Always |
| `updated` | `App\Events\MeleeStatusChanged` | **Only if `status` changed** |
| `deleted` | `App\Events\MeleeDeleted` | Always (soft or hard) |

> **Critical test note:** `Event::fake()` with no arguments intercepts Eloquent internal model events and prevents observers from running. Always scope it: `Event::fake([SpecificEvent::class])`.

### Low-stock notifications

`MeleeStockService::notifyLowStockIfNeeded()` sends `MeleeLowStockNotification` to all super-admins after each stock deduction. This is **separate** from the Observer's `MeleeStatusChanged` event — it is notification-delivery, not status-event propagation.

### Layer diagram

```
HTTP Request
    │
    ▼
Controller  ──►  FormRequest (validate + authorize via Policy)
    │
    ▼
MeleeStockService  ──►  MeleeDiamond (lockForUpdate)
    │                       │
    │                       └── MeleeDiamond saving() boot hook
    │                               (sold_pieces, total_price, status)
    │
    ├──►  MeleeTransaction::insert()  (bulk ledger write)
    │
    └──►  notifyLowStockIfNeeded()
              └──►  Notification::send() → MeleeLowStockNotification

MeleeDiamond::observe(MeleeObserver)
    ├── created  →  MeleeCreated::dispatch()
    ├── updated  →  MeleeStatusChanged::dispatch()  [if status changed]
    └── deleted  →  MeleeDeleted::dispatch()
```

---

## 2. Write Path Explanation

All stock mutations flow through **one entry point**: `MeleeStockService`.

### Entry points (public API)

| Method | Caller | Description |
|--------|--------|-------------|
| `create(array $validated)` | `MeleeDiamondController::addShape` | Creates a new melee lot |
| `update(MeleeDiamond, array)` | `MeleeDiamondController::update` | Updates lot metadata |
| `delete(MeleeDiamond)` | `MeleeDiamondController::destroy` | Soft-deletes lot + hard-deletes transactions |
| `deductForOrder(int $orderId, array $entries)` | Order placement flow | Deducts stock; idempotent |
| `returnForOrder(int $orderId, array $entries)` | Order cancellation / deletion | Returns stock; idempotent |
| `adjustForOrderDiff(int, array $old, array $new)` | Order update flow | Net-diff only — avoids double transactions |
| `recordManualTransaction(array $payload)` | `MeleeDiamondController::transaction` | Manual IN / OUT with safety check |
| `createCategory / updateCategory / deleteCategory` | `MeleeCategoryController` | Category CRUD |

### Idempotency guards

`deductForOrder` and `returnForOrder` each check the ledger for existing transactions before acting:

```php
// deductForOrder — skip if order already has 'out' transactions
$existingOutCount = MeleeTransaction::where('reference_type', 'order')
    ->where('reference_id', $orderId)
    ->where('transaction_type', 'out')
    ->count();

if ($existingOutCount > 0) { return ['success' => true, 'message' => 'Already deducted']; }
```

This prevents double-deductions on retries or duplicate webhook calls.

### `adjustForOrderDiff` — net-diff algorithm

Instead of returning all old entries then re-deducting all new entries (which creates 2N transactions), the service computes the **net delta per diamond** and creates only one transaction per diamond:

```
delta = new_pieces - old_pieces

delta > 0  →  'out' transaction  (need more stock)
delta < 0  →  'in'  transaction  (return excess)
delta = 0  →  skip entirely
```

### Row locking

All mutating methods acquire a row-level lock inside a DB transaction:

```php
MeleeDiamond::whereIn('id', $ids)->lockForUpdate()->get()
```

This prevents concurrent over-selling under high load.

### `saveQuietly()` in `create()`

After `MeleeDiamond::create()`, the service calls `$melee->saveQuietly()` for the legacy-field sync step. This **bypasses the observer** intentionally — the `MeleeCreated` event was already fired by the `create()` call; we do not want a second `updated` event for the internal sync.

---

## 3. Permissions System

### Policies registered

| Policy | Model | Registered in |
|--------|-------|---------------|
| `MeleeDiamondPolicy` | `MeleeDiamond` | `AppServiceProvider::boot()` |
| `MeleeCategoryPolicy` | `MeleeCategory` | `AppServiceProvider::boot()` |

### Gate actions — MeleeDiamondPolicy

| Gate action | Permission string | Used by |
|-------------|------------------|---------|
| `viewAny` | `melee.view` | `index`, `search`, `history` routes |
| `view` | `melee.view` | `getStock` route |
| `create` | `melee.create` | `addShape` route |
| `update` | `melee.edit` | `update` route |
| `delete` | `melee.delete` | `destroy` route |

### Super-admin bypass

`Admin::isSuperAdmin()` returns `true` for the god-admin account. Laravel's `Gate::before()` hook returns `true` for all non-strict policy checks when `isSuperAdmin()` is true — so super-admins **always pass** every policy without needing explicit permissions assigned.

### How permissions are checked

Form Requests use `$this->authorize()` which calls the Gate, which resolves the registered Policy:

```php
// StoreMeleeRequest
public function authorize(): bool
{
    return $this->user('admin')->can('create', MeleeDiamond::class);
}
```

A regular admin with no permissions → `403 Forbidden`.  
A super-admin → passes automatically.

---

## 4. How to Run the Audit

### Manual run

```bash
php artisan melee:audit
```

The command is **strictly read-only** — it will never write, update, or delete any data.

### Schedule

The audit runs automatically every day at **06:00** (server time):

```php
// routes/console.php
Schedule::command('melee:audit')->dailyAt('06:00')->withoutOverlapping();
```

### Audit checks (8 total)

| # | Check | Flag | Meaning |
|---|-------|------|---------|
| 1 | **Summary table** | — | Total diamonds, transactions, orphaned records, negative stock, updated today, total value |
| 2 | **Duplicate transactions** | 🔴 | Same order × diamond × type × pieces appears more than once |
| 3 | **Paired IN+OUT per order** | ⚠️ | Both a deduction and a return exist for the same order-diamond pair |
| 4 | **Non-cancelled orders with returns** | ⚠️ | Active orders that have a `transaction_type='in'` — may indicate premature cancellation |
| 5 | **Stock drift** | ⚠️ | `DB total_pieces` ≠ ledger sum; `DB available_pieces` ≠ ledger available |
| 6 | **Zero purchase price** | ⚠️ | `purchase_price_per_ct = 0` — inventory value will be mis-stated |
| 7 | **Empty categories** | ⚠️ | Category exists but has no diamonds assigned |
| 8 | **Recent transactions (24 h)** | 🕐 | Informational — count of activity in the last day |

### Exit codes

| Code | Meaning |
|------|---------|
| `0` | Audit completed successfully (findings may still exist) |
| Non-zero | Command itself errored — investigate |

> The command always exits `0` even when findings exist. Findings are warnings, not fatal errors. Pipe output to a log file for daily archival:
> ```bash
> php artisan melee:audit >> storage/logs/melee-audit.log
> ```

---

## 5. Known Drift Issue & Fix Plan

> **Status:** Documented — fix deferred post-Sprint 6. Do NOT attempt automated correction without a database backup.

### What is drift?

Stock drift occurs when `melee_diamonds.total_pieces` (or `available_pieces`) does not match the sum computed from the `melee_transactions` ledger.

### Current production drift (as of 2026-05-18)

**11 diamonds with drift** — all show positive drift (DB higher than ledger), meaning the database was manually inflated before the transaction ledger was introduced:

| Diamond | Total Drift | Available Drift |
|---------|------------|-----------------|
| #2 | +5 | +5 |
| #19 | +1 | +1 |
| #56 | +4 | +4 |
| #61 | +4 | +4 |
| #103 | +30 | +30 |
| #106 | +20 | +20 |
| #112 | +5 | +5 |
| #155 | +5 | +5 |
| #161 | +4 | +4 |
| #165 | +6 | +6 |
| #198 | +38 | +38 |

**Root cause:** These diamonds were managed with direct DB edits before `MeleeStockService` was introduced in Sprint 2. No compensating transactions exist for the difference.

### 20 active orders with return transactions

Orders that are not cancelled but have `transaction_type='in'` (return) entries. These were created by `adjustForOrderDiff()` during order quantity reductions on already-shipped orders. The service correctly records a partial return when an order's melee quantity is decreased at edit time.

**These are not bugs** — they reflect legitimate order edits. The audit flags them as anomalies because their order status did not transition to a cancelled state.

### Planned fix steps (post-Sprint 6)

> **⚠️ DO NOT run these without a full DB backup and stakeholder approval.**

1. **Reconciliation report** — run `php artisan melee:audit` and export to CSV. Confirm all 11 diamonds with the business owner to agree on the correct total.
2. **Correction transactions** — for each drifted diamond, insert a `type='adjustment'` transaction for `-(drift amount)` pieces to bring the ledger in line with the agreed total, OR update `total_pieces` to match the ledger.
3. **Active-order review** — cross-reference the 20 orders against actual fulfilment records. Close or cancel orders that are fully shipped and no longer active.
4. **Re-audit** — run `php artisan melee:audit` after corrections to confirm zero drift.

---

## Appendix — File Map

```
app/
├── Console/Commands/MeleeAudit.php          # Read-only audit command (Sprint 1, hardened Sprint 6)
├── Events/
│   ├── MeleeCreated.php                     # Sprint 6
│   ├── MeleeStatusChanged.php               # Sprint 6
│   └── MeleeDeleted.php                     # Sprint 6
├── Http/
│   ├── Controllers/
│   │   ├── MeleeDiamondController.php
│   │   └── MeleeCategoryController.php
│   └── Requests/
│       ├── StoreMeleeRequest.php            # Sprint 3
│       ├── UpdateMeleeRequest.php           # Sprint 3
│       └── StoreMeleeCategoryRequest.php    # Sprint 3
├── Models/
│   ├── MeleeCategory.php
│   ├── MeleeDiamond.php
│   └── MeleeTransaction.php
├── Observers/
│   └── MeleeObserver.php                   # Sprint 6
├── Policies/
│   ├── MeleeDiamondPolicy.php              # Sprint 3
│   └── MeleeCategoryPolicy.php             # Sprint 3
└── Services/
    └── MeleeStockService.php               # Sprint 2

tests/
├── Feature/Melee/
│   ├── MeleeAuditCommandTest.php           # Sprint 1 (converted to #[Test] Sprint 6)
│   ├── MeleeCharacterizationTest.php       # Sprint 1 (converted to #[Test] Sprint 6)
│   ├── MeleeFormRequestTest.php            # Sprint 3 (converted to #[Test] Sprint 6)
│   ├── MeleeObserverTest.php               # Sprint 6
│   └── MeleeStockServiceTest.php           # Sprint 6
└── Unit/Policies/
    └── MeleeDiamondPolicyTest.php          # Sprint 3 (converted to #[Test] Sprint 6)

resources/views/melee/
├── index.blade.php                         # Sprint 4 — shell + @include
└── partials/                               # Sprint 4 — 8 extracted partials
    ├── _page_header.blade.php
    ├── _stats_bar.blade.php
    ├── _category_panel.blade.php
    ├── _diamond_table.blade.php
    ├── _modal_history.blade.php
    ├── _modal_quick_order.blade.php
    ├── _modal_edit_melee.blade.php
    └── _modal_edit_transaction.blade.php

docs/melee/
└── README.md                               # This file — Sprint 6
```
