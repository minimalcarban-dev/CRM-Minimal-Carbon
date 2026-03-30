<?php

namespace App\Http\Controllers;

use App\Models\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FactoryController extends Controller
{
    /**
     * Display a listing of factories with their current gold stock.
     */
    public function index()
    {
        $factories = Factory::with('creator')
            ->withCount([
                'distributions as stock_out' => function ($q) {
                    $q->where('type', 'out')->select(\DB::raw('COALESCE(SUM(weight_grams), 0)'));
                }
            ])
            ->orderBy('code')
            ->paginate(15);

        // Calculate stats
        $totalFactories = Factory::count();
        $activeFactories = Factory::active()->count();

        // Total gold in all factories
        $totalInFactories = GoldDistribution::getTotalInFactories();

        return view('factories.index', compact(
            'factories',
            'totalFactories',
            'activeFactories',
            'totalInFactories'
        ));
    }

    /**
     * Show the form for creating a new factory.
     */
    public function create()
    {
        return view('factories.create');
    }

    /**
     * Store a newly created factory in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:factories,name',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = Auth::guard('admin')->id();

        Factory::create($validated);

        return redirect()->route('factories.index')
            ->with('success', 'Factory created successfully!');
    }

    /**
     * Display the specified factory.
     */
    public function show(Factory $factory)
    {
        $factory->load(['distributions.admin', 'creator']);

        // Recent distributions for this factory
        $recentDistributions = $factory->distributions()
            ->with('admin')
            ->latest('distribution_date')
            ->take(10)
            ->get();

        return view('factories.show', compact('factory', 'recentDistributions'));
    }

    /**
     * Show the form for editing the specified factory.
     */
    public function edit(Factory $factory)
    {
        return view('factories.edit', compact('factory'));
    }

    /**
     * Update the specified factory in storage.
     */
    public function update(Request $request, Factory $factory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('factories')->ignore($factory->id)],
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $factory->update($validated);

        return redirect()->route('factories.index')
            ->with('success', 'Factory updated successfully!');
    }

    /**
     * Remove the specified factory from storage.
     */
    public function destroy(Factory $factory)
    {
        // Prevent deletion if factory has gold allocated
        if ($factory->current_stock > 0) {
            return redirect()->route('factories.index')
                ->with('error', 'Cannot delete factory with gold allocated. Please return all gold first.');
        }

        $factory->delete();

        return redirect()->route('factories.index')
            ->with('success', 'Factory deleted successfully!');
    }
}
