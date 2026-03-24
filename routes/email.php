<?php

use App\Modules\Email\Controllers\AuthController;
use App\Modules\Email\Controllers\InboxController;
use App\Modules\Email\Controllers\EmailActionController;
use Illuminate\Support\Facades\Route;

// Account Management
Route::get('/accounts', [InboxController::class, 'accounts'])->name('accounts.list');
Route::post('/accounts/{account}/revoke', [AuthController::class, 'revoke'])->name('account.revoke');
Route::delete('/accounts/{account}', [AuthController::class, 'destroy'])->name('account.delete');


// OAuth Flow
Route::get('/oauth/redirect/{company}', [AuthController::class, 'redirect'])->name('oauth.redirect');
Route::get('/oauth/callback', [AuthController::class, 'callback'])->name('oauth.callback');

// Inbox & Email Viewing
Route::prefix('/{account}')->group(function () {
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::get('/sent', [InboxController::class, 'sent'])->name('sent');
    Route::get('/starred', [InboxController::class, 'starred'])->name('starred');
    Route::get('/drafts', [InboxController::class, 'drafts'])->name('drafts');
    Route::get('/trash', [InboxController::class, 'trash'])->name('trash');
    Route::get('/sync', [InboxController::class, 'sync'])->name('sync');
    Route::get('/show/{id}', [InboxController::class, 'show'])->name('show');

    // Actions
    Route::post('/compose/send', [EmailActionController::class, 'send'])->name('compose.send');
    Route::post('/compose/draft', [EmailActionController::class, 'draft'])->name('compose.draft');
    Route::post('/email/{email}/star', [EmailActionController::class, 'toggleStar'])->name('email.star');
    Route::post('/email/{email}/read', [EmailActionController::class, 'toggleRead'])->name('email.read');
    Route::delete('/email/{email}', [EmailActionController::class, 'destroy'])->name('email.delete');
});
