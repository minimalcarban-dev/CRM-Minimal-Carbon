<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\MeleeCategory;

/**
 * Melee Category Policy — Sprint 3
 *
 * Permission slugs (from policy-audit.md Sprint 1):
 *   melee.view   → view any / view a category
 *   melee.create → MeleeCategoryController@store
 *   melee.edit   → (future: edit category name/type)
 *   melee.delete → MeleeCategoryController@destroy
 *
 * Note: Super-admins bypass all non-strict slugs via Admin::hasPermission().
 * God admin bypasses everything. See Admin::hasPermission() for the full chain.
 */
class MeleeCategoryPolicy
{
    /**
     * View the list of all melee categories.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('melee.view');
    }

    /**
     * View a specific melee category record.
     */
    public function view(Admin $admin, MeleeCategory $meleeCategory): bool
    {
        return $admin->hasPermission('melee.view');
    }

    /**
     * Create a new melee category (MeleeCategoryController@store).
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('melee.create');
    }

    /**
     * Update an existing melee category.
     */
    public function update(Admin $admin, MeleeCategory $meleeCategory): bool
    {
        return $admin->hasPermission('melee.edit');
    }

    /**
     * Delete a melee category (MeleeCategoryController@destroy).
     */
    public function delete(Admin $admin, MeleeCategory $meleeCategory): bool
    {
        return $admin->hasPermission('melee.delete');
    }
}
