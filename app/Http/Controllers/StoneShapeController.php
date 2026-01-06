<?php

namespace App\Http\Controllers;

use App\Models\StoneShape;

/**
 * Stone Shape Resource Controller
 * Manages stone shapes (Round, Oval, Cushion, etc.)
 */
class StoneShapeController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return StoneShape::class;
    }
    
    protected function getViewPath(): string
    {
        return 'stone_shapes';
    }
    
    protected function getRouteName(): string
    {
        return 'stone_shapes';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'stone_shapes';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:stone_shapes,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:stone_shapes,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}
