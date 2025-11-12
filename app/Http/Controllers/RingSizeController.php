<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RingSize;

class RingSizeController extends Controller
{
    public function index(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('ring_sizes.view')) abort(403);

        $query = RingSize::query();
        if ($q = $request->query('search')) $query->where('name', 'like', "%{$q}%");
        $items = $query->orderBy('id','desc')->paginate(15)->withQueryString();
        return view('ring_sizes.index', compact('items'));
    }

    public function create()
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('ring_sizes.create')) abort(403);
        return view('ring_sizes.create');
    }

    public function store(Request $request)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('ring_sizes.create')) abort(403);
        $data = $request->validate(['name'=>'required|string|max:255|unique:ring_sizes,name','is_active'=>'nullable|boolean']);
        $data['is_active'] = $request->has('is_active');
        RingSize::create($data);
        return redirect()->route('ring_sizes.index')->with('success','Created');
    }

    public function show(RingSize $ring_size)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('ring_sizes.view')) abort(403);
        return view('ring_sizes.show',['item'=>$ring_size]);
    }

    public function edit(RingSize $ring_size)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('ring_sizes.edit')) abort(403);
        return view('ring_sizes.edit',['item'=>$ring_size]);
    }

    public function update(Request $request, RingSize $ring_size)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('ring_sizes.edit')) abort(403);
        $data = $request->validate(['name'=>'required|string|max:255|unique:ring_sizes,name,' . $ring_size->id,'is_active'=>'nullable|boolean']);
        $data['is_active'] = $request->has('is_active');
        $ring_size->update($data);
        return redirect()->route('ring_sizes.index')->with('success','Updated');
    }

    public function destroy(RingSize $ring_size)
    {
        $current = $this->currentAdmin();
        if (!$current || !$current->hasPermission('ring_sizes.delete')) abort(403);
        $ring_size->delete();
        return redirect()->route('ring_sizes.index')->with('success','Deleted');
    }
}
