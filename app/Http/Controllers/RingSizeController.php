<?php

namespace App\Http\Controllers;

use App\Models\RingSize;

/**
 * Ring Size Resource Controller
 * Manages ring sizes (5, 6, 7, etc.)
 */
class RingSizeController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return RingSize::class;
    }
    
    protected function getViewPath(): string
    {
        return 'ring_sizes';
    }
    
    protected function getRouteName(): string
    {
        return 'ring_sizes';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'ring_sizes';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:ring_sizes,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:ring_sizes,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}
