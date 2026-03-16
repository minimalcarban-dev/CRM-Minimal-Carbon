# Shopify CRM Integration Module: Detailed Documentation

## 1. Overview
This module enables seamless integration between your custom CRM and a Shopify store. It provides bi-directional synchronization of products, categories (collections), and orders, allowing centralized management of your e-commerce operations directly from the CRM interface.

---

## 2. What Changes Will Happen on the Shopify Side?
When this CRM integration is active, it **will not** disrupt your Shopify theme or frontend for customers. However, the following backend elements in Shopify will be utilized or modified by the CRM via the API:

### A. Metafields
- **Creation & Updates:** The CRM will create or update Product/Variant Metafields in Shopify. For example, if you extract "Material: Gold" from a CRM product, it can be saved in a specific `custom.material` metafield in Shopify.
- **Namespaces:** A dedicated namespace (e.g., `crm_sync`) should be used to keep CRM-managed custom fields separate from other Shopify apps.

### B. Tags
- Automatically tagging products imported from the CRM (e.g., `CRM_Synced`, `Needs_Review`) to easily filter them inside the Shopify Admin.

### C. Draft Orders & Products
- **Draft Products (Hidden Products):** When a custom product or generic service is sold in the CRM, the CRM can push it to Shopify as a Draft Product (Status: Draft) so it doesn't appear on the public storefront but is available for inventory or invoicing purposes.
- **Draft Orders:** The CRM can generate Shopify Draft Orders for manual invoicing.

### D. Webhooks
- The CRM will register webhooks on your Shopify store. This means Shopify will send instant HTTP requests to your CRM whenever:
  - An Order is created/updated/paid (`orders/create`, `orders/updated`).
  - A Product is created/updated/deleted (`products/create`, `products/update`, `products/delete`).
  - A Customer profile is updated.

---

## 3. How to Create a Shopify Custom App for the CRM
To connect the CRM to Shopify, you need to generate API credentials. You don't need to publish an app on the Shopify App Store; you just need a **Custom App** specifically for your store.

### Step-by-Step Guide:
1. **Log in to Shopify Admin:** Go to your Shopify store admin panel (e.g., `your-store.myshopify.com/admin`).
2. **Go to Apps:** Click on **Settings** (bottom left corner) > **Apps and sales channels**.
3. **Develop Apps:** Click the **Develop apps** button in the top right.
   - *If this is your first time, you may need to click "Allow custom app development".*
4. **Create an App:** 
   - Click **Create an app**.
   - **App Name:** Enter a name like "CRM Integration API".
   - **App Developer:** Select your admin account.
   - Click **Create app**.
5. **Configure API Scopes (Permissions):**
   - Go to the **Configuration** tab.
   - Under "Admin API integration", click **Configure**.
   - Search for and check **Read** and **Write** permissions for the following scopes:
     - `read_products`, `write_products`
     - `read_product_listings`
     - `read_orders`, `write_orders`
     - `read_draft_orders`, `write_draft_orders`
     - `read_customers`, `write_customers`
     - `read_inventory`, `write_inventory`
     - `read_locations`
   - Click **Save** in the top right.
6. **Install the App & Get the Token:**
   - Go to the **API Credentials** tab.
   - Click **Install app** and confirm.
   - Once installed, you will see an **Admin API access token** (it starts with `shpat_...`).
   - **CRITICAL:** Click "Reveal token once" and copy this token immediately. Shopify will only show it once. Save it securely.
7. **Add to CRM:** Paste this `shpat_...` token and your store URL (`your-store.myshopify.com`) into your CRM's Shopify Integration Settings page.

---

## 4. Key CRM Features & Workflows

### A. Product Management & Syncing
- **View Shopify Listings:** A dedicated page in the CRM to view all Shopify products.
- **Import from Shopify:** Pull existing Shopify products into the CRM database.
- **Export to Shopify:** Push a newly created CRM product to Shopify.
- **Sync Logic:** Matching is typically done via **SKU** or **Barcode**. The CRM must store the Shopify `product_id` and `variant_id` in its database for future syncs.

### B. Category (Collection) Synchronization
- **Bi-directional Sync:** Map CRM Categories to Shopify Custom Collections. 
- When a product's category is changed in the CRM, the CRM calls the Shopify API to add/remove the product from the corresponding Collection.

### C. Extracting Custom Meta Fields from Descriptions
- **Task:** Extract the following custom product metafields from the product description and map them to the corresponding CRM/Shopify metafields:
  - Metal Purity
  - Metal
  - Resizable
  - Comfort Fit
  - Ring Height 1
  - Ring Width 1
  - Product Video
  - Stone Measurement
  - Stone Clarity
  - Stone Carat Weight
  - Stone Color
  - Stone Shape
  - Stone Type
  - Side Stone Type
  - Side Shape
  - Side Color
  - Side Carat Weight
  - Side Measurement
  - Side Clarity
  - melee_size
- **Logic:** During import or sync, if a product description contains these structured attributes, the CRM must parse this string and save the respective values.
- AI or Regex scripts in the CRM backend will handle this extraction to populate the custom metafields accurately.

### D. Automated Draft Product Creation on Sale
- **Workflow:** 
  1. A sale is finalized in the CRM for an item that isn't on Shopify (e.g., a custom order).
  2. The CRM automatically fires a POST request to the Shopify API (`/admin/api/2024-01/products.json`).
  3. A new product is created in Shopify with `status: "draft"` (so it remains hidden from the public).
  4. The inventory is adjusted accordingly.

---

## 5. Database Schema Changes Required in CRM
To support this integration, the following tables/columns should be added to the CRM's MySQL database:

### Table: `shopify_settings`
Stores the API credentials for the CRM.
- `id`, `store_url`, `access_token` (Encrypted), `api_version`, `is_active`

### Table updates: `products`
Link CRM products to Shopify.
- Add `shopify_product_id` (BigInt, Nullable)
- Add `shopify_variant_id` (BigInt, Nullable)
- Add `last_synced_at` (Timestamp)

### Table updates: `categories`
Link CRM categories to Shopify Collections.
- Add `shopify_collection_id` (BigInt, Nullable)

### Table: `shopify_sync_logs`
For debugging and tracking API errors.
- `id`, `action` (e.g., "Import", "Export", "Webhook"), `status` (Success/Failed), `response_message`, `created_at`

---

## 6. Technical Stack & API Communication
- **API Format:** Shopify Admin REST API / GraphQL API.
- **Authentication:** HTTP Header `X-Shopify-Access-Token`.
- **Background Jobs:** Product imports/exports and syncing should ideally be processed via background queues (e.g., Laravel Queues, Redis) so the CRM UI doesn't freeze while waiting for Shopify's API.
- **Rate Limits:** Shopify has strict API rate limits (e.g., 2 requests/sec for REST). The CRM must include logic to sleep/retry if it receives a `429 Too Many Requests` error.
