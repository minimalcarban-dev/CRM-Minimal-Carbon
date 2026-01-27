# Implementation Plan: Party Categories & Invoice Images

## Executive Summary

This document outlines the implementation plan for adding party categories and invoice image upload functionality across three modules: Gold Tracking, Purchase Tracking (Diamond), and Office Expense. The implementation follows Laravel best practices and production-grade architecture principles.

**Key Technology**: Invoice images will be uploaded to **Cloudinary** (same as Order and Stock List modules), ensuring consistent file handling across the application.

---

## 1. Party Category System

### 1.1 Database Changes

#### Migration: `add_category_to_parties_table.php`

- **Field**: `category` (ENUM or VARCHAR)
- **Type**: `enum('gold_metal', 'jewelry_mfg', 'diamond_gemstone', 'banks', 'in_person')` OR `varchar(50)` with index
- **Nullable**: NO (default value required for existing records)
- **Index**: YES (for performance on filtered queries)

**Decision Point**:

- **Option A (ENUM)**: Type-safe, database-enforced, but harder to extend
- **Option B (VARCHAR with CONSTANTS)**: Flexible, easy to extend, Laravel-friendly
- **Recommendation**: **Option B** - Use VARCHAR(50) with model constants for future flexibility

#### Migration Strategy

1. Add `category` column as nullable initially
2. Set default value for existing parties (e.g., 'in_person' or NULL)
3. Update existing records via seeder/migration
4. Make column NOT NULL after data migration
5. Add index on `category` column

### 1.2 Model Updates

#### `app/Models/Party.php`

```php
// Add constants for categories
public const CATEGORY_GOLD_METAL = 'gold_metal';
public const CATEGORY_JEWELRY_MFG = 'jewelry_mfg';
public const CATEGORY_DIAMOND_GEMSTONE = 'diamond_gemstone';
public const CATEGORY_BANKS = 'banks';
public const CATEGORY_IN_PERSON = 'in_person';

public const CATEGORIES = [
    self::CATEGORY_GOLD_METAL => 'Gold Metal',
    self::CATEGORY_JEWELRY_MFG => 'Jewelry Mfg.',
    self::CATEGORY_DIAMOND_GEMSTONE => 'Diamond & Gemstone',
    self::CATEGORY_BANKS => 'Banks',
    self::CATEGORY_IN_PERSON => 'In Person',
];

// Add to $fillable
protected $fillable = [
    // ... existing fields
    'category',
];

// Add scope for filtering by category
public function scopeByCategory($query, $category)
{
    return $query->where('category', $category);
}

public function scopeByCategories($query, array $categories)
{
    return $query->whereIn('category', $categories);
}
```

### 1.3 Controller Updates

#### `app/Http/Controllers/PartyController.php`

- Add `category` to validation rules in `store()` and `update()`
- Add validation: `'category' => 'required|in:' . implode(',', array_keys(Party::CATEGORIES))`
- Update search to optionally filter by category

### 1.4 View Updates

#### `resources/views/parties/_form.blade.php`

- Add category dropdown field in "Personal Details" or "Tax Details" section
- Use select with Party::CATEGORIES for options
- Make it required field

---

## 2. Category-Based Party Filtering

### 2.1 Gold Tracking Module - Supplier Details

#### Current State

- Uses text input: `supplier_name` (free text)
- No party relationship

#### Required Changes

- **Option A**: Keep text input but add party selector dropdown (filtered by 'gold_metal')
- **Option B**: Replace text input with party dropdown (filtered by 'gold_metal')
- **Recommendation**: **Option B** - Use party dropdown for data consistency

#### Implementation

1. **Controller**: `app/Http/Controllers/GoldTrackingController.php`
    - In `createPurchase()`: Load parties filtered by category

    ```php
    $suppliers = Party::byCategory(Party::CATEGORY_GOLD_METAL)
        ->orderBy('name')
        ->get();
    ```

    - Pass to view: `compact('suppliers')`

