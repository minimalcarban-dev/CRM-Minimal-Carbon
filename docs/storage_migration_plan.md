# Technical Migration Plan: Local + Cloudinary to Cloudflare R2

This document provides a detailed analysis of the current file storage architecture and a comprehensive strategy for migrating all file storage to Cloudflare R2.

## SECTION 1 — SUMMARY OF STORAGE USAGE

The system currently employs a hybrid storage approach:
1.  **Cloudinary:** Used primarily for images and PDFs related to core business entities (Orders, Expenses, Purchases, Company Logos) and Chat attachments. It provides on-the-fly transformations and absolute URL persistence.
2.  **Local Storage (`storage/app/public`):** Used for administrative documents (Aadhar, Bank Passbooks), product images (Diamonds), and temporary system files (Imports, Exports, Barcodes).
3.  **External CDN (Shopify):** Shopify integration stores URLs to Shopify's own CDN.

The goal is to consolidate all internal and user-uploaded storage into **Cloudflare R2**, a cost-effective, S3-compatible object storage solution.

---

## SECTION 2 — CLOUDINARY USAGE

| Location | Usage | Implementation Pattern | Metadata Stored |
| :--- | :--- | :--- | :--- |
| `OrderController` | Order images & PDFs | Cloudinary SDK | Array of secure URLs |
| `ExpenseController` | Invoice images | Cloudinary Facade | JSON: `url`, `public_id`, `format`, `size` |
| `GoldTrackingController`| Purchase invoices | Cloudinary Facade | JSON: `url`, `public_id` |
| `PurchaseController` | Invoice images | Cloudinary Facade | JSON: `url`, `public_id`, `format`, `size` |
| `CompanyController` | Company logos | Cloudinary SDK | Absolute Secure URL |
| `ProcessChatAttachment`| Chat files/images | Cloudinary SDK | Absolute URLs in `path` & `thumbnail_path` |

**Key Findings:**
*   Most controllers use the `CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary` facade.
*   Metadata often includes `public_id` to facilitate deletion.
*   `ProcessChatAttachment` acts as a backend worker to move local files to Cloudinary.

---

## SECTION 3 — LOCAL STORAGE USAGE

Stored under `storage/app/public/` and accessed via `Storage::disk('public')`.

| Directory Path | Entity / Usage | Implementation Pattern |
| :--- | :--- | :--- |
| `admins/` | Admin Documents | `Storage::disk('public')->putFileAs()` |
| `diamonds/` | Diamond Images | `$image->store('diamonds', 'public')` |
| `chat-attachments/` | Temp Chat Files | `Storage::disk('public')->store()` (moved later) |
| `imports/` | Excel/CSV Imports | `Storage::disk('local')` and `public` |
| `exports/` | Generated Reports | `Excel::store(..., 'public')` |
| `public/barcodes/`* | SVG Barcode Files | `file_put_contents` (Direct File System) |
| `packages/` | Package Images | Mixed (URL or Relative Path) |

*\*Stored directly in public folder, bypasses Laravel Storage facade in some instances. Location: `public/barcodes`*

---

## SECTION 4 — DATABASE STORAGE PATTERN

The system uses three distinct patterns to record file locations in the database:

1.  **Absolute URLs:**
    *   Stored when using Cloudinary (e.g., `https://res.cloudinary.com/...`).
    *   Examples: `companies.logo`, `message_attachments.path`, `message_attachments.thumbnail_path`.
2.  **Relative Paths:**
    *   Stored when using Local Storage.
    *   Examples: `admins.aadhar_image` (`admins/abc.jpg`), `admins.bank_passbook_image`.
3.  **Prefixed Relative Paths:**
    *   Stored with the `/storage/` prefix in the DB.
    *   Example: `diamonds.multi_img_upload` ([`"/storage/diamonds/123.jpg"`, ...]).
4.  **Structured JSON Metadata:**
    *   Stores the URL along with provider-specific IDs.
    *   Examples: `purchases.invoice_image`, `expenses.invoice_image`.

---

## SECTION 5 — FRONTEND DISPLAY MAPPING

