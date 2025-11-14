<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Admin extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'country_code',
        'address',
        'country',
        'state',
        'city',
        'pincode',
        'aadhar_front_image',
        'aadhar_back_image',
        'bank_passbook_image',
        'is_super',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_super' => 'boolean',
    ];

    protected $attributes = [
        'is_super' => false,
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'admin_permission');
    }

    /**
     * Get all diamonds assigned to this admin (many-to-many)
     */
    public function diamonds(): BelongsToMany
    {
        return $this->belongsToMany(Diamond::class, 'diamond_admin', 'admin_id', 'diamond_id')
            ->withPivot('assign_by', 'assigned_at')
            ->withTimestamps();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->is_super) {
            return true;
        }

        return in_array($slug, $this->cachedPermissionSlugs(), true);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->is_super) {
            return true;
        }

        $cached = $this->cachedPermissionSlugs();

        foreach ($slugs as $slug) {
            if (in_array($slug, $cached, true)) {
                return true;
            }
        }

        return false;
    }

    public function clearPermissionCache(): void
    {
        if (!$this->exists) {
            return;
        }

        Cache::forget($this->permissionCacheKey());
    }

    protected function cachedPermissionSlugs(): array
    {
        if (!$this->exists) {
            return $this->permissions->pluck('slug')->all();
        }

        return Cache::remember($this->permissionCacheKey(), now()->addMinutes(10), function () {
            return $this->permissions()->pluck('slug')->all();
        });
    }

    protected function permissionCacheKey(): string
    {
        return 'admin_permissions_' . $this->getKey();
    }

    /**
     * Get the channels this admin is a member of
     */
    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'channel_user', 'admin_id');
    }

    public function canAccessAny(array $slugs): bool
    {
        // Agar Super Admin hai, toh hamesha access de do
        if ($this->is_super) {
            return true;
        }

        // Agar Super Admin nahi hai, toh check karo ki in permissions mein se koi bhi hai ya nahi
        return $this->hasAnyPermission($slugs);
    }
}
