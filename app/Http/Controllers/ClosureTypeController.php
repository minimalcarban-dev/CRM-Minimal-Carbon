<?php

namespace App\Http\Controllers;

use App\Models\ClosureType;

/**
 * Closure Type Resource Controller
 * Manages closure types (Lobster, Spring Ring, Toggle, etc.)
 */
class ClosureTypeController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return ClosureType::class;
    }
    
    protected function getViewPath(): string
    {
        return 'closure_types';
    }
    
    protected function getRouteName(): string
    {
        return 'closure_types';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'closure_types';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:closure_types,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:closure_types,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}

