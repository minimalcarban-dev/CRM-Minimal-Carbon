<?php

namespace App\Traits;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

/**
 * Reusable permission-checking trait for controllers.
 *
 * Replaces the repeated boilerplate pattern:
 *   $current = $this->currentAdmin();
 *   if (!$current || !$current->hasPermission('...')) abort(403);
 *
 * Usage in a controller:
 *   use \App\Traits\AuthorizesPermissions;
 *   $this->authorizePermission('orders.edit');
 */
trait AuthorizesPermissions
{
    /**
     * Abort with 403 unless the current admin has the given permission.
     *
     * Super admins bypass all permission checks via Admin::hasPermission().
     *
     * @param  string       $permission  Permission slug, e.g. 'orders.edit'
     * @param  string|null  $message     Optional custom error message
     */
    protected function authorizePermission(string $permission, ?string $message = null): void
    {
        $admin = $this->resolveAdmin();

        if (!$admin || !$admin->hasPermission($permission)) {
            abort(403, $message ?? 'You do not have permission to perform this action.');
        }
    }

    /**
     * Abort with 403 unless the current admin has an explicit permission
     * (not granted through super-admin status).
     *
     * @param  string       $permission  Permission slug
     * @param  string|null  $message     Optional custom error message
     */
    protected function authorizeExplicitPermission(string $permission, ?string $message = null): void
    {
        $admin = $this->resolveAdmin();

        if (!$admin || !$admin->hasExplicitPermission($permission)) {
            abort(403, $message ?? 'You do not have permission to perform this action.');
        }
    }

    /**
     * Check if the current admin has a given permission (non-aborting).
     *
     * @param  string  $permission  Permission slug
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        $admin = $this->resolveAdmin();
        return $admin && $admin->hasPermission($permission);
    }

    /**
     * Resolve the currently authenticated admin.
     */
    private function resolveAdmin(): ?Admin
    {
        // Prefer currentAdmin() if available on the controller (from base Controller)
        if (method_exists($this, 'currentAdmin')) {
            return $this->currentAdmin();
        }

        return Auth::guard('admin')->user();
    }
}
