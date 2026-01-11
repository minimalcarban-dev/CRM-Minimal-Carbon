<?php

namespace App\Http\Controllers;

use App\Models\MetalType;

/**
 * Metal Type Resource Controller
 * Manages metal types (Gold, Silver, Platinum, etc.)
 */
class MetalTypeController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return MetalType::class;
    }
    
    protected function getViewPath(): string
    {
        return 'metal_types';
    }
    
    protected function getRouteName(): string
    {
        return 'metal_types';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'metal_types';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:metal_types,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:metal_types,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}

