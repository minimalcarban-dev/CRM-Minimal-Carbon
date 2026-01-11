<?php

namespace App\Http\Controllers;

use App\Models\DiamondClarity;

/**
 * Diamond Clarity Resource Controller
 * Manages diamond clarity grades (FL, IF, VVS1, VVS2, VS1, VS2, etc.)
 */
class DiamondClarityController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return DiamondClarity::class;
    }
    
    protected function getViewPath(): string
    {
        return 'diamond_clarities';
    }
    
    protected function getRouteName(): string
    {
        return 'diamond_clarities';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'diamond_clarities';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:diamond_clarities,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:diamond_clarities,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}
