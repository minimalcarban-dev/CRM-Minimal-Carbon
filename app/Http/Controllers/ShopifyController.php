<?php

namespace App\Http\Controllers;

use App\Models\ShopifyProduct;
use App\Models\ShopifyCollection;
use App\Models\ShopifySetting;
use App\Models\ShopifySyncLog;
use App\Services\ShopifyApiService;
use App\Services\ShopifyMetafieldExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyController extends Controller
{
    protected ShopifyApiService $shopify;

    public function __construct(ShopifyApiService $shopify)
    {
        $this->shopify = $shopify;
    }

    /*
     |--------------------------------------------------------------------------
     | Settings
     |--------------------------------------------------------------------------
     */

    public function settings()
    {
        $setting = ShopifySetting::current();

        return view('shopify.settings', [
            'setting' => $setting,
            'isConnected' => $setting && $setting->is_active,
        ]);
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'store_url' => 'required|string|max:255',
            'access_token' => 'required|string',
            'api_version' => 'nullable|string|max:20',
        ]);

        $setting = ShopifySetting::current();

        if ($setting) {
            $setting->update([
                'store_url' => $validated['store_url'],
                'access_token' => $validated['access_token'],
                'api_version' => $validated['api_version'] ?? '2024-01',
                'is_active' => true,
            ]);
        }
        else {
            ShopifySetting::create([
                'store_url' => $validated['store_url'],
                'access_token' => $validated['access_token'],
                'api_version' => $validated['api_version'] ?? '2024-01',
                'is_active' => true,
            ]);
        }

        return redirect()->route('shopify.settings')
            ->with('success', 'Shopify settings saved successfully.');
    }

    public function testConnection()
    {
        $result = $this->shopify->testConnection();

        if ($result['success'] ?? false) {
            $shopName = $result['data']['shop']['name'] ?? 'Unknown';
            return back()->with('success', "✅ Connected to Shopify store: {$shopName}");
        }

        return back()->with('error', '❌ Connection failed: ' . ($result['error'] ?? 'Unknown error'));
    }

    /*
     |--------------------------------------------------------------------------
     | Products
     |--------------------------------------------------------------------------
     */

    public function products(Request $request)
    {
        $query = ShopifyProduct::query()->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $products = $query->paginate(25)->withQueryString();

        return view('shopify.products.index', compact('products'));
    }

    public function importProducts()
    {
        try {
            $result = $this->shopify->getProducts();
            $imported = 0;
            $updated = 0;

            if (!($result['success'] ?? false)) {
                ShopifySyncLog::logAction('Import', 'Product', null, 'Failed', $result['error'] ?? 'API error');
                return back()->with('error', 'Import failed: ' . ($result['error'] ?? 'Unknown'));
            }

            $products = $result['data']['products'] ?? [];

            foreach ($products as $shopifyProduct) {
                $variant = $shopifyProduct['variants'][0] ?? [];
                $images = $shopifyProduct['images'] ?? [];

                // Extract metafields from description
                $metafields = ShopifyMetafieldExtractor::extract($shopifyProduct['body_html'] ?? null);

                // Also try to get metafields via API for this product
                $mfResult = $this->shopify->getProductMetafields($shopifyProduct['id']);
                if (($mfResult['success'] ?? false) && isset($mfResult['data']['metafields'])) {
                    $apiMetafields = ShopifyMetafieldExtractor::extractFromMetafieldApi($mfResult['data']['metafields']);
                    // API metafields take priority over description-extracted ones
                    $metafields = array_merge($metafields, $apiMetafields);
                }

                $data = array_merge([
                    'shopify_product_id' => $shopifyProduct['id'],
                    'shopify_variant_id' => $variant['id'] ?? null,
                    'title' => $shopifyProduct['title'] ?? '',
                    'handle' => $shopifyProduct['handle'] ?? null,
                    'sku' => $variant['sku'] ?? null,
                    'barcode' => $variant['barcode'] ?? null,
                    'price' => $variant['price'] ?? 0,
                    'compare_at_price' => $variant['compare_at_price'] ?? null,
                    'status' => $shopifyProduct['status'] ?? 'active',
                    'description_html' => $shopifyProduct['body_html'] ?? null,
                    'images' => $images,
                    'tags' => $shopifyProduct['tags'] ?? null,
                    'vendor' => $shopifyProduct['vendor'] ?? null,
                    'product_type' => $shopifyProduct['product_type'] ?? null,
                    'inventory_quantity' => $variant['inventory_quantity'] ?? 0,
                    'last_synced_at' => now(),
                ], $metafields);

                $existing = ShopifyProduct::where('shopify_product_id', $shopifyProduct['id'])->first();

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                }
                else {
                    ShopifyProduct::create($data);
                    $imported++;
                }
            }

            ShopifySyncLog::logAction('Import', 'Product', null, 'Success',
                "Imported: {$imported}, Updated: {$updated}, Total: " . count($products));

            return back()->with('success', "✅ Import complete! New: {$imported}, Updated: {$updated}");

        }
        catch (\Exception $e) {
            Log::error('Shopify import error', ['error' => $e->getMessage()]);
            ShopifySyncLog::logAction('Import', 'Product', null, 'Failed', $e->getMessage());

            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function showProduct(int $id)
    {
        $product = ShopifyProduct::findOrFail($id);

        return view('shopify.products.show', compact('product'));
    }

    public function syncProduct(int $id)
    {
        $product = ShopifyProduct::findOrFail($id);

        $result = $this->shopify->getProduct($product->shopify_product_id);

        if (!($result['success'] ?? false)) {
            ShopifySyncLog::logAction('Sync', 'Product', (string)$product->shopify_product_id, 'Failed', $result['error'] ?? 'Unknown');
            return back()->with('error', 'Sync failed: ' . ($result['error'] ?? 'Unknown'));
        }

        $shopifyProduct = $result['data']['product'] ?? [];
        $variant = $shopifyProduct['variants'][0] ?? [];
        $images = $shopifyProduct['images'] ?? [];

        // Re-extract metafields
        $metafields = ShopifyMetafieldExtractor::extract($shopifyProduct['body_html'] ?? null);

        $mfResult = $this->shopify->getProductMetafields($product->shopify_product_id);
        if (($mfResult['success'] ?? false) && isset($mfResult['data']['metafields'])) {
            $apiMetafields = ShopifyMetafieldExtractor::extractFromMetafieldApi($mfResult['data']['metafields']);
            $metafields = array_merge($metafields, $apiMetafields);
        }

        $product->update(array_merge([
            'title' => $shopifyProduct['title'] ?? $product->title,
            'handle' => $shopifyProduct['handle'] ?? null,
            'sku' => $variant['sku'] ?? null,
            'barcode' => $variant['barcode'] ?? null,
            'price' => $variant['price'] ?? $product->price,
            'compare_at_price' => $variant['compare_at_price'] ?? null,
            'status' => $shopifyProduct['status'] ?? $product->status,
            'description_html' => $shopifyProduct['body_html'] ?? null,
            'images' => $images,
            'tags' => $shopifyProduct['tags'] ?? null,
            'vendor' => $shopifyProduct['vendor'] ?? null,
            'product_type' => $shopifyProduct['product_type'] ?? null,
            'inventory_quantity' => $variant['inventory_quantity'] ?? 0,
            'last_synced_at' => now(),
        ], $metafields));

        ShopifySyncLog::logAction('Sync', 'Product', (string)$product->shopify_product_id, 'Success', 'Product synced');

        return back()->with('success', '✅ Product synced successfully!');
    }

    public function exportProduct(int $id)
    {
        $product = ShopifyProduct::findOrFail($id);

        $productData = [
            'title' => $product->title,
            'body_html' => $product->description_html,
            'vendor' => $product->vendor ?? 'Minimal Carbon',
            'status' => 'draft',
            'tags' => 'CRM_Synced',
            'variants' => [
                [
                    'price' => $product->price,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'inventory_quantity' => $product->inventory_quantity,
                    'inventory_management' => 'shopify',
                ],
            ],
        ];

        $result = $this->shopify->createProduct($productData);

        if ($result['success'] ?? false) {
            $newId = $result['data']['product']['id'] ?? null;
            if ($newId) {
                $product->update([
                    'shopify_product_id' => $newId,
                    'last_synced_at' => now(),
                ]);
            }
            ShopifySyncLog::logAction('Export', 'Product', (string)$newId, 'Success', 'Exported as draft');
            return back()->with('success', '✅ Product exported to Shopify as draft!');
        }

        ShopifySyncLog::logAction('Export', 'Product', null, 'Failed', $result['error'] ?? 'Unknown');
        return back()->with('error', 'Export failed: ' . ($result['error'] ?? 'Unknown'));
    }

    /*
     |--------------------------------------------------------------------------
     | Collections
     |--------------------------------------------------------------------------
     */

    public function collections()
    {
        $collections = ShopifyCollection::latest()->paginate(25);

        return view('shopify.collections.index', compact('collections'));
    }

    public function importCollections()
    {
        try {
            $imported = 0;

            // Custom Collections
            $result = $this->shopify->getCustomCollections();
            if ($result['success'] ?? false) {
                foreach ($result['data']['custom_collections'] ?? [] as $col) {
                    ShopifyCollection::updateOrCreate(
                    ['shopify_collection_id' => $col['id']],
                    [
                        'title' => $col['title'],
                        'handle' => $col['handle'] ?? null,
                    ]
                    );
                    $imported++;
                }
            }

            // Smart Collections
            $result = $this->shopify->getSmartCollections();
            if ($result['success'] ?? false) {
                foreach ($result['data']['smart_collections'] ?? [] as $col) {
                    ShopifyCollection::updateOrCreate(
                    ['shopify_collection_id' => $col['id']],
                    [
                        'title' => $col['title'],
                        'handle' => $col['handle'] ?? null,
                    ]
                    );
                    $imported++;
                }
            }

            ShopifySyncLog::logAction('Import', 'Collection', null, 'Success', "Imported {$imported} collections");

            return back()->with('success', "✅ Imported {$imported} collections!");

        }
        catch (\Exception $e) {
            ShopifySyncLog::logAction('Import', 'Collection', null, 'Failed', $e->getMessage());
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /*
     |--------------------------------------------------------------------------
     | Sync Logs
     |--------------------------------------------------------------------------
     */

    public function syncLogs(Request $request)
    {
        $query = ShopifySyncLog::query()->latest();

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('shopify.logs.index', compact('logs'));
    }
}
