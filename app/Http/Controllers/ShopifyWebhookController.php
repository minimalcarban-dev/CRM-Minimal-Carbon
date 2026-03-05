<?php

namespace App\Http\Controllers;

use App\Models\ShopifyProduct;
use App\Models\ShopifySyncLog;
use App\Services\ShopifyApiService;
use App\Services\ShopifyMetafieldExtractor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookController extends Controller
{
    /**
     * Handle product creation webhook from Shopify.
     * Topic: products/create
     */
    public function handleProductCreate(Request $request): JsonResponse
    {
        if (!$this->verifySignature($request)) {
            Log::warning('Shopify webhook: invalid signature on products/create');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        try {
            $variant = $payload['variants'][0] ?? [];

            $metafields = ShopifyMetafieldExtractor::extract($payload['body_html'] ?? null);

            ShopifyProduct::updateOrCreate(
            ['shopify_product_id' => $payload['id']],
                array_merge([
                'shopify_variant_id' => $variant['id'] ?? null,
                'title' => $payload['title'] ?? '',
                'handle' => $payload['handle'] ?? null,
                'sku' => $variant['sku'] ?? null,
                'barcode' => $variant['barcode'] ?? null,
                'price' => $variant['price'] ?? 0,
                'compare_at_price' => $variant['compare_at_price'] ?? null,
                'status' => $payload['status'] ?? 'active',
                'description_html' => $payload['body_html'] ?? null,
                'images' => $payload['images'] ?? [],
                'tags' => $payload['tags'] ?? null,
                'vendor' => $payload['vendor'] ?? null,
                'product_type' => $payload['product_type'] ?? null,
                'inventory_quantity' => $variant['inventory_quantity'] ?? 0,
                'last_synced_at' => now(),
            ], $metafields)
            );

            ShopifySyncLog::logAction('Webhook', 'Product', (string)$payload['id'], 'Success', 'Product created via webhook');

        }
        catch (\Exception $e) {
            Log::error('Shopify webhook products/create error', ['error' => $e->getMessage()]);
            ShopifySyncLog::logAction('Webhook', 'Product', (string)($payload['id'] ?? 'unknown'), 'Failed', $e->getMessage());
        }

        // Always respond 200 to Shopify (they retry on failures)
        return response()->json(['ok' => true]);
    }

    /**
     * Handle product update webhook from Shopify.
     * Topic: products/update
     */
    public function handleProductUpdate(Request $request): JsonResponse
    {
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        try {
            $existing = ShopifyProduct::where('shopify_product_id', $payload['id'])->first();

            if ($existing) {
                $variant = $payload['variants'][0] ?? [];
                $metafields = ShopifyMetafieldExtractor::extract($payload['body_html'] ?? null);

                $existing->update(array_merge([
                    'title' => $payload['title'] ?? $existing->title,
                    'handle' => $payload['handle'] ?? null,
                    'sku' => $variant['sku'] ?? null,
                    'price' => $variant['price'] ?? $existing->price,
                    'compare_at_price' => $variant['compare_at_price'] ?? null,
                    'status' => $payload['status'] ?? $existing->status,
                    'description_html' => $payload['body_html'] ?? null,
                    'images' => $payload['images'] ?? $existing->images,
                    'tags' => $payload['tags'] ?? null,
                    'vendor' => $payload['vendor'] ?? null,
                    'product_type' => $payload['product_type'] ?? null,
                    'inventory_quantity' => $variant['inventory_quantity'] ?? 0,
                    'last_synced_at' => now(),
                ], $metafields));

                ShopifySyncLog::logAction('Webhook', 'Product', (string)$payload['id'], 'Success', 'Product updated via webhook');
            }

        }
        catch (\Exception $e) {
            Log::error('Shopify webhook products/update error', ['error' => $e->getMessage()]);
            ShopifySyncLog::logAction('Webhook', 'Product', (string)($payload['id'] ?? 'unknown'), 'Failed', $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle product delete webhook from Shopify.
     * Topic: products/delete
     */
    public function handleProductDelete(Request $request): JsonResponse
    {
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        try {
            $product = ShopifyProduct::where('shopify_product_id', $payload['id'])->first();

            if ($product) {
                $product->update(['status' => 'archived']);
                ShopifySyncLog::logAction('Webhook', 'Product', (string)$payload['id'], 'Success', 'Product archived via webhook');
            }

        }
        catch (\Exception $e) {
            Log::error('Shopify webhook products/delete error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle order creation webhook from Shopify.
     * Topic: orders/create
     */
    public function handleOrderCreate(Request $request): JsonResponse
    {
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        try {
            ShopifySyncLog::logAction('Webhook', 'Order', (string)($payload['id'] ?? 'unknown'), 'Success',
                'Order created: ' . ($payload['name'] ?? 'N/A') . ' — Total: ' . ($payload['total_price'] ?? '0'));
        }
        catch (\Exception $e) {
            Log::error('Shopify webhook orders/create error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    /*
     |--------------------------------------------------------------------------
     | HMAC Signature Verification
     |--------------------------------------------------------------------------
     */

    protected function verifySignature(Request $request): bool
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-SHA256');

        if (!$hmacHeader) {
            return false;
        }

        $payload = $request->getContent();

        return ShopifyApiService::verifyWebhookSignature($payload, $hmacHeader);
    }
}
