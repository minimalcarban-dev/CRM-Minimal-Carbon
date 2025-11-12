<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClosureType;

class ClosureTypeController extends Controller
{
    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('closure_types.view')) abort(403);

        $query = ClosureType::query();
        if ($q = $request->query('search')) $query->where('name', 'like', "%{$q}%");
        $items = $query->orderBy('id','desc')->paginate(15)->withQueryString();
        return view('closure_types.index', compact('items'));
    }

    public function create()
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('closure_types.create')) abort(403);
        return view('closure_types.create');
    }

    public function store(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('closure_types.create')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:closure_types,name',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        ClosureType::create($data);
        return redirect()->route('closure_types.index')->with('success','Created');
    }

    public function show(ClosureType $closure_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('closure_types.view')) abort(403);
        return view('closure_types.show',['item'=>$closure_type]);
    }

    public function edit(ClosureType $closure_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('closure_types.edit')) abort(403);
        return view('closure_types.edit',['item'=>$closure_type]);
    }

    public function update(Request $request, ClosureType $closure_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('closure_types.edit')) abort(403);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:closure_types,name,' . $closure_type->id,
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        $closure_type->update($data);
        return redirect()->route('closure_types.index')->with('success','Updated');
    }

    public function destroy(ClosureType $closure_type)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('closure_types.delete')) abort(403);
        $closure_type->delete();
        return redirect()->route('closure_types.index')->with('success','Deleted');
    }
}

