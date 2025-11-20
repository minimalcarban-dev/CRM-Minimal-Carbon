<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// Chat routes
Route::middleware(['auth:admin'])->group(function () {
    Route::prefix('chat')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/channels', [ChatController::class, 'createChannel']);
        Route::get('/channels', [ChatController::class, 'getChannels']);
        Route::post('/direct', [ChatController::class, 'direct']);
        Route::get('/channels/{channel}/messages', [ChatController::class, 'getMessages']);
        // Apply custom chat rate limiter (per admin per channel) + fallback throttle (burst protection)
        Route::post('/channels/{channel}/messages', [ChatController::class, 'sendMessage'])
            ->middleware(['chat.rate','throttle:120,1']);
        Route::post('/channels/{channel}/read', [ChatController::class, 'markAsRead']);
        Route::get('/channels/{channel}/sidebar', [ChatController::class, 'sidebar']);
        Route::get('/messages/search', [ChatController::class, 'searchMessages']);
        Route::get('/channels/{channel}/members', [ChatController::class, 'getChannelMembers']);
        Route::put('/channels/{channel}/members', [ChatController::class, 'updateChannelMembers']);
    });
});