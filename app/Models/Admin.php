<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use App\Modules\Email\Traits\HasEmailAccounts;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Admin extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasEmailAccounts;


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
        'family_member_phone',
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

    public function isGodAdmin(): bool
    {
        $godEmail = config('auth.god_admin_email');
        return $this->email === $godEmail;
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isGodAdmin()) {
            return true;
        }

        if ($this->is_super) {
            // List of strict prefixes that super admin shouldn't bypass
            $strictPrefixes = ['purchases.', 'expenses.', 'gold_tracking.', 'factories.', 'sales.'];
            $isStrict = false;
            foreach ($strictPrefixes as $prefix) {
                if (str_starts_with($slug, $prefix)) {
                    $isStrict = true;
                    break;
                }
            }
            if (!$isStrict) {
                return true;
            }
        }

        return in_array($slug, $this->cachedPermissionSlugs(), true);
    }

    /**
     * Check if admin has permission explicitly assigned.
     * Use this for sensitive features like sales that require explicit assignment even for super admins.
     */
    public function hasExplicitPermission(string $slug): bool
    {
        if ($this->isGodAdmin()) {
            return true;
        }
        return in_array($slug, $this->cachedPermissionSlugs(), true);
    }

    public function hasAnyExplicitPermission(array $slugs): bool
    {
        if ($this->isGodAdmin()) {
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

    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->isGodAdmin()) {
            return true;
        }

        if ($this->is_super) {
            // Check if all requested slugs are strict. If even one is non-strict, super admin bypasses.
            // Or better: check each slug independently using hasPermission
            foreach ($slugs as $slug) {
                if ($this->hasPermission($slug)) {
                    return true;
                }
            }
            return false;
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
        // hasAnyPermission now correctly handles the bypass vs regular superadmin logic
        return $this->hasAnyPermission($slugs);
    }

    /**
     * Get the broadcast channel name that is used for this admin's notifications.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'admin.notifications.' . $this->id;
    }
}
