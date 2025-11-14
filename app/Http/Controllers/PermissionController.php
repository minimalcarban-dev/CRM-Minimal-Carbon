<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        // ensure admin is authenticated first
        $this->middleware('admin.auth');

        // viewing/listing permissions
        $this->middleware(function ($request, $next) {
            $admin = $this->currentAdmin();
            if (!$admin) {
                abort(403);
            }
            if ($admin->is_super || $admin->hasPermission('permissions.view')) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);

        // creating permissions
        $this->middleware(function ($request, $next) {
            $admin = $this->currentAdmin();
            if (!$admin) {
                abort(403);
            }
            if ($admin->is_super || $admin->hasPermission('permissions.create')) {
                return $next($request);
            }
            abort(403);
        })->only(['create', 'store']);

        // editing permissions
        $this->middleware(function ($request, $next) {
            $admin = $this->currentAdmin();
            if (!$admin) {
                abort(403);
            }
            if ($admin->is_super || $admin->hasPermission('permissions.edit')) {
                return $next($request);
            }
            abort(403);
        })->only(['edit', 'update']);

        // deleting permissions
        $this->middleware(function ($request, $next) {
            $admin = $this->currentAdmin();
            if (!$admin) {
                abort(403);
            }
            if ($admin->is_super || $admin->hasPermission('permissions.delete')) {
                return $next($request);
            }
            abort(403);
        })->only(['destroy']);
    }

    public function index()
    {
        $permissions = Permission::orderBy('id', 'asc')->get();
        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        // Provide existing permission categories so the form can suggest them
        $categories = Permission::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('permissions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Permission::create($data);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        return view('permissions.show', compact('permission'));
    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug,' . $permission->id,
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $permission->update($data);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.index')->with('success', 'Permission deleted.');
    }
}