2. **View**: `resources/views/gold-tracking/purchase-create.blade.php`
    - Replace text input with select dropdown
    - Add "Add New Party" link/button (opens modal or redirects)
    - Include party details auto-fill on selection (mobile, address, etc.)

3. **Validation**: Update validation to accept `party_id` instead of `supplier_name`
    - Store `party_id` in `gold_purchases` table
    - OR keep `supplier_name` but link to party via foreign key

**Database Decision**:

- **Option A**: Add `party_id` foreign key to `gold_purchases` table
- **Option B**: Keep `supplier_name` as text, add optional `party_id`
- **Recommendation**: **Option B** - Maintain backward compatibility, allow manual entry

### 2.2 Purchase Tracking Module - Payment & Party Info

#### Current State

- Uses text input: `party_name` (free text)
- No party relationship

#### Required Changes

- Filter parties by category: `'diamond_gemstone'`
- Similar implementation as Gold Tracking

#### Implementation

1. **Controller**: `app/Http/Controllers/PurchaseController.php`
    - In `create()`: Load parties filtered by category

    ```php
    $parties = Party::byCategory(Party::CATEGORY_DIAMOND_GEMSTONE)
        ->orderBy('name')
        ->get();
    ```

2. **View**: `resources/views/purchases/create.blade.php`
    - Replace text input with select dropdown
    - Filtered to Diamond & Gemstone category only

### 2.3 Office Expense Module - Paid To / Received From

#### Current State

- Uses text input: `paid_to_received_from` (optional)
- No party relationship

#### Required Changes

- Filter parties by categories: `['banks', 'in_person']`
- Make field **REQUIRED** (currently optional)
- Make `title` and `category` **OPTIONAL** (currently required)

#### Implementation

1. **Controller**: `app/Http/Controllers/ExpenseController.php`
    - In `create()`: Load parties filtered by categories

    ```php
    $parties = Party::byCategories([
        Party::CATEGORY_BANKS,
        Party::CATEGORY_IN_PERSON
    ])->orderBy('name')->get();
    ```

2. **View**: `resources/views/expenses/create.blade.php`
    - Replace text input with select dropdown
    - Add "Add New Party" option
    - Make field required (add `required` attribute and validation)

3. **Validation Updates**:
    ```php
    // In store() and update() methods
    'title' => 'nullable|string|max:255',  // Changed from required
    'category' => 'nullable|string|max:100',  // Changed from required
    'paid_to_received_from' => 'required|string|max:255',  // Changed from nullable
    ```

**Edge Case**: What if user selects a party but also wants to enter custom text?

- **Solution**: Allow both dropdown selection OR manual text entry
- Use radio toggle: "Select Party" vs "Enter Manually"
- If party selected, auto-fill the text field but allow override

---

## 3. Invoice Image Upload (Cloudinary Integration)

### 3.1 Database Changes

#### Migration: `add_invoice_image_to_purchases_table.php`

- **Field**: `invoice_image` (JSON or TEXT)
- **Nullable**: YES
- **Purpose**: Store Cloudinary file metadata (JSON format) or URL string
- **Recommended**: JSON to store metadata (url, public_id, name, format, size, resource_type, uploaded_at)

#### Migration: `add_invoice_image_to_gold_purchases_table.php`

- Same as above for `gold_purchases` table

#### Migration: `add_invoice_image_to_expenses_table.php`

- Same as above for `expenses` table

**Database Schema**:

```php
$table->json('invoice_image')->nullable(); // Store full metadata
// OR simpler: $table->string('invoice_image')->nullable(); // Store just URL
```

### 3.2 Cloudinary Configuration

#### File Storage Strategy (Cloudinary)

- **Cloudinary Folders**:
    - Purchases: `invoices/purchases/`
    - Gold Purchases: `invoices/gold-purchases/`
    - Expenses: `invoices/expenses/`
- **Naming Convention**: `{folder}/{timestamp}_{uniqueId}`
    - Example: `invoices/purchases/1706263822_abc123def`
    - Cloudinary handles extension automatically

#### File Validation

- **Allowed Types**: `image/jpeg, image/png, image/jpg, application/pdf`
- **Max Size**: 5MB (configurable)
- **Validation Rule**: `'invoice_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120'`

