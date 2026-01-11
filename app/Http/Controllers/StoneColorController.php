<?php

namespace App\Http\Controllers;

use App\Models\StoneColor;

/**
 * Stone Color Resource Controller
 * Manages stone colors (Red, Blue, Green, etc.)
 */
class StoneColorController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return StoneColor::class;
    }
    
    protected function getViewPath(): string
    {
        return 'stone_colors';
    }
    
    protected function getRouteName(): string
    {
        return 'stone_colors';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'stone_colors';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:stone_colors,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:stone_colors,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}
