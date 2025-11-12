<?php

namespace App\Http\Controllers;

use App\Models\StoneColor;
use Illuminate\Http\Request;

class StoneColorController extends Controller
{
    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_colors.view')) abort(403);

        $query = StoneColor::query();
        if ($q = $request->query('search')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $items = $query->orderByDesc('id')->paginate(15)->withQueryString();
        return view('stone_colors.index', compact('items'));
    }

    public function create()
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_colors.create')) abort(403);
        return view('stone_colors.create');
    }

    public function store(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_colors.create')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:stone_colors,name',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        StoneColor::create($data);

        return redirect()->route('stone_colors.index')->with('success', 'Stone color created');
    }

    public function show(StoneColor $stone_color)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_colors.view')) abort(403);
        return view('stone_colors.show', ['item' => $stone_color]);
    }

    public function edit(StoneColor $stone_color)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_colors.edit')) abort(403);
        return view('stone_colors.edit', ['item' => $stone_color]);
    }

    public function update(Request $request, StoneColor $stone_color)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_colors.edit')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:stone_colors,name,' . $stone_color->id,
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $stone_color->update($data);

        return redirect()->route('stone_colors.index')->with('success', 'Stone color updated');
    }

    public function destroy(StoneColor $stone_color)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('stone_colors.delete')) abort(403);

        $stone_color->delete();

        return redirect()->route('stone_colors.index')->with('success', 'Stone color deleted');
    }
}