#### Cloudinary Setup

- Uses existing Cloudinary configuration from `config/cloudinary.php`
- Environment variables: `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`
- Already configured in the project (used in OrderController and CompanyController)

### 3.3 Model Updates

#### `app/Models/Purchase.php`

```php
protected $fillable = [
    // ... existing fields
    'invoice_image',
];

protected $casts = [
    // ... existing casts
    'invoice_image' => 'array', // If using JSON column
];

// Add accessor for URL
public function getInvoiceImageUrlAttribute(): ?string
{
    if (!$this->invoice_image) {
        return null;
    }

    // If stored as JSON (recommended)
    if (is_array($this->invoice_image)) {
        return $this->invoice_image['url'] ?? null;
    }

    // If stored as string URL
    return $this->invoice_image;
}

// Add accessor for public_id (for deletion)
public function getInvoiceImagePublicIdAttribute(): ?string
{
    if (!$this->invoice_image || !is_array($this->invoice_image)) {
        return null;
    }
    return $this->invoice_image['public_id'] ?? null;
}

// Check if invoice is PDF
public function isInvoicePdf(): bool
{
    if (!$this->invoice_image || !is_array($this->invoice_image)) {
        return false;
    }
    return ($this->invoice_image['resource_type'] ?? null) === 'raw';
}
```

#### `app/Models/GoldPurchase.php`

- Same updates as Purchase model

#### `app/Models/Expense.php`

- Same updates as Expense model

### 3.4 Controller Updates

#### `app/Http/Controllers/PurchaseController.php`

```php
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    private $cloudinary;

    public function __construct()
    {
        // Initialize Cloudinary (same pattern as OrderController)
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // ... existing rules
            'invoice_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        // Handle file upload to Cloudinary
        if ($request->hasFile('invoice_image')) {
            $validated['invoice_image'] = $this->uploadInvoiceToCloudinary(
                $request->file('invoice_image'),
                'invoices/purchases'
            );
        }

        // ... rest of store logic
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            // ... existing rules
            'invoice_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        // Handle file upload to Cloudinary
        if ($request->hasFile('invoice_image')) {
            // Delete old file from Cloudinary if exists
            if ($purchase->invoice_image && is_array($purchase->invoice_image)) {
                $this->deleteInvoiceFromCloudinary(
                    $purchase->invoice_image['public_id'] ?? null,
                    $purchase->invoice_image['resource_type'] ?? 'image'
                );
            }

            // Upload new file
            $validated['invoice_image'] = $this->uploadInvoiceToCloudinary(
                $request->file('invoice_image'),
                'invoices/purchases'
            );
        }

        // ... rest of update logic
    }

    /**
     * Upload invoice file to Cloudinary
     */
    private function uploadInvoiceToCloudinary($file, string $folder): ?array
    {
        try {
            if (!$file->isValid()) {
                Log::error("Invalid invoice file upload: {$file->getClientOriginalName()}");
                return null;
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $timestamp = time();
            $uniqueId = uniqid();

            // Create unique public_id
            $publicId = "{$folder}/{$timestamp}_{$uniqueId}";

            // Determine if PDF or image
            $isPdf = strtolower($extension) === 'pdf';
            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);

            // Upload options
            $uploadOptions = [
                'public_id' => $publicId,
                'folder' => $folder,
            ];

            Log::info("Uploading invoice to Cloudinary", [
                'file' => $file->getClientOriginalName(),
                'type' => $isPdf ? 'PDF' : 'Image',
                'size' => $file->getSize()
            ]);

            // Upload using Cloudinary Upload API
            $uploadApi = $this->cloudinary->uploadApi();

            if ($isPdf) {
                // For PDFs - use raw resource type
                $uploadOptions['resource_type'] = 'raw';
                $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);
            } else {
                // For images - use optimization transformations
                $uploadOptions['transformation'] = [
                    'quality' => 'auto:good',
                    'fetch_format' => 'auto'
                ];
                $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);
            }

            // Store file information (same structure as OrderController)
            $fileInfo = [
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'name' => $originalName . '.' . $extension,
                'format' => $extension,
                'size' => $file->getSize(),
                'resource_type' => $isPdf ? 'raw' : 'image',
                'uploaded_at' => now()->toDateTimeString(),
            ];

            Log::info("Successfully uploaded invoice to Cloudinary", [
                'file' => $originalName,
                'url' => $fileInfo['url'],
                'public_id' => $result['public_id']
            ]);

            return $fileInfo;

        } catch (\Exception $e) {
            Log::error('Cloudinary invoice upload failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Delete invoice file from Cloudinary
     */
    private function deleteInvoiceFromCloudinary(?string $publicId, string $resourceType = 'image'): bool
    {
        if (!$publicId) {
            return false;
        }

        try {
            $uploadApi = $this->cloudinary->uploadApi();
            $uploadApi->destroy($publicId, ['resource_type' => $resourceType]);

            Log::info("Invoice deleted from Cloudinary", [
                'public_id' => $publicId,
                'resource_type' => $resourceType
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete invoice from Cloudinary', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete purchase and its invoice from Cloudinary
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            // Delete invoice from Cloudinary if exists
            if ($purchase->invoice_image && is_array($purchase->invoice_image)) {
                $this->deleteInvoiceFromCloudinary(
                    $purchase->invoice_image['public_id'] ?? null,
                    $purchase->invoice_image['resource_type'] ?? 'image'
                );
            }

            // Delete linked expense if exists
            if ($purchase->expense_id) {
                Expense::where('id', $purchase->expense_id)->delete();
            }

            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase and invoice deleted successfully!');
    }
}
```

