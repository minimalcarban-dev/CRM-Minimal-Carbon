<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\MeleeDiamond;

/**
 * Melee Diamond Policy — Sprint 3
 *
 * Permission slugs (from policy-audit.md Sprint 1):
 *   melee.view   → index, search, getStock, getHistory
 *   melee.create → addShape, MeleeCategoryController@store
 *   melee.edit   → update, transaction, updateTransaction
 *   melee.delete → destroy, destroyTransaction, MeleeCategoryController@destroy
 *
 * Note: Super-admins bypass all non-strict slugs via Admin::hasPermission().
 * God admin bypasses everything. See Admin::hasPermission() for the full chain.
 */
class MeleeDiamondPolicy
{
    /**
     * View the inventory dashboard, search, stock, and history.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission('melee.view');
    }

    /**
     * View a specific melee diamond record.
     */
    public function view(Admin $admin, MeleeDiamond $meleeDiamond): bool
    {
        return $admin->hasPermission('melee.view');
    }

    /**
     * Add a new shape/size (addShape) or create a category.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermission('melee.create');
    }

    /**
     * Update an existing melee diamond or record a transaction.
     */
    public function update(Admin $admin, MeleeDiamond $meleeDiamond): bool
    {
        return $admin->hasPermission('melee.edit');
    }

    /**
     * Delete a melee diamond, a transaction, or a category.
     *
     * Note: "isLocked" guard is not applicable here — melee diamonds
     * have no lock state in this system (Sprint 3). Add in Sprint 6 if needed.
     */
    public function delete(Admin $admin, MeleeDiamond $meleeDiamond): bool
    {
        return $admin->hasPermission('melee.delete');
    }
}
