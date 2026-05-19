<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($permission) {
            self::clearCache();
            Cache::forget('permission_friendly_' . md5($permission->slug));
        });

        static::deleted(function ($permission) {
            self::clearCache();
            Cache::forget('permission_friendly_' . md5($permission->slug));
        });
    }

    /**
     * Clear permission caches safely depending on cache driver support.
     */
    protected static function clearCache()
    {
        $store = Cache::getStore();

        if (method_exists($store, 'tags')) {
            Cache::tags(['permissions', 'admin_permissions'])->flush();
        } else {
            Cache::forget('grouped_permissions');
            // also clear admin permissions cache fallback
            $keys = Cache::get('admin_permission_keys', []);
            if (is_array($keys)) {
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
            Cache::forget('admin_permission_keys');
        }
    }

    /**
     * Grouped permissions by category, cached safely.
     */
    public static function getGroupedPermissions()
    {
        $store = Cache::getStore();

        if (method_exists($store, 'tags')) {
            return Cache::tags(['permissions'])->remember('grouped_permissions', 3600, function () {
                return static::orderBy('category')->orderBy('name')->get()->groupBy('category');
            });
        }

        // Fallback for file/database cache
        return Cache::remember('grouped_permissions', 3600, function () {
            return static::orderBy('category')->orderBy('name')->get()->groupBy('category');
        });
    }

    /**
     * Cached admin permissions list, with safe fallback.
     */
    public static function getAdminPermissions($adminId)
    {
        $store = Cache::getStore();

        $cacheKey = 'admin_permissions_' . $adminId;

        if (method_exists($store, 'tags')) {
            return Cache::tags(['permissions', 'admin_permissions'])->remember(
                $cacheKey,
                3600,
                function () use ($adminId) {
                    return static::whereHas('admins', function ($query) use ($adminId) {
                        $query->where('admin_id', $adminId);
                    })->pluck('id')->toArray();
                }
            );
        }

        // Fallback for file cache
        $keys = Cache::get('admin_permission_keys', []);
        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put('admin_permission_keys', $keys, 3600);
        }

        return Cache::remember(
            $cacheKey,
            3600,
            function () use ($adminId) {
                return static::whereHas('admins', function ($query) use ($adminId) {
                    $query->where('admin_id', $adminId);
                })->pluck('id')->toArray();
            }
        );
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_permission');
    }

    /**
     * Get a friendly human-readable name for a permission slug.
     * Caches lookups for maximum performance.
     */
    public static function getFriendlyName(string $slug): string
    {
        return Cache::remember('permission_friendly_' . md5($slug), 86400, function () use ($slug) {
            $permission = static::where('slug', $slug)->first();
            if ($permission) {
                return $permission->name;
            }

            // Fallback parsing (e.g. 'melee_diamonds.create' -> 'Create Melee Diamonds')
            $parts = explode('.', $slug);
            if (count($parts) < 2) {
                return ucwords(str_replace(['_', '-'], ' ', $slug));
            }

            $module = str_replace(['_', '-'], ' ', $parts[0]);
            $action = str_replace(['_', '-'], ' ', $parts[1]);

            // Map common actions to cleaner verbs
            $actionMapping = [
                'view' => 'View',
                'create' => 'Create',
                'edit' => 'Edit',
                'delete' => 'Delete',
                'transaction' => 'Perform Transactions on',
                'access' => 'Access',
            ];

            $actionWord = $actionMapping[strtolower($action)] ?? ucwords($action);
            return $actionWord . ' ' . ucwords($module);
        });
    }
}
