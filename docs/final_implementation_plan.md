# 💎 Jewellery Pricing Matrix — Final Implementation Plan

## Architecture Summary

| Material | Rate Source | Formula | Colors |
|---|---|---|---|
| **925 Silver** | Custom API → `silver.per_gram` | `base × 0.925` | None (single row) |
| **935 Argentium** | Custom API → `silver.per_gram` | `base × 0.935` | None (single row) |
| **950 Platinum** | `app_settings` (manual, API-ready) | `base × 0.950` | None (single row) |
| **10K Gold** | Custom API → `24k.per_gram` | `base × 0.417` | Yellow / White / Rose |
| **14K Gold** | Custom API → `24k.per_gram` | `base × 0.585` | Yellow / White / Rose |
| **18K Gold** | Custom API → `24k.per_gram` | `base × 0.750` | Yellow / White / Rose |
| **22K Gold** | Custom API → `24k.per_gram` | `base × 0.917` | Yellow / White / Rose |

**Rows: 13 → 7** (Silver no-color × 3, Gold with color\_weights × 4)

***

## Rate Flow Diagram

```
Custom API (custom-gold-api.onrender.com/gold)
├── prices.24k.per_gram  (INR) ──→ × purity% × USD rate → Gold rows
└── prices.silver.per_gram (INR) ──→ × 92.5% × USD rate → 925 Silver
                                 └→ × 93.5% × USD rate → 935 Argentium

app_settings (manual)
└── jewellery_pricing.platinum_950_rate_usd_per_gram ──→ 950 Platinum
    (Future: can plug into API same as silver — just one line change)
```

***

## Files to Change

### 1. New Migration — `color_weights` column + data merge

**File:** `database/migrations/2026_04_27_XXXXXX_refactor_jewellery_stock_pricings.php`

* Add `color_weights` JSON column (nullable) to `jewellery_stock_pricings`
* Migrate existing data: group by `(jewellery_stock_id, material_code)`, merge color rows into `color_weights` JSON
* Drop duplicate color rows, keep one row per karat

### 2. New Migration — seed new app\_settings

**File:** `database/migrations/2026_04_27_XXXXXX_add_platinum_silver_pricing_settings.php`

```php
'jewellery_pricing.platinum_950_rate_usd_per_gram' => '30'  // manual default
// Remove: jewellery_pricing.silver_925_rate_usd_per_gram   (now from API)
```

### 3. `JewelleryMaterialRateService.php`

**Change:** Fetch silver from API, calculate purity-based rates, platinum from settings

```php
// currentRates() returns:
[
    'gold_adjusted_usd_per_gram' => ...,   // existing (24K × markup)
    'silver_base_usd_per_gram'   => ...,   // NEW: from API silver.per_gram
    'silver_925_usd_per_gram'    => ...,   // NEW: base × 0.925
    'silver_935_usd_per_gram'    => ...,   // NEW: base × 0.935
    'platinum_950_usd_per_gram'  => ...,   // NEW: from app_settings (manual)
]
```

> **Platinum future-ready:** `getPlatinumRate()` private method — swap one line to API later.

### 4. `JewelleryPricingService.php`

**Change:** MATERIALS constant + matrix logic

```php
public const MATERIALS = [
    'silver_925'    => ['label' => '925 Silver',     'purity' => 92.5, 'colors' => [null]],
    'silver_935'    => ['label' => '935 Argentium',  'purity' => 93.5, 'colors' => [null]],
    'platinum_950'  => ['label' => '950 Platinum',   'purity' => 95.0, 'colors' => [null]],
    'gold_10k'      => ['label' => '10K Gold',        'purity' => 41.7, 'colors' => ['yellow','white','rose']],
    'gold_14k'      => ['label' => '14K Gold',        'purity' => 58.5, 'colors' => ['yellow','white','rose']],
    'gold_18k'      => ['label' => '18K Gold',        'purity' => 75.0, 'colors' => ['yellow','white','rose']],
    'gold_22k'      => ['label' => '22K Gold',        'purity' => 91.7, 'colors' => ['yellow','white','rose']],
];
```

**`blankVariantMatrix()`** — 7 keys instead of 13\
**`calculateMatrix()`** — rate lookup per material type:

```php
$baseRate = match(true) {
    str_starts_with($code, 'silver_') => $rates['silver_base_usd_per_gram'] * ($purity/100),
    $code === 'platinum_950'          => $rates['platinum_950_usd_per_gram'],
    str_starts_with($code, 'gold_')   => $rates['gold_adjusted_usd_per_gram'] * ($purity/100),
};
```

**`color_weights` handling:**

* Single-color materials (silver, platinum): `net_weight_grams` as before
* Gold: `color_weights = ['yellow' => X, 'white' => Y, 'rose' => Z]` stored in JSON column
* `listing_price` = price calculated using the **heaviest / default color** weight

### 5. `JewelleryStockPricing.php` (Model)

```php
protected $casts = [
    ...
    'color_weights' => 'array',   // NEW
];
```

### 6. `pricing-matrix.blade.php`

**Table columns:**

```
Default | Material | Yellow Wt(g) | White Wt(g) | Rose Wt(g) | Rate/g | Metal | Labor | Stone | Extra | Subtotal | Final
```

* Silver/Platinum rows: Yellow Wt colspan=3 (single input)
* Gold rows: 3 separate weight inputs
* Assumptions bar: Remove "925 Silver / g ($)" manual input, add "Platinum / g ($)" manual input
* Rate status bar: Show silver + platinum rates alongside gold

### 7. `StoreJewelleryStockRequest.php` + `UpdateJewelleryStockRequest.php`

```php
'pricing_variants.*.color_weights'         => 'nullable|array',
'pricing_variants.*.color_weights.yellow'  => 'nullable|numeric|min:0',
'pricing_variants.*.color_weights.white'   => 'nullable|numeric|min:0',
'pricing_variants.*.color_weights.rose'    => 'nullable|numeric|min:0',
// Remove: silver_925_rate_usd_per_gram (no longer manual)
// Add:    platinum_950_rate_usd_per_gram
```

***

## Action Items (Ordered)

* \[ ] 1. Migration: `color_weights` column add + existing 13-row data → 7-row merge
* \[ ] 2. Migration: `platinum_950_rate_usd_per_gram` app\_setting seed, remove `silver_925` setting
* \[ ] 3. `JewelleryMaterialRateService`: silver from API, platinum from settings, platinum API-ready structure
* \[ ] 4. `JewelleryPricingService`: MATERIALS update, `calculateMatrix()` rate logic, `blankVariantMatrix()` 7 rows
* \[ ] 5. `JewelleryStockPricing` model: `color_weights` cast add, label attributes update
* \[ ] 6. `pricing-matrix.blade.php`: New table structure — 7 rows, colspan for silver/platinum, 3 inputs for gold
* \[ ] 7. Form Requests: `color_weights` validation, platinum rate field
* \[ ] 8. Test: Create → Save → Edit → Recalculate flow end-to-end

***

> **Note:** User will update `custom-gold-api.onrender.com` to include `prices.silver.per_gram` before Step 3 is activated. Until then, silver rate gracefully falls back to 0 with a log warning.