#### `app/Http/Controllers/GoldTrackingController.php`

- Add Cloudinary initialization in constructor (same pattern)
- Add `uploadInvoiceToCloudinary()` method (folder: `'invoices/gold-purchases'`)
- Add `deleteInvoiceFromCloudinary()` method
- Update `storePurchase()` and `updatePurchase()` methods
- Update `destroyPurchase()` to delete invoice from Cloudinary

#### `app/Http/Controllers/ExpenseController.php`

- Add Cloudinary initialization in constructor (same pattern)
- Add `uploadInvoiceToCloudinary()` method (folder: `'invoices/expenses'`)
- Add `deleteInvoiceFromCloudinary()` method
- Update `store()` and `update()` methods
- Update `destroy()` to delete invoice from Cloudinary

### 3.5 View Updates

#### Purchase Create/Edit Forms

- Add file input field: `<input type="file" name="invoice_image" accept="image/*,application/pdf">`
- Add preview for existing invoice (if image, show thumbnail; if PDF, show download link)
- Add delete option for existing invoice (removes from Cloudinary)
- Display invoice using Cloudinary URL from JSON metadata

**Example View Code**:

```blade
@if($purchase->invoice_image && is_array($purchase->invoice_image))
    <div class="invoice-preview">
        @if(($purchase->invoice_image['resource_type'] ?? null) === 'raw')
            {{-- PDF --}}
            <a href="{{ $purchase->invoice_image['url'] }}" target="_blank" class="btn btn-sm btn-primary">
                <i class="bi bi-file-pdf"></i> View Invoice PDF
            </a>
        @else
            {{-- Image --}}
            <img src="{{ $purchase->invoice_image['url'] }}"
                 alt="Invoice"
                 class="img-thumbnail"
                 style="max-width: 300px; max-height: 300px;">
        @endif

        <form action="{{ route('purchases.delete-invoice', $purchase) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"
                    onclick="return confirm('Delete this invoice?')">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
    </div>
@endif
```

#### Gold Purchase Create/Edit Forms

- Same as above (use `$goldPurchase` instead of `$purchase`)

#### Expense Create/Edit Forms

- Same as above (use `$expense` instead of `$purchase`)

**Note**:

- Can reuse existing `<x-cloudinary-image>` component for image display
- For PDFs, use direct link to Cloudinary URL
- Component available at `resources/views/components/cloudinary-image.blade.php`

