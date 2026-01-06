<?php

namespace App\Http\Controllers;

use App\Models\StoneType;

/**
 * Stone Type Resource Controller
 * Manages stone types (Ruby, Sapphire, Emerald, etc.)
 */
class StoneTypeController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return StoneType::class;
    }
    
    protected function getViewPath(): string
    {
        return 'stone_types';
    }
    
    protected function getRouteName(): string
    {
        return 'stone_types';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'stone_types';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:stone_types,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:stone_types,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}
