<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

/**
 * Admin Management Controller
 * Handles admin CRUD operations with document uploads (Aadhar, bank passbook)
 */
class AdminController extends Controller
{

    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.view')) {
            abort(403, 'Unauthorized');
        }

        $query = Admin::where('id', '<>', $current->id);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admins.index', compact('admins'));
    }

    public function create()
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.create')) {
            abort(403, 'Unauthorized');
        }
        return view('admins.create');
    }

    public function store(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.create')) {
            abort(403, 'Unauthorized');
        }

        $rules = [
            'name' => 'required|string|unique:admins,name',
            'email' => 'required|email|unique:admins,email',
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => 'required|same:password',
            'phone_number' => 'required|numeric',
            'country_code' => 'nullable|string',
            'address' => 'nullable|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'pincode' => 'nullable|numeric',
            'aadhar_front_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'aadhar_back_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bank_passbook_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'family_member_phone' => 'nullable|numeric',
        ];

        try {
            $validated = $request->validate($rules);

            DB::beginTransaction();

            $data = $request->only([
                'name',
                'email',
                'phone_number',
                'country_code',
                'address',
                'country',
                'state',
                'city',
                'pincode',
                'family_member_phone'
            ]);

            $data['password'] = Hash::make($validated['password']);

            // Create admin first
            $admin = Admin::create($data);

            // Upload documents - failure here won't prevent admin creation
            $uploadedDocs = [];
            $failedDocs = [];
            foreach (['aadhar_front_image', 'aadhar_back_image', 'bank_passbook_image'] as $field) {
                try {
                    $uploadPath = $this->storeAdminDocument($request, $field);
                    if ($uploadPath) {
                        $admin->{$field} = $uploadPath;
                        $uploadedDocs[] = $field;
                    }
                } catch (\Exception $e) {
                    $failedDocs[] = $field;
                    Log::warning('Admin document upload failed', [
                        'admin_id' => $admin->id,
                        'field' => $field,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (!empty($uploadedDocs)) {
                $admin->save();
            }

            DB::commit();

            Log::info('Admin created', [
                'admin_id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'uploaded_docs' => $uploadedDocs,
                'failed_docs' => $failedDocs,
                'created_by' => $current->id
            ]);

            $message = 'Admin created successfully.';
            if (!empty($failedDocs)) {
                $message .= ' Some documents failed to upload: ' . implode(', ', $failedDocs);
            }

            return redirect()->route('admins.index')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'created_by' => $current->id
            ]);
            return back()->withInput()->with('error', 'Failed to create admin. Please try again.');
        }
    }

    public function edit(Admin $admin)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.edit')) {
            abort(403, 'Unauthorized');
        }
        return view('admins.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.edit')) {
            abort(403, 'Unauthorized');
        }

        $rules = [
            'name' => 'required|string|unique:admins,name,' . $admin->id,
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => ['nullable', 'string', 'min:8'],
            'confirm_password' => 'nullable|same:password',
            'phone_number' => 'required|numeric',
            'country_code' => 'nullable|string',
            'address' => 'nullable|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'pincode' => 'nullable|numeric',
            'aadhar_front_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'aadhar_back_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bank_passbook_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'family_member_phone' => 'nullable|numeric',
        ];

        try {
            $validated = $request->validate($rules);

            DB::beginTransaction();

            $changes = [];
            $data = $request->only([
                'name',
                'email',
                'phone_number',
                'country_code',
                'address',
                'country',
                'state',
                'city',
                'pincode',
                'family_member_phone'
            ]);

            // Track what changed
            foreach ($data as $key => $value) {
                if ($admin->{$key} != $value) {
                    $changes[] = $key;
                }
            }

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
                $changes[] = 'password';
            }

            // Update basic data first
            $admin->update($data);

            // Handle document uploads - failure here won't rollback admin update
            $uploadedDocs = [];
            $failedDocs = [];
            foreach (['aadhar_front_image', 'aadhar_back_image', 'bank_passbook_image'] as $field) {
                try {
                    $uploadPath = $this->storeAdminDocument($request, $field, $admin->{$field});
                    if ($uploadPath) {
                        $admin->{$field} = $uploadPath;
                        $uploadedDocs[] = $field;
                        $changes[] = $field;
                    }
                } catch (\Exception $e) {
                    $failedDocs[] = $field;
                    Log::warning('Admin document update failed', [
                        'admin_id' => $admin->id,
                        'field' => $field,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (!empty($uploadedDocs)) {
                $admin->save();
            }

            DB::commit();

            Log::info('Admin updated', [
                'admin_id' => $admin->id,
                'changes' => $changes,
                'uploaded_docs' => $uploadedDocs,
                'failed_docs' => $failedDocs,
                'updated_by' => $current->id
            ]);

            $message = 'Admin updated successfully.';
            if (!empty($failedDocs)) {
                $message .= ' Some documents failed to upload: ' . implode(', ', $failedDocs);
            }

            return redirect()->route('admins.index')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin update failed', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'updated_by' => $current->id
            ]);
            return back()->withInput()->with('error', 'Failed to update admin. Please try again.');
        }
    }

    public function show(Admin $admin)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.view')) {
            abort(403, 'Unauthorized');
        }
        return view('admins.show', compact('admin'));
    }

    public function destroy(Admin $admin)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.delete')) {
            abort(403, 'Unauthorized');
        }

        if ($admin->id === $current->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        try {
            DB::beginTransaction();

            $adminData = [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email
            ];

            // Reassign related records to the current admin (or another designated admin)
            \App\Models\Channel::where('created_by', $admin->id)->update(['created_by' => $current->id]);
            \App\Models\Message::where('sender_id', $admin->id)->update(['sender_id' => $current->id]);

            // Delete non-critical related records
            \App\Models\MessageRead::where('user_id', $admin->id)->delete();


            // Delete associated documents
            $deletedDocs = [];
            $failedDocs = [];
            foreach (['aadhar_front_image', 'aadhar_back_image', 'bank_passbook_image'] as $field) {
                if ($admin->{$field}) {
                    try {
                        if (Storage::disk('public')->delete($admin->{$field})) {
                            $deletedDocs[] = $field;
                        } else {
                            $failedDocs[] = $field;
                        }
                    } catch (\Exception $e) {
                        $failedDocs[] = $field;
                        Log::warning('Failed to delete admin document', [
                            'admin_id' => $admin->id,
                            'field' => $field,
                            'path' => $admin->{$field},
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            $admin->clearPermissionCache();
            $admin->delete();

            DB::commit();

            Log::info('Admin deleted', [
                'admin' => $adminData,
                'deleted_docs' => $deletedDocs,
                'failed_docs' => $failedDocs,
                'deleted_by' => $current->id
            ]);

            return redirect()->route('admins.index')->with('success', 'Admin deleted successfully. Associated channels and messages have been reassigned.');
        } catch (QueryException $e) {
            DB::rollBack();
            // Check for foreign key constraint violation
            if ($e->errorInfo[1] == 1451) {
                Log::warning('Admin deletion blocked by foreign key constraint', [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                    'deleted_by' => $current->id,
                ]);
                return back()->with('error', 'This admin cannot be deleted because they are linked to other data (e.g., they created channels or messages). Please reassign or remove the associated data first.');
            }

            // For other database-related errors
            Log::error('Admin deletion failed with a database error', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'deleted_by' => $current->id
            ]);
            return back()->with('error', 'Failed to delete admin due to a database error. Please try again.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin deletion failed', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'deleted_by' => $current->id
            ]);
            return back()->with('error', 'Failed to delete admin. An unexpected error occurred.');
        }
    }

    private function storeAdminDocument(Request $request, string $field, ?string $existingPath = null): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);

        if ($existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'dat';
        $filename = Str::uuid()->toString() . '.' . $extension;

        $storedPath = Storage::disk('public')->putFileAs('admins', $file, $filename);

        return is_string($storedPath) ? $storedPath : null;
    }

    public function showPermissions(Admin $admin)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.assign_permissions')) {
            abort(403, 'Unauthorized');
        }

        $permissionsByCategory = \App\Models\Permission::getGroupedPermissions();
        $assignedPermissions = \App\Models\Permission::getAdminPermissions($admin->id);

        return view('admins.permissions', compact('admin', 'permissionsByCategory', 'assignedPermissions'));
    }

    public function updatePermissions(Request $request, Admin $admin)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.assign_permissions')) {
            abort(403, 'Unauthorized');
        }

        try {
            DB::beginTransaction();

            $oldPermissions = $admin->permissions()->pluck('permissions.id')->toArray();
            $newPermissions = $request->input('permissions', []);

            $admin->permissions()->sync($newPermissions);

            // Clear the cache for this admin's permissions
            Cache::tags(['permissions', 'admin_permissions'])->forget('admin_permissions_' . $admin->id);

            DB::commit();

            $added = array_diff($newPermissions, $oldPermissions);
            $removed = array_diff($oldPermissions, $newPermissions);

            Log::info('Admin permissions updated', [
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'permissions_added' => count($added),
                'permissions_removed' => count($removed),
                'total_permissions' => count($newPermissions),
                'updated_by' => $current->id
            ]);

            return redirect()
                ->route('admins.permissions.show', $admin)
                ->with('success', 'Permissions updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission update failed', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'updated_by' => $current->id
            ]);
            return back()->with('error', 'Failed to update permissions. Please try again.');
        }
    }

    /**
     * Mark a single notification as read
     */
    public function markNotificationAsRead(Request $request, $notificationId)
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notification = $admin->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $admin->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Show all notifications for the current admin
     */
    public function showNotifications()
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            abort(401, 'Unauthorized');
        }

        // Get all notifications (both read and unread)
        $notifications = $admin->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }
}
