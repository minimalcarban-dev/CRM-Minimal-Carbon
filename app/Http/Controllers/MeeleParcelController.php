<?php

namespace App\Http\Controllers;

use App\Models\MeeleParcel;
use App\Services\MeeleStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeeleParcelController extends Controller
{
    protected $stockService;

    public function __construct(MeeleStockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        $parcels = MeeleParcel::orderBy('created_at', 'desc')->paginate(20);

        // Calculate totals for stats cards
        $totalWeight = MeeleParcel::sum('current_weight');
        $totalPieces = MeeleParcel::sum('current_pieces');

        return view('meele.index', compact('parcels', 'totalWeight', 'totalPieces'));
    }

    public function create()
    {
        return view('meele.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parcel_code' => 'required|unique:meele_parcels,parcel_code|max:32',
            'sieve_size' => 'required|string',
            'category' => 'required|in:Stars,Meele,Coarse',
            'initial_pieces' => 'required|integer|min:0',
            'initial_weight' => 'required|numeric|min:0',
            'avg_cost_per_carat' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $parcel = MeeleParcel::create([
                'parcel_code' => $validated['parcel_code'],
                'sieve_size' => $validated['sieve_size'],
                'category' => $validated['category'],
                'avg_cost_per_carat' => $validated['avg_cost_per_carat'] ?? 0,
                // Initial stock is 0, we add it via transaction below
                'current_pieces' => 0,
                'current_weight' => 0,
            ]);

            // Add Initial Stock
            if ($validated['initial_pieces'] > 0 || $validated['initial_weight'] > 0) {
                $this->stockService->addStock(
                    $parcel,
                    $validated['initial_pieces'],
                    $validated['initial_weight'],
                    'initial',
                    auth()->id(),
                    'Initial Stock Creation'
                );
            }
        });

        return redirect()->route('meele-parcels.index')->with('success', 'Parcel created successfully.');
    }

    public function show($id)
    {
        $parcel = MeeleParcel::with(['transactions.user', 'transactions.reference'])->findOrFail($id);

        // Calculate average price or other specific metrics if needed
        return view('meele.show', compact('parcel'));
    }

    // Direct update only allowed for non-stock fields
    public function update(Request $request, $id)
    {
        $parcel = MeeleParcel::findOrFail($id);

        $validated = $request->validate([
            'sieve_size' => 'required|string',
            'avg_cost_per_carat' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,archived,out_of_stock',
        ]);

        $parcel->update($validated);

        return redirect()->back()->with('success', 'Parcel details updated.');
    }

    // Specialized Method for Stock Movements (Ajax or Form)
    public function adjustment(Request $request, $id)
    {
        $parcel = MeeleParcel::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:add,subtract', // UI helper logic
            'pieces' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        try {
            if ($validated['type'] === 'add') {
                $this->stockService->addStock(
                    $parcel,
                    $validated['pieces'],
                    $validated['weight'],
                    'adjustment_add',
                    auth()->id(),
                    $validated['reason']
                );
            } else {
                $this->stockService->deductStock(
                    $parcel,
                    $validated['pieces'],
                    $validated['weight'],
                    'adjustment_sub',
                    auth()->id(),
                    $validated['reason']
                );
            }

            return redirect()->back()->with('success', 'Stock adjustment recorded successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
