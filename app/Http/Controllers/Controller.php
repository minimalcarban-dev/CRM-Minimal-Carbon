<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

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
