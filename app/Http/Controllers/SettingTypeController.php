<?php

namespace App\Http\Controllers;

use App\Models\SettingType;

/**
 * Setting Type Resource Controller
 * Manages setting types (Prong, Bezel, Channel, etc.)
 */
class SettingTypeController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return SettingType::class;
    }
    
    protected function getViewPath(): string
    {
        return 'setting_types';
    }
    
    protected function getRouteName(): string
    {
        return 'setting_types';
    }
    
    protected function getPermissionPrefix(): ?string
    {
        return 'setting_types';
    }
    
    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:setting_types,name',
            'is_active' => 'nullable|boolean',
        ];
    }
    
    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:setting_types,name,' . $id,
            'is_active' => 'nullable|boolean',
        ];
    }
}

