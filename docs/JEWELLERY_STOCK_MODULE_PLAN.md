# Jewellery Stock Module Plan

## 1) Overview

This document describes a new **Jewellery Stock** module (module name: `jewellery_stocks`) for managing jewelry inventory (rings, earrings, tennis bracelets, etc.) within the CRM. The module will be built using the **Diamond** module as the primary reference (model/controller/views/routes) and will allow admins to:

- Add/edit/delete jewelry stock items
- Assign each item a **SKU**
- Track quantity, pricing, and stock status
- View stock list with filters and status cards
- (Optionally) integrate stock update with orders

The design reuses existing patterns already present in the project (Diamond stock, Meele stock, permissions, import/export, and UI conventions).

---

## 2) Reusable "Skills" (Existing Patterns)

These are the capabilities already implemented in the app that the new module will reuse:

- **Model derived fields / auto-calculation** (e.g., `Diamond::boot()`, `MeleeDiamond::boot()`)
- **List + filter UI** + **pagination** (like `resources/views/diamonds/index.blade.php`)
- **FormRequest validation** (`StoreDiamondRequest`, `UpdateDiamondRequest`)
- **Admin permissions** and middleware (`admin.permission:diamonds.view`, etc.)
- **SKU availability check endpoint** (`diamond.check-sku`)
- **Import/export job pattern** (Excel import/export & status jobs)
- **Stock status / low stock logic** (as in `MeleeDiamond`)

---

## 3) Data Design (Database)

### 3.1 Table: `jewellery_stocks` (suggested)

**Core columns:**

- `id` (PK)
- `sku` (string, unique)
- `type` (enum: `ring`, `earrings`, `tennis_bracelet`, `other`)
- `name` (string)
- `metal_type_id` (FK to `metal_types.id`)
- `ring_size_id` (FK to `ring_sizes.id`, nullable)
- `weight` (decimal)
- `quantity` (int)
- `low_stock_threshold` (int)
- `purchase_price` (decimal)
- `selling_price` (decimal)
- `status` (string / enum: `in_stock`, `low_stock`, `out_of_stock`)
- `description` (text)
- `image_url` (string, optional)
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Derived fields (in model):**

- `status` auto-calculated from `quantity` and `low_stock_threshold`
- (Optional) `is_in_stock` / `low_stock` flag.

---

## 4) Module Components (Files)

### 4.1 Models

- `app/Models/JewelleryStock.php`
  - scoped `fillable`
  - casts for decimals
  - `boot()` with auto status logic like `MeleeDiamond`
  - helper methods: `addStock()`, `deductStock()`
  - relations: `metalType()`, `ringSize()`

### 4.2 Controllers

- `app/Http/Controllers/JewelleryStockController.php`
  - `index()` (filters: SKU, type, metal, status, price range)
  - `create()`, `store()`
  - `edit()`, `update()`
  - `show()` (optional detail view)
  - `destroy()` (soft delete)
  - `checkSkuAvailability()` (for orders)
  - (Optional) `import()` / `export()`

### 4.3 Requests (Validation)

- `app/Http/Requests/StoreJewelleryStockRequest.php`
- `app/Http/Requests/UpdateJewelleryStockRequest.php`

### 4.4 Views (Blade)

Create a new view folder: `resources/views/jewellery-stock/`:

- `index.blade.php` (stock list)
- `create.blade.php`
- `edit.blade.php`
- `show.blade.php` (optional)
- `partials/` (shared form components)

### 4.5 Routes

Add admin routes in `routes/web.php` (like diamonds). Example:

- `GET /admin/jewellery-stock` → index
- `GET /admin/jewellery-stock/create` → create
- `POST /admin/jewellery-stock` → store
- `GET /admin/jewellery-stock/{id}` → show
- `GET /admin/jewellery-stock/{id}/edit` → edit
- `PUT /admin/jewellery-stock/{id}` → update
- `DELETE /admin/jewellery-stock/{id}` → destroy
- `GET /admin/jewellery-stock/check-sku` → checkSkuAvailability

Add middleware: `admin.permission:jewellery_stock.view`, etc.

### 4.6 Permissions

Add these permissions to the seeder(s):

- `jewellery_stock.view`
- `jewellery_stock.create`
- `jewellery_stock.edit`
- `jewellery_stock.delete`
- (Optional) `jewellery_stock.stock` for stock adjustments

---

## 5) UX / Features (Optional Enhancements)

### 5.1 SKU assignment

- Enforce SKU uniqueness with DB unique index + validation.
- Provide auto-generated SKU (ex: `JWL-2026-001`) or allow manual entry.

### 5.2 Stock list UI

- Status cards (Total Items, In Stock, Low Stock, Value)
- Table with columns: SKU, Name, Type, Metal, Qty, Status, Purchase Price, Selling Price, Actions
- Filters + sorting (same pattern as diamond list)

### 5.3 Stock adjustments (advanced)

- Manual stock movement modal (like `meele_transactions`)
- Maintain transaction log table (optional)

### 5.4 Order integration (later)

- Provide endpoint for order forms to validate SKU + available quantity
- Deduct stock when order is created (like meele stock integration)

---

## 6) Verification & Tests

### Manual checks

1. Visit `/admin/jewellery-stock` and confirm list loads.
2. Create an item and confirm SKU + status logic.
3. Edit item and verify updates.
4. Delete soft deletes and confirms record still exists (soft delete).

### Automated tests

- Feature test for create + validation
- Test SKU uniqueness
- Test status calculation when quantity drops below threshold
- Test list filters and SKU check endpoint

---

## 7) Next Steps

1. Confirm module name: **`jewellery_stocks`** (this plan assumes `jewellery_stocks` is the table + route prefix).
2. Decide if you want **transaction logging** (like `meele_transactions`) or just soft quantity changes.
3. Share any existing view you already created (or the path to it) so I can tailor the controller/view data shape; otherwise I will base the UI shapes on the **Diamond module** (`resources/views/diamonds/*`).

If you want, I can now generate the initial migration + model + controller stubs in your repo.
