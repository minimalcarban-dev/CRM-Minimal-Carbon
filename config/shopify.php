<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Shopify Store Configuration
     |--------------------------------------------------------------------------
     */

    'store_url' => env('SHOPIFY_STORE_URL', ''),

    'admin_access_token' => env('SHOPIFY_ADMIN_ACCESS_TOKEN', ''),

    'api_key' => env('SHOPIFY_API_KEY', ''),

    'api_secret' => env('SHOPIFY_API_SECRET', ''),

    'api_version' => env('SHOPIFY_API_VERSION', '2024-01'),

    /*
     |--------------------------------------------------------------------------
     | Metafield Namespace
     |--------------------------------------------------------------------------
     | Namespace used for CRM-managed metafields on Shopify products.
     */

    'metafield_namespace' => 'crm_sync',

    /*
     |--------------------------------------------------------------------------
     | Rate Limiting
     |--------------------------------------------------------------------------
     | Shopify REST API allows ~2 requests/second.
     */

    'rate_limit_delay_ms' => 550, // ms to sleep between requests
    'max_retries' => 3, // retry count on 429 errors
    'retry_delay_ms' => 2000, // initial retry delay on 429

    /*
     |--------------------------------------------------------------------------
     | Custom Metafield Keys
     |--------------------------------------------------------------------------
     | The product-level custom metafields to extract from descriptions
     | and sync between CRM and Shopify.
     */

    'custom_metafields' => [
        'metal_purity',
        'metal',
        'resizable',
        'comfort_fit',
        'ring_height_1',
        'ring_width_1',
        'product_video',
        'stone_measurement',
        'stone_clarity',
        'stone_carat_weight',
        'stone_color',
        'stone_shape',
        'stone_type',
        'side_stone_type',
        'side_shape',
        'side_color',
        'side_carat_weight',
        'side_measurement',
        'side_clarity',
        'melee_size',
    ],

];
