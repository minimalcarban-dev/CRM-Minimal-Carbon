# Diamond Import Excel Sheet Format

## 📋 Excel Template Structure

### Required Columns (MUST HAVE)

| Column Header | Data Type   | Example             | Description                          |
| ------------- | ----------- | ------------------- | ------------------------------------ |
| `lot_no`      | String/Text | `10001` or `L10001` | **REQUIRED** - Unique lot number     |
| `sku`         | String/Text | `DIA-001`           | **REQUIRED** - Unique SKU identifier |

### Optional Columns (Recommended)

| Column Header | Data Type | Example                          | Description            |
| ------------- | --------- | -------------------------------- | ---------------------- |
| `material`    | Text      | `Gold`, `Platinum`, `Silver`     | Material type          |
| `cut`         | Text      | `Excellent`, `Very Good`, `Good` | Diamond cut quality    |
| `clarity`     | Text      | `VVS1`, `VS1`, `SI1`, `IF`       | Diamond clarity grade  |
| `color`       | Text      | `D`, `E`, `F`, `G`, `H`          | Diamond color grade    |
| `shape`       | Text      | `Round`, `Princess`, `Emerald`   | Diamond shape          |
| `measurement` | Text      | `5.2 x 5.2 x 3.1 mm`             | Diamond dimensions     |
| `weight`      | Number    | `1.25`                           | Carat weight (decimal) |
| `per_ct`      | Number    | `5000`                           | Price per carat        |

### Pricing Columns

| Column Header    | Data Type | Example    | Description                |
| ---------------- | --------- | ---------- | -------------------------- |
| `purchase_price` | Number    | `12500.00` | Purchase price of diamond  |
| `margin`         | Number    | `2500.00`  | Profit margin amount       |
| `listing_price`  | Number    | `15000.00` | Listed selling price       |
| `shipping_price` | Number    | `500.00`   | Shipping cost (default: 0) |

### Date & Status Columns

| Column Header    | Data Type | Example                      | Description                      |
| ---------------- | --------- | ---------------------------- | -------------------------------- |
| `purchase_date`  | Date      | `2025-12-08` or `08/12/2025` | Date of purchase                 |
| `sold_out_date`  | Date      | `2025-12-15`                 | Date when sold (if sold)         |
| `is_sold_out`    | Text      | `IN Stock` or `Sold`         | Stock status                     |
| `sold_out_month` | Text      | `2025-12`                    | Month when sold (YYYY-MM format) |
| `sold_out_price` | Number    | `16000.00`                   | Final selling price              |
| `profit`         | Number    | `3500.00`                    | Total profit earned              |

### Duration & Calculation Fields

| Column Header    | Data Type | Example    | Description                     |
| ---------------- | --------- | ---------- | ------------------------------- |
| `duration_days`  | Number    | `45`       | Days held in inventory          |
| `duration_price` | Number    | `13000.00` | Calculated price after duration |

### Additional Info Columns

| Column Header    | Data Type | Example                      | Description                       |
| ---------------- | --------- | ---------------------------- | --------------------------------- |
| `barcode_number` | Text      | `BC-ABC123`                  | Barcode (auto-generated if blank) |
| `description`    | Text      | `Beautiful round diamond...` | Diamond description               |
| `note`           | Text      | `Special handling required`  | Internal notes                    |
| `diamond_type`   | Text      | `Natural`, `Lab-grown`       | Type of diamond                   |
| `admin_id`       | Number    | `1`                          | Admin ID to assign (must exist)   |

---

## 📝 Excel Template Example

### Sheet Name: `Diamonds`

| lot_no | sku     | material | cut       | clarity | color | shape    | weight | per_ct | purchase_price | margin | listing_price | shipping_price | purchase_date | is_sold_out | description       | diamond_type | note                    |
| ------ | ------- | -------- | --------- | ------- | ----- | -------- | ------ | ------ | -------------- | ------ | ------------- | -------------- | ------------- | ----------- | ----------------- | ------------ | ----------------------- |
| 10001  | DIA-001 | Gold     | Excellent | VVS1    | D     | Round    | 1.50   | 8000   | 12000          | 3000   | 15000         | 500            | 2025-12-01    | IN Stock    | Premium diamond   | Natural      | VIP customer            |
| 10002  | DIA-002 | Platinum | Very Good | VS1     | E     | Princess | 1.25   | 7500   | 9375           | 2125   | 11500         | 500            | 2025-12-02    | IN Stock    | Excellent quality | Natural      |                         |
| 10003  | DIA-003 | Gold     | Good      | SI1     | F     | Emerald  | 2.00   | 6000   | 12000          | 2500   | 14500         | 0              | 2025-11-20    | Sold        | Budget friendly   | Lab-grown    | Sold to repeat customer |

