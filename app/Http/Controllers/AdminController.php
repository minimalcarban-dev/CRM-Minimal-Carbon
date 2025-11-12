<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminController extends Controller
{

    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('admins.view')) {
            abort(403, 'Unauthorized');
        }

    $query = Admin::where('is_super', false)->where('id', '<>', $current->id);

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
        ];

        $validated = $request->validate($rules);

        $data = $request->only([
            'name',
            'email',
            'phone_number',
            'country_code',
            'address',
            'country',
            'state',
            'city',
            'pincode'
        ]);

        $data['password'] = Hash::make($validated['password']);

        foreach (['aadhar_front_image', 'aadhar_back_image', 'bank_passbook_image'] as $field) {
            $uploadPath = $this->storeAdminDocument($request, $field);
            if ($uploadPath) {
                $data[$field] = $uploadPath;
            }
        }

        Admin::create($data);

        return redirect()->route('admins.index')->with('success', 'Admin created successfully.');
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
        ];

        $validated = $request->validate($rules);

        $data = $request->only([
            'name',
            'email',
            'phone_number',
            'country_code',
            'address',
            'country',
            'state',
            'city',
            'pincode'
        ]);

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        foreach (['aadhar_front_image', 'aadhar_back_image', 'bank_passbook_image'] as $field) {
            $uploadPath = $this->storeAdminDocument($request, $field, $admin->{$field});
            if ($uploadPath) {
                $data[$field] = $uploadPath;
            }
        }

        $admin->update($data);

        return redirect()->route('admins.index')->with('success', 'Admin updated successfully.');
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

        foreach (['aadhar_front_image', 'aadhar_back_image', 'bank_passbook_image'] as $field) {
            if ($admin->{$field}) {
                Storage::disk('public')->delete($admin->{$field});
            }
        }

        $admin->clearPermissionCache();
        $admin->delete();

        return redirect()->route('admins.index')->with('success', 'Admin deleted successfully.');
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

        $permissions = $request->input('permissions', []);
        $admin->permissions()->sync($permissions);
        
        // Clear the cache for this admin's permissions
        Cache::tags(['permissions', 'admin_permissions'])->forget('admin_permissions_' . $admin->id);

        return redirect()
            ->route('admins.permissions.show', $admin)
            ->with('success', 'Permissions updated successfully.');
    }
}
