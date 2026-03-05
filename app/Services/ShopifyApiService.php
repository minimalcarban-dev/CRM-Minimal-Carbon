<?php

namespace App\Services;

use App\Models\ShopifySyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyApiService
{
    protected string $baseUrl;
    protected string $token;
    protected string $apiVersion;

    public function __construct()
    {
        $storeUrl = config('shopify.store_url');
        $this->token = config('shopify.admin_access_token');
        $this->apiVersion = config('shopify.api_version');
        $this->baseUrl = "https://{$storeUrl}/admin/api/{$this->apiVersion}";
    }

    /*
     |--------------------------------------------------------------------------
     | HTTP Client (with rate-limit handling)
     |--------------------------------------------------------------------------
     */

    /**
     * Make a GET request to Shopify Admin API.
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request to Shopify Admin API.
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request to Shopify Admin API.
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request to Shopify Admin API.
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Core request method with retry logic for rate limiting (429).
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $url = "{$this->baseUrl}/{$endpoint}";
        $maxRetries = config('shopify.max_retries', 3);
        $retryDelay = config('shopify.retry_delay_ms', 2000);

        for ($attempt = 0; $attempt <= $maxRetries; $attempt++) {
            try {
                $pending = Http::timeout(30)
                    ->withHeaders([
                    'X-Shopify-Access-Token' => $this->token,
                    'Content-Type' => 'application/json',
                ]);

                $response = match ($method) {
                        'GET' => $pending->get($url, $options['query'] ?? []),
                        'POST' => $pending->post($url, $options['json'] ?? []),
                        'PUT' => $pending->put($url, $options['json'] ?? []),
                        'DELETE' => $pending->delete($url),
                    };

                // Rate limit hit — retry with backoff
                if ($response->status() === 429) {
                    $retryAfter = $response->header('Retry-After', $retryDelay / 1000);
                    $sleepMs = (int)($retryAfter * 1000) + ($attempt * 500);
                    Log::warning("Shopify 429 rate limit hit, sleeping {$sleepMs}ms (attempt {$attempt})");
                    usleep($sleepMs * 1000);
                    continue;
                }

                $data = $response->json() ?? [];

                if (!$response->successful()) {
                    Log::error('Shopify API error', [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'body' => $data,
                    ]);
                    return ['success' => false, 'error' => $data['errors'] ?? 'Unknown error', 'status' => $response->status()];
                }

                // Respectful rate limiting — small sleep between calls
                usleep(config('shopify.rate_limit_delay_ms', 550) * 1000);

                return ['success' => true, 'data' => $data];

            }
            catch (\Exception $e) {
                Log::error('Shopify API exception', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt === $maxRetries) {
                    return ['success' => false, 'error' => $e->getMessage()];
                }

                usleep($retryDelay * 1000 * ($attempt + 1));
            }
        }

        return ['success' => false, 'error' => 'Max retries exhausted'];
    }

    /*
     |--------------------------------------------------------------------------
     | Products
     |--------------------------------------------------------------------------
     */

    /**
     * Fetch all products (paginated, max 250 per page).
     */
    public function getProducts(array $params = []): array
    {
        $params = array_merge(['limit' => 250], $params);
        return $this->get('products.json', $params);
    }

    /**
     * Fetch single product by ID.
     */
    public function getProduct(int $productId): array
    {
        return $this->get("products/{$productId}.json");
    }

    /**
     * Create a product on Shopify.
     */
    public function createProduct(array $productData): array
    {
        return $this->post('products.json', ['product' => $productData]);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(int $productId, array $productData): array
    {
        return $this->put("products/{$productId}.json", ['product' => $productData]);
    }

    /*
     |--------------------------------------------------------------------------
     | Product Metafields
     |--------------------------------------------------------------------------
     */

    /**
     * Get metafields for a product.
     */
    public function getProductMetafields(int $productId): array
    {
        return $this->get("products/{$productId}/metafields.json");
    }

    /**
     * Set/update metafields for a product.
     */
    public function setProductMetafield(int $productId, array $metafield): array
    {
        return $this->post("products/{$productId}/metafields.json", [
            'metafield' => $metafield,
        ]);
    }

    /*
     |--------------------------------------------------------------------------
     | Collections
     |--------------------------------------------------------------------------
     */

    /**
     * Fetch custom collections.
     */
    public function getCustomCollections(array $params = []): array
    {
        $params = array_merge(['limit' => 250], $params);
        return $this->get('custom_collections.json', $params);
    }

    /**
     * Fetch smart collections.
     */
    public function getSmartCollections(array $params = []): array
    {
        $params = array_merge(['limit' => 250], $params);
        return $this->get('smart_collections.json', $params);
    }

    /*
     |--------------------------------------------------------------------------
     | Draft Orders
     |--------------------------------------------------------------------------
     */

    /**
     * Create a draft order on Shopify.
     */
    public function createDraftOrder(array $orderData): array
    {
        return $this->post('draft_orders.json', ['draft_order' => $orderData]);
    }

    /*
     |--------------------------------------------------------------------------
     | Webhooks
     |--------------------------------------------------------------------------
     */

    /**
     * Register a webhook.
     */
    public function registerWebhook(string $topic, string $address): array
    {
        return $this->post('webhooks.json', [
            'webhook' => [
                'topic' => $topic,
                'address' => $address,
                'format' => 'json',
            ],
        ]);
    }

    /**
     * List all registered webhooks.
     */
    public function getWebhooks(): array
    {
        return $this->get('webhooks.json');
    }

    /*
     |--------------------------------------------------------------------------
     | Webhook HMAC Verification
     |--------------------------------------------------------------------------
     */

    /**
     * Verify the HMAC signature of an incoming Shopify webhook.
     */
    public static function verifyWebhookSignature(string $payload, string $hmacHeader): bool
    {
        $secret = config('shopify.api_secret');
        $computed = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($computed, $hmacHeader);
    }

    /*
     |--------------------------------------------------------------------------
     | Connection Test
     |--------------------------------------------------------------------------
     */

    /**
     * Simple test: fetch shop info to confirm the token is valid.
     */
    public function testConnection(): array
    {
        $result = $this->get('shop.json');

        if ($result['success'] ?? false) {
            ShopifySyncLog::logAction('TestConnection', 'Shop', null, 'Success', 'Connected successfully');
        }
        else {
            ShopifySyncLog::logAction('TestConnection', 'Shop', null, 'Failed', $result['error'] ?? 'Unknown');
        }

        return $result;
    }
}
