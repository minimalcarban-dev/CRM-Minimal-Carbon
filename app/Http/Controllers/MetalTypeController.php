<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MetalType;

class MetalTypeController extends Controller
{
    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('metal_types.view')) {
            abort(403);
        }

        $query = MetalType::query();
        if ($q = $request->query('search')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        return view('metal_types.index', compact('items'));
    }

    public function create()
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('metal_types.create')) abort(403);
        return view('metal_types.create');
    }

    public function store(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('metal_types.create')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:metal_types,name',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        MetalType::create($data);

        return redirect()->route('metal_types.index')->with('success', 'Metal type created.');
    }

    public function show(MetalType $metal_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('metal_types.view')) abort(403);
        return view('metal_types.show', ['item' => $metal_type]);
    }

    public function edit(MetalType $metal_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('metal_types.edit')) abort(403);
        return view('metal_types.edit', ['item' => $metal_type]);
    }

    public function update(Request $request, MetalType $metal_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('metal_types.edit')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:metal_types,name,' . $metal_type->id,
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $metal_type->update($data);

        return redirect()->route('metal_types.index')->with('success', 'Metal type updated.');
    }

    public function destroy(MetalType $metal_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('metal_types.delete')) abort(403);
        $metal_type->delete();
        return redirect()->route('metal_types.index')->with('success', 'Deleted.');
    }
}

