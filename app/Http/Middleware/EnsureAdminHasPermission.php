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
     * Usage in routes (if registered in Kernel): 'permission:admins.assign_permissions'
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            abort(403, 'Unauthorized');
        }

        if ($admin->is_super) {
            return $next($request);
        }

        if ($permission && $admin->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
