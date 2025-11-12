<?php

namespace App\Http\Controllers;

use App\Models\StoneShape;
use Illuminate\Http\Request;

class StoneShapeController extends Controller
{
    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_shapes.view')) abort(403);

        $query = StoneShape::query();
        if ($q = $request->query('search')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $items = $query->orderByDesc('id')->paginate(15)->withQueryString();
        return view('stone_shapes.index', compact('items'));
    }

    public function create()
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_shapes.create')) abort(403);
        return view('stone_shapes.create');
    }

    public function store(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_shapes.create')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:stone_shapes,name',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        StoneShape::create($data);

        return redirect()->route('stone_shapes.index')->with('success', 'Stone shape created');
    }

    public function show(StoneShape $stone_shape)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_shapes.view')) abort(403);
        return view('stone_shapes.show', ['item' => $stone_shape]);
    }

    public function edit(StoneShape $stone_shape)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_shapes.edit')) abort(403);
        return view('stone_shapes.edit', ['item' => $stone_shape]);
    }

    public function update(Request $request, StoneShape $stone_shape)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_shapes.edit')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:stone_shapes,name,' . $stone_shape->id,
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $stone_shape->update($data);

        return redirect()->route('stone_shapes.index')->with('success', 'Stone shape updated');
    }

    public function destroy(StoneShape $stone_shape)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_shapes.delete')) abort(403);

        $stone_shape->delete();

        return redirect()->route('stone_shapes.index')->with('success', 'Stone shape deleted');
    }
}
