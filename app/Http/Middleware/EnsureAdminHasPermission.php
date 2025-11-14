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

        // Agar Admin logged in nahi hai, toh seedha 403 (ya login page par bhej sakte hain)
        if (!$admin) {
            // Agar aapki admin login route 'admin.login' hai
            return redirect()->route('admin.login');
        }

        // 1. Super Admin hamesha allowed hai
        if ($admin->is_super) {
            return $next($request);
        }

        // 2. Permission check
        if ($permission && $admin->hasPermission($permission)) {
            return $next($request);
        }

        // 3. 🛑 PERMISSION FAIL HONE PAR (The FIX)

        // Permission slug se module ka naam nikalte hain (e.g., 'orders.view' -> 'Orders')
        $moduleName = 'this module';
        if ($permission) {
            $parts = explode('.', $permission);
            $moduleName = ucfirst($parts[0]);
        }

        // User ko dashboard par redirect karein aur error message dein
        return redirect()->route('admin.dashboard') // 🚨 Apna sahi dashboard route name use karein
            ->with('error', "🚨 Your permission to access the <strong>$moduleName</strong> module may have changed or been revoked, so you have been redirected here.");

        // Old Code: abort(403, 'Unauthorized');
    }
}