<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Base controller for simple CRUD operations
 * 
 * Extend this class for simple resource controllers to get:
 * - Database transactions
 * - Error handling with logging
 * - Caching support
 * - Permission checks
 * - Consistent response format
 */
abstract class BaseResourceController extends Controller
{
    /**
     * Get the model class name
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the view path (e.g., 'metal_types' for metal_types/index.blade.php)
     */
    abstract protected function getViewPath(): string;

    /**
     * Get the route name prefix (e.g., 'metal_types' for metal_types.index)
     */
    abstract protected function getRouteName(): string;

    /**
     * Get validation rules for store
     */
    abstract protected function getStoreRules(): array;

    /**
     * Get validation rules for update
     */
    abstract protected function getUpdateRules($id): array;

    /**
     * Get the permission prefix (e.g., 'metal_types' for metal_types.view)
     * Return null to skip permission checks
     */
    protected function getPermissionPrefix(): ?string
    {
        return null;
    }

    /**
     * Get cache key for listing
     */
    protected function getCacheKey(): string
    {
        return $this->getRouteName() . '_all';
    }

    /**
     * Get cache duration in seconds (default: 1 hour)
     */
    protected function getCacheDuration(): int
    {
        return 3600;
    }

    /**
     * Clear the cache
     */
    protected function clearCache(): void
    {
        Cache::forget($this->getCacheKey());
        Cache::forget($this->getRouteName() . '_list');
    }

    /**
     * Check permission
     */
    protected function checkPermission(string $action): void
    {
        $prefix = $this->getPermissionPrefix();
        if ($prefix === null) {
            return;
        }

        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission("{$prefix}.{$action}")) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Display a listing of the resource
     */
    public function index(Request $request)
    {
        $this->checkPermission('view');

        $modelClass = $this->getModelClass();
        $query = $modelClass::query();

        // Search functionality
        if ($q = $request->query('search')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view($this->getViewPath() . '.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource
     */
    public function create()
    {
        $this->checkPermission('create');
        return view($this->getViewPath() . '.create');
    }

    /**
     * Store a newly created resource in storage
     */
    public function store(Request $request)
    {
        $this->checkPermission('create');

        try {
            DB::beginTransaction();

            $validated = $request->validate($this->getStoreRules());
            $validated = $this->prepareDataForStore($validated, $request);

            $modelClass = $this->getModelClass();
            $item = $modelClass::create($validated);

            DB::commit();
            $this->clearCache();

            Log::info("{$modelClass} created", [
                'id' => $item->id,
                'name' => $item->name ?? null,
                'created_by' => auth('admin')->id()
            ]);

            return redirect()->route($this->getRouteName() . '.index')
                ->with('success', $this->getResourceName() . ' created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("{$this->getModelClass()} creation failed", [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            return back()->withInput()->with('error', 'Failed to create ' . $this->getResourceName());
        }
    }

    /**
     * Display the specified resource
     */
    public function show($id)
    {
        $this->checkPermission('view');

        $modelClass = $this->getModelClass();
        $item = $modelClass::findOrFail($id);

        return view($this->getViewPath() . '.show', ['item' => $item]);
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit($id)
    {
        $this->checkPermission('edit');

        $modelClass = $this->getModelClass();
        $item = $modelClass::findOrFail($id);

        return view($this->getViewPath() . '.edit', ['item' => $item]);
    }

    /**
     * Update the specified resource in storage
     */
    public function update(Request $request, $id)
    {
        $this->checkPermission('edit');

        try {
            DB::beginTransaction();

            $modelClass = $this->getModelClass();
            $item = $modelClass::findOrFail($id);

            $validated = $request->validate($this->getUpdateRules($id));
            $validated = $this->prepareDataForUpdate($validated, $request, $item);

            $item->update($validated);

            DB::commit();
            $this->clearCache();

            Log::info("{$modelClass} updated", [
                'id' => $item->id,
                'name' => $item->name ?? null,
                'updated_by' => auth('admin')->id()
            ]);

            return redirect()->route($this->getRouteName() . '.index')
                ->with('success', $this->getResourceName() . ' updated successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("{$this->getModelClass()} update failed", [
                'id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            return back()->withInput()->with('error', 'Failed to update ' . $this->getResourceName());
        }
    }

    /**
     * Remove the specified resource from storage
     */
    public function destroy($id)
    {
        $this->checkPermission('delete');

        try {
            DB::beginTransaction();

            $modelClass = $this->getModelClass();
            $item = $modelClass::findOrFail($id);

            $item->delete();

            DB::commit();
            $this->clearCache();

            Log::info("{$modelClass} deleted", [
                'id' => $id,
                'deleted_by' => auth('admin')->id()
            ]);

            return redirect()->route($this->getRouteName() . '.index')
                ->with('success', $this->getResourceName() . ' deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("{$this->getModelClass()} deletion failed", [
                'id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            return back()->with('error', 'Failed to delete ' . $this->getResourceName());
        }
    }

    /**
     * Prepare data before storing (override if needed)
     */
    protected function prepareDataForStore(array $validated, Request $request): array
    {
        // Handle is_active - supports both checkbox and radio button formats
        // Radio buttons send '1' or '0', checkboxes send 'on' or nothing
        if ($request->has('is_active')) {
            $validated['is_active'] = (bool) $request->input('is_active');
        } else {
            $validated['is_active'] = false;
        }

        return $validated;
    }

    /**
     * Prepare data before updating (override if needed)
     */
    protected function prepareDataForUpdate(array $validated, Request $request, $item): array
    {
        // Handle is_active - supports both checkbox and radio button formats
        // Radio buttons send '1' or '0', checkboxes send 'on' or nothing
        if ($request->has('is_active')) {
            $validated['is_active'] = (bool) $request->input('is_active');
        } else {
            $validated['is_active'] = false;
        }

        return $validated;
    }

    /**
     * Get human-readable resource name (override if needed)
     */
    protected function getResourceName(): string
    {
        // Convert snake_case to Title Case
        return ucwords(str_replace('_', ' ', $this->getRouteName()));
    }
}
