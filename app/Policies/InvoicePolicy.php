<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Invoice;

class InvoicePolicy
{
    public function view(Admin $admin, Invoice $invoice)
    {
        return $admin->hasPermission('invoices.view') || $invoice->created_by === $admin->id;
    }

    public function create(Admin $admin)
    {
        return $admin->hasPermission('invoices.create');
    }

    public function update(Admin $admin, Invoice $invoice)
    {
        return $admin->hasPermission('invoices.edit') || $invoice->created_by === $admin->id;
    }

    public function delete(Admin $admin, Invoice $invoice)
    {
        return $admin->hasPermission('invoices.delete') || $invoice->created_by === $admin->id;
    }
}
