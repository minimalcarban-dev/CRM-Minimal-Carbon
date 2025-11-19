<?php

use App\Http\Controllers\ClosureTypeController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MetalTypeController;
use App\Http\Controllers\RingSizeController;
use App\Http\Controllers\SettingTypeController;
use App\Http\Controllers\StoneColorController;
use App\Http\Controllers\StoneShapeController;
use App\Http\Controllers\StoneTypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPermissionController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DiamondController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('admin/login');
    // return view('welcome');
});

// Admin auth routes
Route::get('admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Broadcasting auth is registered in BroadcastServiceProvider with admin guard and admin prefix

// Admin CRUD routes (protected)
Route::middleware(['admin.auth'])->prefix('admin')->group(function () {
    // Lightweight dashboard landing for safe redirects
    Route::get('dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Chat routes with middleware
    Route::get('chat', [ChatController::class, 'index'])
        ->name('chat.index')
        ->middleware('admin.permission:chat.access');

    Route::prefix('chat')->middleware('admin.permission:chat.access')->group(function () {
        Route::post('/channels', [ChatController::class, 'createChannel']);
        Route::get('/channels', [ChatController::class, 'getChannels']);
        Route::post('/direct', [ChatController::class, 'direct']);
        Route::get('/channels/{channel}/messages', [ChatController::class, 'getMessages']);
        Route::post('/channels/{channel}/messages', [ChatController::class, 'sendMessage']);
        Route::post('/channels/{channel}/read', [ChatController::class, 'markAsRead']);
        Route::get('/channels/{channel}/sidebar', [ChatController::class, 'sidebar']);
        Route::get('/messages/search', [ChatController::class, 'searchMessages']);
        // Admins list for channel creation (super admin only)
        Route::get('/admins', [ChatController::class, 'listAdmins']);
        // Manage members (owner or super admin)
        Route::get('/channels/{channel}/members', [ChatController::class, 'getChannelMembers']);
        Route::put('/channels/{channel}/members', [ChatController::class, 'updateChannelMembers']);
    });
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
        ->name('permissions.destroy')
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

    // Notification Routes
    Route::get('notifications', [AdminController::class, 'showNotifications'])
        ->name('notifications.index');
    Route::post('notifications/{notification}/read', [AdminController::class, 'markNotificationAsRead'])
        ->name('notifications.read');
    Route::post('notifications/mark-all-read', [AdminController::class, 'markAllNotificationsAsRead'])
        ->name('notifications.mark-all-read');
});
