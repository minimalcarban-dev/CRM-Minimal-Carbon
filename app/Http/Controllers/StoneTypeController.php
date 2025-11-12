<?php

namespace App\Http\Controllers;

use App\Models\StoneType;
use Illuminate\Http\Request;

class StoneTypeController extends Controller
{
    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_types.view')) abort(403);

        $query = StoneType::query();
        if ($q = $request->query('search')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $items = $query->orderByDesc('id')->paginate(15)->withQueryString();
        return view('stone_types.index', compact('items'));
    }

    public function create()
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_types.create')) abort(403);
        return view('stone_types.create');
    }

    public function store(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_types.create')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:stone_types,name',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        StoneType::create($data);

        return redirect()->route('stone_types.index')->with('success', 'Stone type created');
    }

    public function show(StoneType $stone_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_types.view')) abort(403);
        return view('stone_types.show', ['item' => $stone_type]);
    }

    public function edit(StoneType $stone_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_types.edit')) abort(403);
        return view('stone_types.edit', ['item' => $stone_type]);
    }

    public function update(Request $request, StoneType $stone_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_types.edit')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:stone_types,name,' . $stone_type->id,
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $stone_type->update($data);

        return redirect()->route('stone_types.index')->with('success', 'Stone type updated');
    }

    public function destroy(StoneType $stone_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_types.delete')) abort(403);

        $stone_type->delete();

        return redirect()->route('stone_types.index')->with('success', 'Stone type deleted');
    }
}
