<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminHasPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Delegate to Admin::hasPermission() which handles super admin bypass
        // (including strict-prefix enforcement for purchases, expenses, etc.)
        if (!$permission || $admin->hasPermission($permission)) {
            return $next($request);
        }

        $permissionName = 'this feature';
        if ($permission) {
            $permissionName = Permission::getFriendlyName($permission);
        }

        // For AJAX/API requests, return JSON instead of HTML redirect.
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => "You do not have permission to: {$permissionName}.",
                'error' => 'forbidden',
                'permission' => $permission,
            ], 403);
        }

        // For normal page requests, keep redirect behavior.
        return redirect()->route('admin.dashboard')
            ->with('error', "Your permission to: <strong>$permissionName</strong> may have changed or been revoked, so you have been redirected here.");
    }
}