---

## ✅ Validation Rules

### MUST FOLLOW:

1. **lot_no** - Must be unique across all diamonds
2. **sku** - Must be unique across all diamonds
3. **Numeric fields** - Must not be negative (prices, weight, etc.)
4. **is_sold_out** - Only `IN Stock` or `Sold` (case-sensitive)
5. **Dates** - Use format: `YYYY-MM-DD` or `DD/MM/YYYY`
6. **admin_id** - If provided, admin must exist in database

### AUTO-GENERATED (Leave Blank):

-   `barcode_number` - System generates if not provided
-   `barcode_image_url` - Generated after import
-   `multi_img_upload` - Upload images separately after import
-   `duration_days` - Calculated automatically
-   `duration_price` - Calculated automatically based on margin rate

---

## 🎯 Import Process

### Step 1: Prepare Excel File

1. Create new Excel file (`.xlsx` or `.xls`)
2. First row MUST contain column headers (exact names as shown above)
3. Data starts from row 2
4. Save the file

### Step 2: Upload via Admin Panel

1. Navigate to Diamonds > Import
2. Select your Excel file
3. Click "Import"
4. System will validate each row

### Step 3: Review Results

-   **Success**: Diamonds created with auto-generated barcodes
-   **Errors**: View error report with row numbers and issues
-   **Partial Success**: Some rows imported, others skipped

---

## ⚠️ Common Errors & Solutions

| Error                       | Cause               | Solution                               |
| --------------------------- | ------------------- | -------------------------------------- |
| "Lot number already exists" | Duplicate `lot_no`  | Use unique lot numbers                 |
| "SKU already exists"        | Duplicate `sku`     | Use unique SKU codes                   |
| "Invalid date format"       | Wrong date format   | Use `YYYY-MM-DD` format                |
| "Admin does not exist"      | Invalid `admin_id`  | Check admin ID or leave blank          |
| "Price must be numeric"     | Text in price field | Use numbers only (no currency symbols) |
| "Weight must be positive"   | Negative number     | Use positive numbers only              |

---

## 📦 Sample Import File

**File Name**: `diamonds_import_sample.xlsx`

**Minimum Required Data** (Simplest import):

```
lot_no  | sku
--------|--------
10001   | DIA-001
10002   | DIA-002
10003   | DIA-003
```

**Recommended Data** (Better for inventory):

```
lot_no | sku     | weight | purchase_price | listing_price | shape | clarity | color | purchase_date | is_sold_out
-------|---------|--------|----------------|---------------|-------|---------|-------|---------------|-------------
10001  | DIA-001 | 1.50   | 12000          | 15000         | Round | VVS1    | D     | 2025-12-01    | IN Stock
10002  | DIA-002 | 1.25   | 9375           | 11500         | Round | VS1     | E     | 2025-12-02    | IN Stock
```

---

## 💡 Pro Tips

1. **Start Small**: Test with 2-3 rows first before importing hundreds
2. **Backup**: Keep original Excel file as backup
3. **Unique Values**: Ensure lot_no and sku are truly unique
4. **Date Format**: Stick to `YYYY-MM-DD` to avoid confusion
5. **No Special Characters**: Avoid special characters in lot_no and sku
6. **Case Sensitivity**: `IN Stock` and `in stock` are different - use exact match
7. **Leave Blank**: If you don't have data for a column, leave it blank (don't use "N/A" or "-")
8. **Numbers Only**: Don't use currency symbols ($, ₹) in price fields

---

## 📊 Excel Template Download

Create a blank Excel file with these exact headers in Row 1:

```
lot_no | sku | material | cut | clarity | color | shape | measurement | weight | per_ct | purchase_price | margin | listing_price | shipping_price | purchase_date | sold_out_date | is_sold_out | duration_days | duration_price | sold_out_price | profit | sold_out_month | barcode_number | description | note | diamond_type | admin_id
```

Then fill data from Row 2 onwards.

---

## 🔧 Environment Configuration

Make sure your `.env` file has:

```env
DIAMOND_BRAND_CODE=100
DIAMOND_MARGIN_RATE=0.05
```

This affects barcode generation and duration price calculations.
