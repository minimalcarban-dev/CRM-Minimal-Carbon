<?php

namespace App\Http\Controllers;

use App\Models\Party;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function index(Request $request)
    {
        $query = Party::query();
        
        // Search filter
        if ($s = $request->get('search')) {
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('gst_no', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }
        
        // Category filter
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }
        
        $parties = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Party::CATEGORIES;
        
        return view('parties.index', compact('parties', 'categories'));
    }

    public function create()
    {
        $party = new Party();
        $categories = Party::CATEGORIES;
        return view('parties.create', compact('party', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(Party::CATEGORIES)),
            'address' => 'nullable|string',
            'gst_no' => 'nullable|string|max:64',
            'tax_id' => 'nullable|string|max:128',
            'pan_no' => 'nullable|string|max:64',
            'state' => 'nullable|string|max:255',
            'state_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
            'is_foreign' => 'nullable|boolean',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:64',
        ]);

        $data['is_foreign'] = (bool) ($data['is_foreign'] ?? false);
        $party = Party::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($party, 201);
        }

        return redirect()->route('parties.index')->with('success', 'Party created');
    }

    public function show(Party $party)
    {
        $invoicesCount = Invoice::where('billed_to_id', $party->id)
            ->orWhere('shipped_to_id', $party->id)
            ->count();

        $recentInvoices = Invoice::with('company')
            ->where('billed_to_id', $party->id)
            ->orWhere('shipped_to_id', $party->id)
            ->orderBy('invoice_date', 'desc')
            ->limit(10)
            ->get();

        if (request()->wantsJson()) {
            return response()->json($party);
        }

        return view('parties.show', compact('party', 'invoicesCount', 'recentInvoices'));
    }

    public function edit(Party $party)
    {
        $categories = Party::CATEGORIES;
        return view('parties.edit', compact('party', 'categories'));
    }

    public function update(Request $request, Party $party)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(Party::CATEGORIES)),
            'address' => 'nullable|string',
            'gst_no' => 'nullable|string|max:64',
            'tax_id' => 'nullable|string|max:128',
            'pan_no' => 'nullable|string|max:64',
            'state' => 'nullable|string|max:255',
            'state_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:255',
            'is_foreign' => 'nullable|boolean',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:64',
        ]);

        $data['is_foreign'] = (bool) ($data['is_foreign'] ?? false);
        $party->update($data);
        return redirect()->route('parties.index')->with('success', 'Party updated');
    }

    public function destroy(Party $party)
    {
        $party->delete();
        return redirect()->route('parties.index')->with('success', 'Party deleted');
    }
    
    /**
     * API endpoint to get parties filtered by category
     * Used by AJAX dropdowns in Gold Tracking, Purchase Tracker, Office Expense
     */
    public function apiByCategory(Request $request)
    {
        $query = Party::query();
        
        if ($category = $request->get('category')) {
            $query->byCategory($category);
        }
        
        if ($categories = $request->get('categories')) {
            $categoryArray = is_array($categories) ? $categories : explode(',', $categories);
            $query->byCategories($categoryArray);
        }
        
        $parties = $query->orderBy('name')->get(['id', 'name', 'phone', 'email', 'gst_no', 'address', 'category']);
        
        return response()->json($parties);
    }
}
