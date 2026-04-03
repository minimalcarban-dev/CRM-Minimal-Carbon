<?php

namespace App\Http\Middleware;

use App\Models\Admin;
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

        // Super admin is always allowed.
        if ($admin->is_super) {
            return $next($request);
        }

        // Permission check for normal admins.
        if ($permission && $admin->hasPermission($permission)) {
            return $next($request);
        }

        $moduleName = 'this module';
        if ($permission) {
            $parts = explode('.', $permission);
            $moduleName = ucfirst($parts[0]);
        }

        // For AJAX/API requests, return JSON instead of HTML redirect.
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => "You do not have permission to access the {$moduleName} module.",
                'error' => 'forbidden',
                'permission' => $permission,
            ], 403);
        }

        // For normal page requests, keep redirect behavior.
        return redirect()->route('admin.dashboard')
            ->with('error', "Your permission to access the <strong>$moduleName</strong> module may have changed or been revoked, so you have been redirected here.");
    }
}
