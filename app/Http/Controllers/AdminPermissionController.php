<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPermissionController extends Controller
{
    public function show(Admin $admin)
    {
        $currentAdmin = $this->currentAdmin();

        if (!$currentAdmin || !$currentAdmin->hasPermission('admins.assign_permissions')) {
            abort(403, 'Unauthorized');
        }

        // Load all permissions grouped by category
        $permissionsByCategory = Permission::orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        // Get the IDs of assigned permissions
        $assignedPermissions = $admin->load('permissions')
            ->permissions
            ->pluck('id')
            ->toArray();

        return view('admins.permissions', compact('admin', 'permissionsByCategory', 'assignedPermissions'));
    }

    public function update(Request $request, Admin $admin)
    {
        $currentAdmin = $this->currentAdmin();

        if (!$currentAdmin || !$currentAdmin->hasPermission('admins.assign_permissions')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'is_super' => 'nullable',
        ]);

        // Update Super Admin status (hidden input sends '1' or '0')
        $isSuperValue = $request->input('is_super') === '1';
        $admin->is_super = $isSuperValue;
        $admin->save();

        Log::info('Super Admin status update attempt', [
            'admin_id' => $admin->id,
            'request_has_is_super' => $request->has('is_super'),
            'request_is_super_value' => $request->input('is_super'),
            'new_is_super_value' => $isSuperValue,
            'admin_is_super_after_save' => $admin->fresh()->is_super,
        ]);

        $newPermissionIds = $request->input('permissions', []);
        $admin->permissions()->sync($newPermissionIds);
        $admin->clearPermissionCache();

        // Auto-membership policy for chat.access
        $chatPermId = Permission::where('slug', 'chat.access')->value('id');
        $chatAccessAssigned = $chatPermId ? in_array($chatPermId, $newPermissionIds) : false;

        if ($chatAccessAssigned) {
            // Ensure General channel exists and attach admin as member
            $general = Channel::firstOrCreate(
                ['name' => 'General'],
                [
                    'type' => 'group',
                    'created_by' => $this->currentAdmin()->id ?? $admin->id,
                    'description' => 'General chat channel for all administrators'
                ]
            );
            if (!$general->hasMember($admin)) {
                $general->users()->attach($admin->id);
            }
        } else {
            // Policy A: when chat.access revoked, detach from all channels
            // (If you prefer to retain memberships, remove the following line)
            $admin->channels()->detach();
        }

        Log::info('Admin permissions updated', [
            'acted_by' => $currentAdmin->id,
            'target_admin' => $admin->id,
            'permission_ids' => $request->input('permissions', []),
        ]);

        return redirect()->route('admins.permissions.show', $admin)->with('success', 'Permissions updated');
    }
}