### 3.6 File Cleanup & Deletion

#### Soft Delete Handling

- When purchase/expense is soft deleted, keep file in Cloudinary (for audit trail)
- When permanently deleted, delete file from Cloudinary using `public_id`
- Cloudinary handles file cleanup automatically (no manual cleanup needed)

#### Deletion Pattern

```php
// In destroy() methods, before deleting record:
if ($model->invoice_image && is_array($model->invoice_image)) {
    $this->deleteInvoiceFromCloudinary(
        $model->invoice_image['public_id'] ?? null,
        $model->invoice_image['resource_type'] ?? 'image'
    );
}
```

#### Cloudinary Benefits

- No local storage management required
- Automatic CDN delivery
- Image optimization built-in
- PDF support via raw resource type
- Secure URLs with HTTPS
- No disk space concerns

---

## 4. Data Migration & Backward Compatibility

### 4.1 Existing Parties

- Set default category: `'in_person'` for all existing parties
- OR: Set to NULL and require user to assign category on next edit

### 4.2 Existing Purchases/Gold Purchases/Expenses

- Keep existing `supplier_name` / `party_name` / `paid_to_received_from` as-is
- New entries use party dropdown
- Old entries remain text-based (no breaking changes)

---

## 5. Edge Cases & Risk Mitigation

### 5.1 Party Category Changes

- **Risk**: User changes party category, breaking existing filters
- **Mitigation**:
    - Show warning when changing category
    - Check if party is used in other modules before allowing change
    - OR: Allow multiple categories per party (future enhancement)

### 5.2 File Upload Failures

- **Risk**: Cloudinary upload fails mid-transaction
- **Mitigation**:
    - Use database transactions
    - Upload file AFTER database record created (or handle rollback)
    - Wrap Cloudinary upload in try-catch
    - Log errors for debugging
    - Return null on failure, allow record creation without invoice
    - If upload fails after DB commit, user can retry upload via edit

### 5.3 Cloudinary Quota/API Limits

- **Risk**: Cloudinary API rate limits or quota exceeded
- **Mitigation**:
    - Cloudinary handles scaling automatically
    - Monitor Cloudinary dashboard for usage
    - Implement retry logic for transient failures
    - File size limits already enforced (5MB max)
    - Cloudinary free tier: 25GB storage, 25GB bandwidth/month

### 5.4 Missing Party in Dropdown

- **Risk**: User needs to add party but form doesn't allow
- **Mitigation**:
    - Add "Add New Party" button/link
    - Open modal or redirect to party create page
    - Return to form with new party pre-selected

### 5.5 Invoice Image Deletion

- **Risk**: Accidental deletion of invoice image
- **Mitigation**:
    - Add confirmation dialog
    - Keep file even after "deletion" (soft delete)
    - Add restore functionality

---

## 6. Testing Checklist

### 6.1 Party Category

- [ ] Create party with each category
- [ ] Edit party category
- [ ] Search parties by category
- [ ] Filter parties in each module correctly

### 6.2 Gold Tracking

- [ ] Only 'gold_metal' parties show in supplier dropdown
- [ ] Can create purchase with selected party
- [ ] Can still enter manual supplier name (if Option B chosen)
- [ ] Party details auto-fill on selection

### 6.3 Purchase Tracking

- [ ] Only 'diamond_gemstone' parties show in party dropdown
- [ ] Can create purchase with selected party
- [ ] Validation works correctly

### 6.4 Office Expense

- [ ] Only 'banks' and 'in_person' parties show in dropdown
- [ ] `paid_to_received_from` is required
- [ ] `title` and `category` are optional
- [ ] Validation works correctly

### 6.5 Invoice Images (Cloudinary)

- [ ] Upload image for purchase (JPEG/PNG)
- [ ] Upload PDF for purchase
- [ ] View uploaded invoice (secure URL works)
- [ ] Delete invoice image (removes from Cloudinary)
- [ ] File persists after soft delete (stays in Cloudinary)
- [ ] File deleted from Cloudinary after permanent delete
- [ ] File size validation works (5MB limit)
- [ ] File type validation works (images + PDF)
- [ ] Cloudinary upload error handling
- [ ] Cloudinary deletion error handling
- [ ] JSON metadata stored correctly
- [ ] Accessor methods work (getInvoiceImageUrlAttribute)

