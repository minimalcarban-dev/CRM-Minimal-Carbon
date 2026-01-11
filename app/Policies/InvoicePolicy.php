<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Invoice;

class InvoicePolicy
{
    public function view(Admin $admin, Invoice $invoice)
    {
        if ($admin->is_super) return true;
        return $invoice->created_by === $admin->id;
    }

    public function create(Admin $admin)
    {
        return $admin->is_super || $admin->hasPermission('invoices.create');
    }

    public function update(Admin $admin, Invoice $invoice)
    {
        if ($admin->is_super) return true;
        return $invoice->created_by === $admin->id && $admin->hasPermission('invoices.edit');
    }

    public function delete(Admin $admin, Invoice $invoice)
    {
        if ($admin->is_super) return true;
        return $invoice->created_by === $admin->id && $admin->hasPermission('invoices.delete');
    }
}
