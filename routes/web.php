<?php

use App\Http\Controllers\ClosureTypeController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MetalTypeController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\RingSizeController;
use App\Http\Controllers\SettingTypeController;
use App\Http\Controllers\StoneColorController;
use App\Http\Controllers\StoneShapeController;
use App\Http\Controllers\StoneTypeController;
use App\Http\Controllers\DiamondClarityController;
use App\Http\Controllers\DiamondCutController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPermissionController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DiamondController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('admin.login');
    // return view('welcome');
});

// Admin auth routes
Route::get('admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('admin.login.post');
Route::post('admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Broadcasting auth is registered in BroadcastServiceProvider with admin guard and admin prefix

// Admin CRUD routes (protected)
Route::middleware(['admin.auth'])->prefix('admin')->group(function () {
    // Lightweight dashboard landing for safe redirects
    Route::get('dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // DIAGNOSTIC ROUTE
    Route::any('test-broadcast', [ChatController::class, 'testBroadcast'])->name('admin.test-broadcast');

    // Chat routes with middleware
    Route::get('chat', [ChatController::class, 'index'])
        ->name('chat.index')
        ->middleware('admin.permission:chat.access');

    // Chat routes
    Route::prefix('chat')->middleware('admin.permission:chat.access')->group(function () {
        Route::post('/channels', [ChatController::class, 'createChannel']);
        Route::get('/channels', [ChatController::class, 'getChannels']);
        Route::post('/direct', [ChatController::class, 'direct']);
        Route::get('/channels/{channel}/messages', [ChatController::class, 'getMessages']);
        Route::post('/channels/{channel}/messages', [ChatController::class, 'sendMessage']);
        Route::post('/channels/{channel}/read', [ChatController::class, 'markAsRead']);
        Route::get('/channels/{channel}/sidebar', [ChatController::class, 'sidebar']);
        Route::get('/messages/search', [ChatController::class, 'searchMessages']);
        Route::get('/attachments/{attachment}/download', [ChatController::class, 'downloadAttachment'])->name('chat.attachment.download');
        Route::get('/attachments/{attachment}/proxy', [ChatController::class, 'proxyAttachment'])->name('chat.attachment.proxy');

        // Thread Routes (Missing previously)
        Route::get('/messages/{message}/thread', [ChatController::class, 'getThreadMessages']);
        Route::post('/messages/{message}/thread/replies', [ChatController::class, 'postThreadReply']);

        // Get unread message count
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);

        // Admins list for channel creation (super admin only)
        Route::get('/admins', [ChatController::class, 'listAdmins']);
        // Manage members (owner or super admin)
        Route::get('/channels/{channel}/members', [ChatController::class, 'getChannelMembers']);
        Route::put('/channels/{channel}/members', [ChatController::class, 'updateChannelMembers']);
        // Delete channel (super admin only)
        Route::delete('/channels/{channel}', [ChatController::class, 'deleteChannel']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Admin management (CRUD)
    Route::get('admins', [AdminController::class, 'index'])
        ->name('admins.index')
        ->middleware('admin.permission:admins.view');
    Route::get('admins/create', [AdminController::class, 'create'])
        ->name('admins.create')
        ->middleware('admin.permission:admins.create');
    Route::post('admins', [AdminController::class, 'store'])
        ->name('admins.store')
        ->middleware('admin.permission:admins.create');
    Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])
        ->name('admins.edit')
        ->middleware('admin.permission:admins.edit');
    Route::put('admins/{admin}', [AdminController::class, 'update'])
        ->name('admins.update')
        ->middleware('admin.permission:admins.edit');
    Route::get('admins/{admin}', [AdminController::class, 'show'])
        ->name('admins.show')
        ->middleware('admin.permission:admins.view');
    Route::delete('admins/{admin}', [AdminController::class, 'destroy'])
        ->name('admins.destroy')
        ->middleware('admin.permission:admins.delete');

    // Admin Permissions Management (consolidated)
    Route::get('admins/{admin}/permissions', [AdminPermissionController::class, 'show'])
        ->name('admins.permissions.show')
        ->middleware('admin.permission:admins.assign_permissions');
    Route::put('admins/{admin}/permissions', [AdminPermissionController::class, 'update'])
        ->name('admins.permissions.update')
        ->middleware('admin.permission:admins.assign_permissions');

    // Permission management (CRUD)
    Route::get('permissions', [PermissionController::class, 'index'])
        ->name('permissions.index')
        ->middleware('admin.permission:permissions.view');
    Route::get('permissions/create', [PermissionController::class, 'create'])
        ->name('permissions.create')
        ->middleware('admin.permission:permissions.create');
    Route::post('permissions', [PermissionController::class, 'store'])
        ->name('permissions.store')
        ->middleware('admin.permission:permissions.create');
    Route::get('permissions/{permission}', [PermissionController::class, 'show'])
        ->name('permissions.show')
        ->middleware('admin.permission:permissions.view');
    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->name('permissions.edit')
        ->middleware('admin.permission:permissions.edit');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])
        ->name('permissions.update')
        ->middleware('admin.permission:permissions.edit');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])
        ->name('permissions.delete')
        ->middleware('admin.permission:permissions.delete');

    // Orders
    Route::get('orders', [OrderController::class, 'index'])
        ->name('orders.index')
        ->middleware('admin.permission:orders.view');
    Route::get('orders/create', [OrderController::class, 'create'])
        ->name('orders.create')
        ->middleware('admin.permission:orders.create');
    Route::post('orders', [OrderController::class, 'store'])
        ->name('orders.store')
        ->middleware('admin.permission:orders.create');
    Route::get('orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show')
        ->middleware('admin.permission:orders.view');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])
        ->name('orders.edit')
        ->middleware('admin.permission:orders.edit');
    Route::put('orders/{order}', [OrderController::class, 'update'])
        ->name('orders.update')
        ->middleware('admin.permission:orders.edit');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])
        ->name('orders.destroy')
        ->middleware('admin.permission:orders.delete');
    Route::get('orders/form/{type}', [OrderController::class, 'loadFormPartial'])
        ->name('orders.loadFormPartial')
        ->middleware('admin.permission:orders.create');

    // Invoices (basic CRUD)
    Route::get('invoices', [InvoiceController::class, 'index'])
        ->name('invoices.index')
        ->middleware('admin.permission:invoices.view');
    Route::get('invoices/create', [InvoiceController::class, 'create'])
        ->name('invoices.create')
        ->middleware('admin.permission:invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])
        ->name('invoices.store')
        ->middleware('admin.permission:invoices.create');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])
        ->name('invoices.show')
        ->middleware('admin.permission:invoices.view');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
        ->name('invoices.pdf')
        ->middleware('admin.permission:invoices.view');
    Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])
        ->name('invoices.edit')
        ->middleware('admin.permission:invoices.edit');
    Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])
        ->name('invoices.update')
        ->middleware('admin.permission:invoices.edit');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])
        ->name('invoices.destroy')
        ->middleware('admin.permission:invoices.delete');


    // Metal Types
    Route::get('metal-types', [MetalTypeController::class, 'index'])
        ->name('metal_types.index')
        ->middleware('admin.permission:metal_types.view');
    Route::get('metal-types/create', [MetalTypeController::class, 'create'])
        ->name('metal_types.create')
        ->middleware('admin.permission:metal_types.create');
    Route::post('metal-types', [MetalTypeController::class, 'store'])
        ->name('metal_types.store')
        ->middleware('admin.permission:metal_types.create');
    Route::get('metal-types/{metal_type}', [MetalTypeController::class, 'show'])
        ->name('metal_types.show')
        ->middleware('admin.permission:metal_types.view');
    Route::get('metal-types/{metal_type}/edit', [MetalTypeController::class, 'edit'])
        ->name('metal_types.edit')
        ->middleware('admin.permission:metal_types.edit');
    Route::put('metal-types/{metal_type}', [MetalTypeController::class, 'update'])
        ->name('metal_types.update')
        ->middleware('admin.permission:metal_types.edit');
    Route::delete('metal-types/{metal_type}', [MetalTypeController::class, 'destroy'])
        ->name('metal_types.destroy')
        ->middleware('admin.permission:metal_types.delete');

    // Setting Types
    Route::get('setting-types', [SettingTypeController::class, 'index'])
        ->name('setting_types.index')
        ->middleware('admin.permission:setting_types.view');
    Route::get('setting-types/create', [SettingTypeController::class, 'create'])
        ->name('setting_types.create')
        ->middleware('admin.permission:setting_types.create');
    Route::post('setting-types', [SettingTypeController::class, 'store'])
        ->name('setting_types.store')
        ->middleware('admin.permission:setting_types.create');
    Route::get('setting-types/{setting_type}', [SettingTypeController::class, 'show'])
        ->name('setting_types.show')
        ->middleware('admin.permission:setting_types.view');
    Route::get('setting-types/{setting_type}/edit', [SettingTypeController::class, 'edit'])
        ->name('setting_types.edit')
        ->middleware('admin.permission:setting_types.edit');
    Route::put('setting-types/{setting_type}', [SettingTypeController::class, 'update'])
        ->name('setting_types.update')
        ->middleware('admin.permission:setting_types.edit');
    Route::delete('setting-types/{setting_type}', [SettingTypeController::class, 'destroy'])
        ->name('setting_types.destroy')
        ->middleware('admin.permission:setting_types.delete');

    // Closure Types
    Route::get('closure-types', [ClosureTypeController::class, 'index'])
        ->name('closure_types.index')
        ->middleware('admin.permission:closure_types.view');
    Route::get('closure-types/create', [ClosureTypeController::class, 'create'])
        ->name('closure_types.create')
        ->middleware('admin.permission:closure_types.create');
    Route::post('closure-types', [ClosureTypeController::class, 'store'])
        ->name('closure_types.store')
        ->middleware('admin.permission:closure_types.create');
    Route::get('closure-types/{closure_type}', [ClosureTypeController::class, 'show'])
        ->name('closure_types.show')
        ->middleware('admin.permission:closure_types.view');
    Route::get('closure-types/{closure_type}/edit', [ClosureTypeController::class, 'edit'])
        ->name('closure_types.edit')
        ->middleware('admin.permission:closure_types.edit');
    Route::put('closure-types/{closure_type}', [ClosureTypeController::class, 'update'])
        ->name('closure_types.update')
        ->middleware('admin.permission:closure_types.edit');
    Route::delete('closure-types/{closure_type}', [ClosureTypeController::class, 'destroy'])
        ->name('closure_types.destroy')
        ->middleware('admin.permission:closure_types.delete');

    // Ring Sizes
    Route::get('ring-sizes', [RingSizeController::class, 'index'])
        ->name('ring_sizes.index')
        ->middleware('admin.permission:ring_sizes.view');
    Route::get('ring-sizes/create', [RingSizeController::class, 'create'])
        ->name('ring_sizes.create')
        ->middleware('admin.permission:ring_sizes.create');
    Route::post('ring-sizes', [RingSizeController::class, 'store'])
        ->name('ring_sizes.store')
        ->middleware('admin.permission:ring_sizes.create');
    Route::get('ring-sizes/{ring_size}', [RingSizeController::class, 'show'])
        ->name('ring_sizes.show')
        ->middleware('admin.permission:ring_sizes.view');
    Route::get('ring-sizes/{ring_size}/edit', [RingSizeController::class, 'edit'])
        ->name('ring_sizes.edit')
        ->middleware('admin.permission:ring_sizes.edit');
    Route::put('ring-sizes/{ring_size}', [RingSizeController::class, 'update'])
        ->name('ring_sizes.update')
        ->middleware('admin.permission:ring_sizes.edit');
    Route::delete('ring-sizes/{ring_size}', [RingSizeController::class, 'destroy'])
        ->name('ring_sizes.destroy')
        ->middleware('admin.permission:ring_sizes.delete');

    // Stone Types
    Route::get('stone-types', [StoneTypeController::class, 'index'])
        ->name('stone_types.index')
        ->middleware('admin.permission:stone_types.view');
    Route::get('stone-types/create', [StoneTypeController::class, 'create'])
        ->name('stone_types.create')
        ->middleware('admin.permission:stone_types.create');
    Route::post('stone-types', [StoneTypeController::class, 'store'])
        ->name('stone_types.store')
        ->middleware('admin.permission:stone_types.create');
    Route::get('stone-types/{stone_type}', [StoneTypeController::class, 'show'])
        ->name('stone_types.show')
        ->middleware('admin.permission:stone_types.view');
    Route::get('stone-types/{stone_type}/edit', [StoneTypeController::class, 'edit'])
        ->name('stone_types.edit')
        ->middleware('admin.permission:stone_types.edit');
    Route::put('stone-types/{stone_type}', [StoneTypeController::class, 'update'])
        ->name('stone_types.update')
        ->middleware('admin.permission:stone_types.edit');
    Route::delete('stone-types/{stone_type}', [StoneTypeController::class, 'destroy'])
        ->name('stone_types.destroy')
        ->middleware('admin.permission:stone_types.delete');

    // Stone Shapes
    Route::get('stone-shapes', [StoneShapeController::class, 'index'])
        ->name('stone_shapes.index')
        ->middleware('admin.permission:stone_shapes.view');
    Route::get('stone-shapes/create', [StoneShapeController::class, 'create'])
        ->name('stone_shapes.create')
        ->middleware('admin.permission:stone_shapes.create');
    Route::post('stone-shapes', [StoneShapeController::class, 'store'])
        ->name('stone_shapes.store')
        ->middleware('admin.permission:stone_shapes.create');
    Route::get('stone-shapes/{stone_shape}', [StoneShapeController::class, 'show'])
        ->name('stone_shapes.show')
        ->middleware('admin.permission:stone_shapes.view');
    Route::get('stone-shapes/{stone_shape}/edit', [StoneShapeController::class, 'edit'])
        ->name('stone_shapes.edit')
        ->middleware('admin.permission:stone_shapes.edit');
    Route::put('stone-shapes/{stone_shape}', [StoneShapeController::class, 'update'])
        ->name('stone_shapes.update')
        ->middleware('admin.permission:stone_shapes.edit');
    Route::delete('stone-shapes/{stone_shape}', [StoneShapeController::class, 'destroy'])
        ->name('stone_shapes.destroy')
        ->middleware('admin.permission:stone_shapes.delete');

    // Stone Colors
    Route::get('stone-colors', [StoneColorController::class, 'index'])
        ->name('stone_colors.index')
        ->middleware('admin.permission:stone_colors.view');
    Route::get('stone-colors/create', [StoneColorController::class, 'create'])
        ->name('stone_colors.create')
        ->middleware('admin.permission:stone_colors.create');
    Route::post('stone-colors', [StoneColorController::class, 'store'])
        ->name('stone_colors.store')
        ->middleware('admin.permission:stone_colors.create');
    Route::get('stone-colors/{stone_color}', [StoneColorController::class, 'show'])
        ->name('stone_colors.show')
        ->middleware('admin.permission:stone_colors.view');
    Route::get('stone-colors/{stone_color}/edit', [StoneColorController::class, 'edit'])
        ->name('stone_colors.edit')
        ->middleware('admin.permission:stone_colors.edit');
    Route::put('stone-colors/{stone_color}', [StoneColorController::class, 'update'])
        ->name('stone_colors.update')
        ->middleware('admin.permission:stone_colors.edit');
    Route::delete('stone-colors/{stone_color}', [StoneColorController::class, 'destroy'])
        ->name('stone_colors.destroy')
        ->middleware('admin.permission:stone_colors.delete');

    // Diamond Clarities
    Route::get('diamond-clarities', [DiamondClarityController::class, 'index'])
        ->name('diamond_clarities.index')
        ->middleware('admin.permission:diamond_clarities.view');
    Route::get('diamond-clarities/create', [DiamondClarityController::class, 'create'])
        ->name('diamond_clarities.create')
        ->middleware('admin.permission:diamond_clarities.create');
    Route::post('diamond-clarities', [DiamondClarityController::class, 'store'])
        ->name('diamond_clarities.store')
        ->middleware('admin.permission:diamond_clarities.create');
    Route::get('diamond-clarities/{diamond_clarity}', [DiamondClarityController::class, 'show'])
        ->name('diamond_clarities.show')
        ->middleware('admin.permission:diamond_clarities.view');
    Route::get('diamond-clarities/{diamond_clarity}/edit', [DiamondClarityController::class, 'edit'])
        ->name('diamond_clarities.edit')
        ->middleware('admin.permission:diamond_clarities.edit');
    Route::put('diamond-clarities/{diamond_clarity}', [DiamondClarityController::class, 'update'])
        ->name('diamond_clarities.update')
        ->middleware('admin.permission:diamond_clarities.edit');
    Route::delete('diamond-clarities/{diamond_clarity}', [DiamondClarityController::class, 'destroy'])
        ->name('diamond_clarities.destroy')
        ->middleware('admin.permission:diamond_clarities.delete');

    // Diamond Cuts
    Route::get('diamond-cuts', [DiamondCutController::class, 'index'])
        ->name('diamond_cuts.index')
        ->middleware('admin.permission:diamond_cuts.view');
    Route::get('diamond-cuts/create', [DiamondCutController::class, 'create'])
        ->name('diamond_cuts.create')
        ->middleware('admin.permission:diamond_cuts.create');
    Route::post('diamond-cuts', [DiamondCutController::class, 'store'])
        ->name('diamond_cuts.store')
        ->middleware('admin.permission:diamond_cuts.create');
    Route::get('diamond-cuts/{diamond_cut}', [DiamondCutController::class, 'show'])
        ->name('diamond_cuts.show')
        ->middleware('admin.permission:diamond_cuts.view');
    Route::get('diamond-cuts/{diamond_cut}/edit', [DiamondCutController::class, 'edit'])
        ->name('diamond_cuts.edit')
        ->middleware('admin.permission:diamond_cuts.edit');
    Route::put('diamond-cuts/{diamond_cut}', [DiamondCutController::class, 'update'])
        ->name('diamond_cuts.update')
        ->middleware('admin.permission:diamond_cuts.edit');
    Route::delete('diamond-cuts/{diamond_cut}', [DiamondCutController::class, 'destroy'])
        ->name('diamond_cuts.destroy')
        ->middleware('admin.permission:diamond_cuts.delete');


    // Company CRUD
    Route::get('companies', [CompanyController::class, 'index'])
        ->name('companies.index')
        ->middleware('admin.permission:companies.view');
    Route::get('companies/create', [CompanyController::class, 'create'])
        ->name('companies.create')
        ->middleware('admin.permission:companies.create');
    Route::post('companies', [CompanyController::class, 'store'])
        ->name('companies.store')
        ->middleware('admin.permission:companies.create');
    Route::get('companies/{company}', [CompanyController::class, 'show'])
        ->name('companies.show')
        ->middleware('admin.permission:companies.view');
    Route::get('companies/{company}/edit', [CompanyController::class, 'edit'])
        ->name('companies.edit')
        ->middleware('admin.permission:companies.edit');
    Route::put('companies/{company}', [CompanyController::class, 'update'])
        ->name('companies.update')
        ->middleware('admin.permission:companies.edit');
    Route::delete('companies/{company}', [CompanyController::class, 'destroy'])
        ->name('companies.destroy')
        ->middleware('admin.permission:companies.delete');

    // Diamond Import/Export  (KEEP THESE FIRST)
    // Rate limited: Import is CPU/memory intensive
    Route::post('diamonds/import', [DiamondController::class, 'import'])
        ->name('diamonds.import')
        ->middleware('throttle:10,1'); // 10 imports per minute

    // Import result page - shows success/failure count with error report download
    Route::get('diamonds/import-result', [DiamondController::class, 'importResult'])
        ->name('diamond.import.result');

    // Download error report Excel file with failed rows
    Route::get('diamonds/download-errors/{fileName}', [DiamondController::class, 'downloadErrorReport'])
        ->name('diamond.download-errors');

    // Rate limited: Export can be heavy on large datasets
    Route::get('diamonds/export', [DiamondController::class, 'export'])
        ->name('diamonds.export')
        ->middleware('throttle:20,1'); // 20 exports per minute

    // Background Job Routes
    Route::get('diamonds/jobs/history', [DiamondController::class, 'jobHistory'])
        ->name('diamond.job.history');
    Route::get('diamonds/jobs/{id}', [DiamondController::class, 'jobStatus'])
        ->name('diamond.job.status');
    Route::get('diamonds/jobs/{id}/status-json', [DiamondController::class, 'jobStatusJson'])
        ->name('diamond.job.status.json');
    Route::get('diamonds/jobs/{id}/download', [DiamondController::class, 'jobDownload'])
        ->name('diamond.job.download');

    // Diamond SKU availability check for orders (real-time validation)
    Route::get('diamonds/check-sku', [DiamondController::class, 'checkSkuAvailability'])
        ->name('diamond.check-sku')
        ->middleware('admin.permission:orders.create');

    // Diamond CRUD 
    Route::get('diamonds/create', [DiamondController::class, 'create'])
        ->name('diamond.create')
        ->middleware('admin.permission:diamonds.create');
    Route::post('diamonds', [DiamondController::class, 'store'])
        ->name('diamond.store')
        ->middleware('admin.permission:diamonds.create');
    Route::get('diamonds', [DiamondController::class, 'index'])
        ->name('diamond.index')
        ->middleware('admin.permission:diamonds.view');

    // Restock sold diamonds
    // Rate limited: Prevents accidental duplicate restocks
    Route::post('diamonds/{diamond}/restock', [DiamondController::class, 'restockAction'])
        ->name('diamond.restock')
        ->middleware('throttle:30,1'); // 30 restocks per minute
    Route::get('diamonds/{diamond}', [DiamondController::class, 'show'])
        ->name('diamond.show')
        ->middleware('admin.permission:diamonds.view');
    Route::get('diamonds/{diamond}/edit', [DiamondController::class, 'edit'])
        ->name('diamond.edit')
        ->middleware('admin.permission:diamonds.edit');
    Route::put('diamonds/{diamond}', [DiamondController::class, 'update'])
        ->name('diamond.update')
        ->middleware('admin.permission:diamonds.edit');
    Route::delete('diamonds/{diamond}', [DiamondController::class, 'destroy'])
        ->name('diamond.destroy')
        ->middleware('admin.permission:diamonds.delete');
    Route::post('diamonds/{diamond}/assign', [DiamondController::class, 'assignToAdmin'])
        ->name('diamond.assign')
        ->middleware('admin.permission:diamonds.assign');

    // Bulk Edit Routes
    Route::get('diamonds/bulk-edit/diamonds', [DiamondController::class, 'bulkEditDiamonds'])
        ->name('diamond.bulk-edit.diamonds')
        ->middleware('admin.permission:diamonds.edit');
    Route::post('diamonds/bulk-edit', [DiamondController::class, 'bulkEdit'])
        ->name('diamond.bulk-edit')
        ->middleware(['admin.permission:diamonds.edit', 'throttle:10,1']);

    // Notification Routes
    Route::get('notifications', [AdminController::class, 'showNotifications'])
        ->name('notifications.index');
    Route::post('notifications/{notification}/read', [AdminController::class, 'markNotificationAsRead'])
        ->name('notifications.read');
    Route::post('notifications/mark-all-read', [AdminController::class, 'markAllNotificationsAsRead'])
        ->name('notifications.mark-all-read');

    // Parties CRUD
    Route::get('parties', [PartyController::class, 'index'])
        ->name('parties.index')
        ->middleware('admin.permission:parties.view');
    Route::get('parties/create', [PartyController::class, 'create'])
        ->name('parties.create')
        ->middleware('admin.permission:parties.create');
    Route::post('parties', [PartyController::class, 'store'])
        ->name('parties.store')
        ->middleware('admin.permission:parties.create');
    Route::get('parties/{party}', [PartyController::class, 'show'])
        ->name('parties.show')
        ->middleware('admin.permission:parties.view');
    Route::get('parties/{party}/edit', [PartyController::class, 'edit'])
        ->name('parties.edit')
        ->middleware('admin.permission:parties.edit');
    Route::put('parties/{party}', [PartyController::class, 'update'])
        ->name('parties.update')
        ->middleware('admin.permission:parties.edit');
    Route::delete('parties/{party}', [PartyController::class, 'destroy'])
        ->name('parties.destroy')
        ->middleware('admin.permission:parties.delete');

    // ─────────────────────────────────────────────────────────────
    // Lead Management Module
    // ─────────────────────────────────────────────────────────────

    // Lead Inbox (Kanban Board)
    Route::get('leads', [\App\Http\Controllers\LeadController::class, 'index'])
        ->name('leads.index')
        ->middleware('admin.permission:leads.view');

    // Lead Analytics
    Route::get('leads/analytics', [\App\Http\Controllers\LeadController::class, 'analytics'])
        ->name('leads.analytics')
        ->middleware('admin.permission:leads.view');

    // Lead CRUD
    Route::post('leads', [\App\Http\Controllers\LeadController::class, 'store'])
        ->name('leads.store')
        ->middleware('admin.permission:leads.create');

    Route::get('leads/{lead}', [\App\Http\Controllers\LeadController::class, 'show'])
        ->name('leads.show')
        ->middleware('admin.permission:leads.view');

    Route::put('leads/{lead}', [\App\Http\Controllers\LeadController::class, 'update'])
        ->name('leads.update')
        ->middleware('admin.permission:leads.edit');

    Route::delete('leads/{lead}', [\App\Http\Controllers\LeadController::class, 'destroy'])
        ->name('leads.destroy')
        ->middleware('admin.permission:leads.delete');

    // Lead Actions
    Route::patch('leads/{lead}/status', [\App\Http\Controllers\LeadController::class, 'updateStatus'])
        ->name('leads.updateStatus')
        ->middleware('admin.permission:leads.edit');

    Route::post('leads/{lead}/assign', [\App\Http\Controllers\LeadController::class, 'assign'])
        ->name('leads.assign')
        ->middleware('admin.permission:leads.assign');

    Route::post('leads/{lead}/message', [\App\Http\Controllers\LeadController::class, 'sendMessage'])
        ->name('leads.sendMessage')
        ->middleware(['admin.permission:leads.message', 'throttle:meta-api']);

    Route::post('leads/{lead}/note', [\App\Http\Controllers\LeadController::class, 'addNote'])
        ->name('leads.addNote')
        ->middleware('admin.permission:leads.edit');

    // Bulk Operations
    Route::post('leads/bulk-action', [\App\Http\Controllers\LeadController::class, 'bulkAction'])
        ->name('leads.bulkAction')
        ->middleware(['admin.permission:leads.edit', 'throttle:critical-ops']);

    // ─────────────────────────────────────────────────────────────
    // Meta Settings Routes
    // ─────────────────────────────────────────────────────────────

    Route::prefix('settings/meta')->name('settings.meta.')->middleware(['admin.permission:meta_leads.settings', 'throttle:meta-api'])->group(function () {
        Route::get('/', [\App\Http\Controllers\MetaSettingsController::class, 'index'])
            ->name('index');

        Route::post('/', [\App\Http\Controllers\MetaSettingsController::class, 'store'])
            ->name('store');

        Route::post('/{account}/toggle', [\App\Http\Controllers\MetaSettingsController::class, 'toggle'])
            ->name('toggle');

        Route::post('/{account}/refresh', [\App\Http\Controllers\MetaSettingsController::class, 'refresh'])
            ->name('refresh');

        Route::delete('/{account}', [\App\Http\Controllers\MetaSettingsController::class, 'destroy'])
            ->name('destroy');

        Route::post('/test-webhook', [\App\Http\Controllers\MetaSettingsController::class, 'testWebhook'])
            ->name('test-webhook');
    });

    // ─────────────────────────────────────────────────────────────
    // Purchase Tracker Module
    // ─────────────────────────────────────────────────────────────
    Route::get('purchases', [PurchaseController::class, 'index'])
        ->name('purchases.index')
        ->middleware('admin.permission:purchases.view');
    Route::get('purchases/create', [PurchaseController::class, 'create'])
        ->name('purchases.create')
        ->middleware('admin.permission:purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])
        ->name('purchases.store')
        ->middleware('admin.permission:purchases.create');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])
        ->name('purchases.show')
        ->middleware('admin.permission:purchases.view');
    Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])
        ->name('purchases.edit')
        ->middleware('admin.permission:purchases.edit');
    Route::put('purchases/{purchase}', [PurchaseController::class, 'update'])
        ->name('purchases.update')
        ->middleware('admin.permission:purchases.edit');
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])
        ->name('purchases.destroy')
        ->middleware('admin.permission:purchases.delete');

    // ─────────────────────────────────────────────────────────────
    // Office Expense Manager Module
    // ─────────────────────────────────────────────────────────────

    // Reports (must be before resource route)
    Route::get('expenses/report/monthly', [ExpenseController::class, 'monthlyReport'])
        ->name('expenses.monthly-report')
        ->middleware('admin.permission:expenses.reports');
    Route::get('expenses/report/annual', [ExpenseController::class, 'annualReport'])
        ->name('expenses.annual-report')
        ->middleware('admin.permission:expenses.reports');

    // Excel Exports
    Route::get('expenses/export/monthly', [ExpenseController::class, 'exportMonthly'])
        ->name('expenses.export-monthly')
        ->middleware('admin.permission:expenses.reports');
    Route::get('expenses/export/annual', [ExpenseController::class, 'exportAnnual'])
        ->name('expenses.export-annual')
        ->middleware('admin.permission:expenses.reports');

    // Expense CRUD
    Route::get('expenses', [ExpenseController::class, 'index'])
        ->name('expenses.index')
        ->middleware('admin.permission:expenses.view');
    Route::get('expenses/create', [ExpenseController::class, 'create'])
        ->name('expenses.create')
        ->middleware('admin.permission:expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])
        ->name('expenses.store')
        ->middleware('admin.permission:expenses.create');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])
        ->name('expenses.show')
        ->middleware('admin.permission:expenses.view');
    Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])
        ->name('expenses.edit')
        ->middleware('admin.permission:expenses.edit');
    Route::put('expenses/{expense}', [ExpenseController::class, 'update'])
        ->name('expenses.update')
        ->middleware('admin.permission:expenses.edit');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->name('expenses.destroy')
        ->middleware('admin.permission:expenses.delete');

});

// ─────────────────────────────────────────────────────────────
// Meta Webhook Routes (Outside auth middleware)
// ─────────────────────────────────────────────────────────────

Route::prefix('webhook/meta')->group(function () {
    // Webhook verification (GET)
    Route::get('/', [\App\Http\Controllers\MetaWebhookController::class, 'verify']);

    // Webhook events (POST) - Must exclude from CSRF
    Route::post('/', [\App\Http\Controllers\MetaWebhookController::class, 'handle'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
});


