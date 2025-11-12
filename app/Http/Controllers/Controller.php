<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class Controller extends BaseController
{
    /**
     * Return the currently authenticated admin from session or null.
     */
    protected function currentAdmin(): ?Admin
    {
        try {
            /** @var Admin|null $admin */
            $admin = Auth::guard('admin')->user();
            return $admin instanceof Admin ? $admin : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