---

## 7. Implementation Order

### Phase 1: Party Category System

1. Create migration for `category` column
2. Update Party model with constants and scopes
3. Update PartyController validation
4. Update party form view
5. Migrate existing data
6. Test party CRUD with categories

### Phase 2: Category-Based Filtering

1. Update Gold Tracking (supplier dropdown)
2. Update Purchase Tracking (party dropdown)
3. Update Office Expense (paid_to dropdown + validation changes)
4. Test filtering in each module

### Phase 3: Invoice Image Upload

1. Create migrations for `invoice_image` columns
2. Update models with fillable and accessors
3. Update controllers with file upload logic
4. Update views with file inputs
5. Test upload, view, delete functionality

### Phase 4: Polish & Edge Cases

1. Add "Add New Party" functionality in forms
2. Add file cleanup jobs
3. Add storage monitoring
4. Performance testing
5. Security audit

---

## 8. Security Considerations

### 8.1 File Upload Security

- Validate file types (MIME type, not just extension)
- Cloudinary provides built-in security scanning
- Limit file size (5MB max)
- Cloudinary sanitizes file names automatically
- Files stored in Cloudinary (secure cloud storage)
- Use secure URLs (HTTPS) from Cloudinary
- Public IDs are unique and non-guessable

### 8.2 Access Control

- Only authenticated admins can upload/view invoices
- Add authorization checks in controllers
- Protect storage routes

### 8.3 Data Integrity

- Use database transactions for file + record operations
- Foreign key constraints for party relationships
- Validation at both client and server side

---

## 9. Performance Considerations

### 9.1 Database Indexes

- Index on `parties.category` column
- Index on foreign keys (`party_id` if added)

### 9.2 File Storage (Cloudinary)

- Cloudinary provides automatic CDN delivery
- Implement lazy loading for images in views
- Cloudinary automatically compresses/optimizes images on upload
- Transformations available on-the-fly (thumbnails, resizing)
- No local storage overhead

### 9.3 Query Optimization

- Eager load parties when needed
- Use scopes for filtered queries
- Cache category lists if needed

---

## 10. Future Enhancements

1. **Multiple Categories per Party**: Allow party to belong to multiple categories
2. **Category Permissions**: Restrict which admins can create parties in certain categories
3. **Invoice OCR**: Extract data from invoice images using Cloudinary OCR add-on or external service
4. **Bulk Upload**: Allow multiple invoice images per transaction (store as JSON array)
5. **Image Thumbnails**: Use Cloudinary transformations for thumbnails (already available)
6. **Party Import/Export**: CSV import/export with categories
7. **Cloudinary Transformations**: Add on-the-fly image transformations (resize, crop, watermark)
8. **Invoice Preview**: Generate PDF previews for images using Cloudinary

---

## 11. Rollback Plan

If issues arise:

1. **Party Category**: Can be made nullable, existing functionality unaffected
2. **Filtering**: Can revert to text inputs, keep party dropdown as optional
3. **Invoice Images**: Can be made optional, Cloudinary files can be manually deleted via dashboard or API

---

## 12. Documentation Updates

After implementation:

1. Update API documentation (if applicable)
2. Update user manual/guide
3. Add inline code comments
4. Update database schema documentation

---

## Approval Required

**Please review and approve before implementation:**

- [ ] Database schema changes
- [ ] Model architecture
- [ ] File storage strategy
- [ ] Validation rules
- [ ] Edge case handling
- [ ] Security measures

**Questions for Clarification:**

1. Should parties be restricted to single category or allow multiple?
2. Should we keep text input as fallback or force party selection?
3. What is the file retention policy for invoice images?
4. Should invoice images be required or optional?

---

**Document Version**: 1.0  
**Created**: 2026-01-26  
**Author**: Senior Laravel Architect  
**Status**: Pending Approval
