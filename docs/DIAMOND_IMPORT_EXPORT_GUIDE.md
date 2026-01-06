# Diamond Import/Export Template

## Import Excel Column Headers

When importing diamonds, your Excel file should have the following columns:

### Required Columns:
- **lot_no** - Unique lot number (can include letters, e.g., L0010078)
- **sku** - Unique SKU identifier (e.g., OM0078)

### Optional Columns (Specifications):
- **material** - Diamond material
- **cut** - Cut grade (e.g., Excellent, Very Good)
- **clarity** - Clarity grade (e.g., VVS1, VS2, IF)
- **color** - Color grade
- **shape** - Diamond shape (e.g., Round, Oval, Princess)
- **measurement** - Dimensions in mm (e.g., 6.5 x 6.3 x 4.1)
- **weight** - Carat weight (numeric)
- **per_ct** - Price per carat (numeric)

### Optional Columns (Pricing):
- **purchase_price** - Purchase/cost price (numeric)
- **price** - Alternative column name for purchase_price (backward compatibility)
- **margin** - Profit margin percentage (numeric)
- **listing_price** - Selling/listing price (numeric)
- **shipping_price** - Shipping cost (numeric, default: 0)

### Optional Columns (Lifecycle):
- **purchase_date** - Date format: YYYY-MM-DD
- **sold_out_date** - Date format: YYYY-MM-DD
- **is_sold_out** - Status: "IN Stock" or "Sold"
- **duration_days** - Days in inventory (numeric)
- **duration_price** - Time-based pricing (numeric)
- **sold_out_price** - Final selling price (numeric)
- **profit** - Profit amount (numeric)
- **sold_out_month** - Format: YYYY-MM

### Optional Columns (Additional):
- **barcode_number** - Custom barcode (auto-generated if not provided)
- **description** - Detailed description
- **note** - Internal notes
- **diamond_type** - Type/category
- **admin_id** - Assign to admin (admin's ID number)

---

## Export Columns

When you export diamonds, the Excel file will contain:

1. ID
2. Lot No
3. SKU
4. Material
5. Cut
6. Clarity
7. Color
8. Shape
9. Measurement
10. Weight
11. Per Ct
12. Purchase Price
13. Margin
14. Listing Price
15. Shipping Price
16. Purchase Date
17. Sold Out Date
18. Status (IN Stock/Sold)
19. Duration Days
20. Duration Price
21. Sold Out Price
22. Profit
23. Sold Out Month
24. Barcode Number
25. Description
26. Note
27. Diamond Type
28. Assigned Admin
29. Assigned By
30. Assigned At
31. Images (comma-separated URLs)
32. Created At
33. Updated At

---

## Sample Import Data

```
lot_no    | sku      | purchase_price | listing_price | shape | cut       | weight | margin | purchase_date
----------|----------|----------------|---------------|-------|-----------|--------|--------|---------------
L0010078  | OM0078   | 3284.00        | 3801.00       | Oval  | Excellent | 1.50   | 30     | 2025-01-15
L0010079  | OM0079   | 2500.00        | 3000.00       | Round | Very Good | 1.20   | 25     | 2025-01-20
1125      | 1125     | 250.00         | 300.00        | Round | Good      | 0.75   | 20     | 2025-02-01
```

---

## Notes:

- **Lot No** is now string type, so you can use alphanumeric values like "L0010078"
- **Purchase Price** is the main cost field (old "price" column still works for backward compatibility)
- **Duration Price** calculation: `Purchase Price + (Purchase Price × 1.5% × months)`
- **Barcode** will be auto-generated if not provided
- **Validation errors** will be reported row by row during import
- Empty rows are automatically skipped
