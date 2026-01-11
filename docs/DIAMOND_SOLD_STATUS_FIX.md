# Diamond Sold Status Fix - Root Cause Analysis

## Problem

When creating an order with a diamond SKU, the diamond was NOT being marked as "Sold" in the diamonds list. The status remained "IN Stock" even after the order was created.

## Root Cause

The `OrderController::store()` and `OrderController::update()` methods were **missing the critical logic to mark diamonds as sold**.

### Specific Issues:

1. **In `store()` method (Line 88-125):**

    - The order was saved to the database
    - BUT there was NO call to mark the diamond as sold
    - The diamond SKU from the order was never linked to update the diamond's sold status

2. **In `update()` method (Line 131-159):**

    - Similarly, when updating an order with a new diamond SKU
    - NO check was performed to mark the new diamond as sold
    - NO tracking of old vs. new diamond SKU

3. **Missing Import:**
    - The `Diamond` model was not imported at the top of `OrderController`
    - The `DiamondController` method to mark sold was never called

## Solution Implemented

### 1. Added Diamond Model Import

```php
// In OrderController.php (line 7)
use App\Models\Diamond;
```

### 2. Modified `store()` Method

After order is saved, added:

```php
// If diamond SKU is provided, mark it as sold
if (!empty($validated['diamond_sku'])) {
    $diamondController = new DiamondController();
    $soldPrice = $validated['gross_sell'] ?? 0;
    $diamondController->markSoldOutBySku($validated['diamond_sku'], (float) $soldPrice);
}
```

**What this does:**

-   Checks if `diamond_sku` was provided in the order
-   Instantiates `DiamondController` to access the `markSoldOutBySku()` method
-   Passes the SKU and gross_sell price to mark the diamond as sold
-   This automatically sets:
    -   `is_sold_out = 'Sold'`
    -   `sold_out_date` = today
    -   `sold_out_price` = gross_sell value
    -   `duration_days` and `duration_price` calculations
    -   `profit` calculation

### 3. Modified `update()` Method

Before saving, track the old SKU:

```php
$oldDiamondSku = $order->diamond_sku;
$newDiamondSku = $validated['diamond_sku'] ?? null;
```

After saving, check if SKU changed:

```php
// If diamond SKU changed or was newly added, mark the new one as sold
if (!empty($newDiamondSku) && $newDiamondSku !== $oldDiamondSku) {
    $diamondController = new DiamondController();
    $soldPrice = $validated['gross_sell'] ?? 0;
    $diamondController->markSoldOutBySku($newDiamondSku, (float) $soldPrice);
}
```

**What this does:**

-   Only marks as sold if diamond SKU is actually being changed/added
-   Prevents duplicate marking if the same SKU is used
-   If removing a diamond SKU, it doesn't affect the previously sold diamond

## How It Works End-to-End

1. **Admin creates an order** with a diamond SKU (e.g., "DIA-001") and gross_sell price (e.g., $5000)
2. **Order is saved** to the database
3. **`markSoldOutBySku()` is called** with SKU="DIA-001" and soldOutPrice=$5000
4. **Diamond record is updated:**
    - `is_sold_out` = "Sold"
    - `sold_out_price` = $5000
    - `sold_out_date` = today
    - `profit` is calculated automatically
    - `duration_price` is calculated based on purchase_date
5. **Admin checks Diamonds index** → sees diamond with red "Sold" badge

## Files Modified

-   `app/Http/Controllers/OrderController.php`
    -   Added `Diamond` import
    -   Modified `store()` method (lines 88-131)
    -   Modified `update()` method (lines 135-188)

## Testing

To verify the fix works:

1. Create a diamond with SKU "TEST-001" and purchase_price "$1000"
2. Create an order with diamond_sku "TEST-001" and gross_sell "$5000"
3. Go to diamonds index → Should see "TEST-001" with "Sold" status badge in red
4. Diamond details should show:
    - `is_sold_out` = "Sold"
    - `sold_out_price` = 5000
    - `profit` = calculated (5000 - 1000 - shipping)
