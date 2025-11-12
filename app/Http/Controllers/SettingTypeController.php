<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SettingType;

class SettingTypeController extends Controller
{
    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('setting_types.view')) abort(403);

        $query = SettingType::query();
        if ($q = $request->query('search')) $query->where('name', 'like', "%{$q}%");

        $items = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        return view('setting_types.index', compact('items'));
    }

    public function create()
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('setting_types.create')) abort(403);
        return view('setting_types.create');
    }

    public function store(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('setting_types.create')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:setting_types,name',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        SettingType::create($data);
        return redirect()->route('setting_types.index')->with('success', 'Created.');
    }

    public function show(SettingType $setting_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('setting_types.view')) abort(403);
        return view('setting_types.show', ['item' => $setting_type]);
    }

    public function edit(SettingType $setting_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('setting_types.edit')) abort(403);
        return view('setting_types.edit', ['item' => $setting_type]);
    }

    public function update(Request $request, SettingType $setting_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('setting_types.edit')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:setting_types,name,' . $setting_type->id,
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        $setting_type->update($data);
        return redirect()->route('setting_types.index')->with('success', 'Updated.');
    }

    public function destroy(SettingType $setting_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('setting_types.delete')) abort(403);
        $setting_type->delete();
        return redirect()->route('setting_types.index')->with('success', 'Deleted.');
    }
}

