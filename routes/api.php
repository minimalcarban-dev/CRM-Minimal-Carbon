<?php

use App\Http\Controllers\ShopifyWebhookController;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider and all of them will | be assigned to the "api" middleware group. | */

// ─────────────────────────────────────────────────────────────
// Shopify Webhook Routes (public, HMAC-verified)
// ─────────────────────────────────────────────────────────────
Route::prefix('webhooks/shopify')->group(function () {
    Route::post('products/create', [ShopifyWebhookController::class, 'handleProductCreate']);
    Route::post('products/update', [ShopifyWebhookController::class, 'handleProductUpdate']);
    Route::post('products/delete', [ShopifyWebhookController::class, 'handleProductDelete']);
    Route::post('orders/create', [ShopifyWebhookController::class, 'handleOrderCreate']);
});
