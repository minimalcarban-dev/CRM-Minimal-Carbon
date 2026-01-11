<?php

namespace App\Http\Controllers;

use App\Models\DiamondCut;

/**
 * Diamond Cut Resource Controller
 * Manages diamond cut grades (Excellent, Very Good, Good, Fair, Poor)
 */
class DiamondCutController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return DiamondCut::class;
    }
    
    protected function getViewPath(): string
    {
        return 'diamond_cuts';
    }
    
    protected function getRouteName(): string
    {
        return 'diamond_cuts';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'diamond_cuts';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:diamond_cuts,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:diamond_cuts,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}
