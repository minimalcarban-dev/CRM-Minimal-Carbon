<?php

namespace App\Jobs;

use App\Models\ShopifyProduct;
use App\Models\ShopifySyncLog;
use App\Services\ShopifyApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExportProductToShopifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productId;

    /**
     * Create a new job instance.
     */
    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    /**
     * Execute the job.
     */
    public function handle(ShopifyApiService $shopify): void
    {
        try {
            $product = ShopifyProduct::findOrFail($this->productId);

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

            // If it already has an ID, we update, else we create
            if ($product->shopify_product_id) {
                $result = $shopify->updateProduct($product->shopify_product_id, $productData);
                $action = 'Update';
            }
            else {
                $result = $shopify->createProduct($productData);
                $action = 'Create';
            }

            if ($result['success'] ?? false) {
                $newId = $result['data']['product']['id'] ?? null;
                if ($newId && !$product->shopify_product_id) {
                    $product->update([
                        'shopify_product_id' => $newId,
                        'last_synced_at' => now(),
                    ]);
                }
                else {
                    $product->update(['last_synced_at' => now()]);
                }

                ShopifySyncLog::logAction('Export (Job)', 'Product', (string)($newId ?? $product->shopify_product_id), 'Success', "Exported via background job ({$action})");
                return;
            }

            ShopifySyncLog::logAction('Export (Job)', 'Product', (string)$this->productId, 'Failed', $result['error'] ?? 'Unknown');
        }
        catch (\Exception $e) {
            Log::error('Shopify background export error', ['error' => $e->getMessage()]);
            ShopifySyncLog::logAction('Export (Job)', 'Product', (string)$this->productId, 'Failed', $e->getMessage());
            throw $e;
        }
    }
}