| Platform | Pattern | Example |
| :--- | :--- | :--- |
| **Blade Views** | Checks prefix or uses helpers | `{{ str_starts_with($path, 'http') ? $path : asset($path) }}` |
| **Vue (MediaGallery)** | Method-based resolution | `if (img.path.startsWith('http')) return img.path; else return '/storage/' + img.path;` |
| **Vue (Chat)** | Direct prefixing | `return "/storage/" + attachment.path;` |
| **PDF Invoices** | Hardcoded logic | `$logoUrl = asset('storage/' . $logo);` |

---

## SECTION 6 — DELETION LOGIC

*   **Cloudinary Deletion:**
    *   Triggered in `update()` or `destroy()` methods.
    *   Uses `Cloudinary::destroy($public_id)`.
    *   `MessageAttachment` uses a model `deleting` boot event to clean up Cloudinary.
*   **Local Deletion:**
    *   Uses `Storage::disk('public')->delete($path)`.
    *   `DiamondController` uses `unlink()` for barcode SVGs.

---

## SECTION 7 — RECOMMENDED CODE CHANGES

### 1. Filesystem Configuration
Update `config/filesystems.php` to include the Cloudflare R2 disk using the S3 driver:
```php
'r2' => [
    'driver' => 's3',
    'key' => env('R2_ACCESS_KEY_ID'),
    'secret' => env('R2_SECRET_ACCESS_KEY'),
    'region' => 'auto',
    'bucket' => env('R2_BUCKET'),
    'url' => env('R2_URL'),
    'endpoint' => env('R2_ENDPOINT'),
    'use_path_style_endpoint' => true,
],
```

### 2. Unified Storage Service
Create a `StorageService` to abstract the underlying implementation.
*   **Method `upload(UploadedFile $file, $folder)`**: Determines the correct path and stores to `r2`.
*   **Method `getUrl($path)`**: Generates the R2 public URL.
*   **Method `delete($path)`**: Removes from R2.

### 3. Controller Refactoring
Replace `Cloudinary::upload` and `Storage::disk('public')->put` calls with the new `StorageService`.

### 4. Model Accessors
Standardize models to return absolute URLs via accessors, removing the need for frontend prefix checks.
```php
public function getImageUrlAttribute()
{
    return Storage::disk('r2')->url($this->image_path);
}
```

---

## SECTION 8 — MIGRATION STRATEGY

We recommend a **Three-Phase Migration**:

### Phase 1: Dual-Write (Preparation)
1.  Configure Cloudflare R2.
2.  Implement the unified `StorageService`.
3.  Update controllers to write **new** uploads to R2 while maintaining Cloudinary/Local read logic.

### Phase 2: Background Data Transfer
1.  Develop a migration command to scan database tables.
2.  Download files from Cloudinary/Local and upload them to R2.
3.  Update DB records to point to relative R2 paths or absolute R2 URLs.

### Phase 3: Switch & Cleanup
1.  Point all read accessors/URL helpers to R2.
2.  Disable Cloudinary SDK and delete local `storage/app/public` files (after verified backup).
3.  Remove Cloudinary credentials and configurations.

---

## SECTION 9 — RISK ANALYSIS & SCOPE

### Risks:
*   **Broken Absolute URLs:** Hardcoded `/storage/` or `res.cloudinary.com` strings in frontend components or PDF templates might break.
*   **Large File Volumes:** Chat attachments and Diamond images may take significant time to transfer.
*   **Public Visibility:** Ensuring R2 bucket permissions are correctly configured to public-read for assets while securing private documents.

### Estimated Scope:
*   **Complexity:** Medium-High (due to multiple implementation patterns).
*   **Timeline:**
    *   Analysis & Setup: 1 Day
    *   Code Refactoring: 3-4 Days
    *   Data Migration: 1-2 Days (depending on volume)
    *   QA & Testing: 2 Days

### Recommended Tools:
*   **Rclone:** For migrating files directly from Local/Cloudinary to R2.
*   **Laravel Console Commands:** For database record updates.
