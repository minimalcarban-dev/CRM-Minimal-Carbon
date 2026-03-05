<?php

namespace App\Jobs;

use App\Models\ShopifyProduct;
use App\Models\ShopifySyncLog;
use App\Services\ShopifyApiService;
use App\Services\ShopifyMetafieldExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportShopifyProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    //
    }

    /**
     * Execute the job.
     */
    public function handle(ShopifyApiService $shopify): void
    {
        try {
            $result = $shopify->getProducts();
            $imported = 0;
            $updated = 0;

            if (!($result['success'] ?? false)) {
                ShopifySyncLog::logAction('Import (Job)', 'Product', null, 'Failed', $result['error'] ?? 'API error');
                return;
            }

            $products = $result['data']['products'] ?? [];

            foreach ($products as $shopifyProduct) {
                $variant = $shopifyProduct['variants'][0] ?? [];
                $images = $shopifyProduct['images'] ?? [];

                // Extract metafields from description
                $metafields = ShopifyMetafieldExtractor::extract($shopifyProduct['body_html'] ?? null);

                // Fetch API metafields
                $mfResult = $shopify->getProductMetafields($shopifyProduct['id']);
                if (($mfResult['success'] ?? false) && isset($mfResult['data']['metafields'])) {
                    $apiMetafields = ShopifyMetafieldExtractor::extractFromMetafieldApi($mfResult['data']['metafields']);
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

                // Small delay to prevent hitting Shopify API limits if doing many API calls for metafields.
                usleep(300 * 1000); // 300ms
            }

            ShopifySyncLog::logAction('Import (Job)', 'Product', null, 'Success',
                "Imported: {$imported}, Updated: {$updated}, Total Processed: " . count($products));

        }
        catch (\Exception $e) {
            Log::error('Shopify background import error', ['error' => $e->getMessage()]);
            ShopifySyncLog::logAction('Import (Job)', 'Product', null, 'Failed', $e->getMessage());
            throw $e; // Trigger retry
        }
    }
}
