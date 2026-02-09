<?php

namespace App\Http\Controllers;

use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MeleeDiamondController extends Controller
{
    /**
     * Display the main inventory dashboard.
     */
    public function index()
    {
        // Load categories with hierarchy for the view
        // We separate them for the tabs: Lab Grown vs Natural

        $labGrownCategories = MeleeCategory::labGrown()
            ->with([
                'diamonds' => function ($q) {
                    $q->orderBy('sieve_size')->orderBy('size_label');
                }
            ])
            ->orderBy('sort_order')
            ->get();

        $naturalCategories = MeleeCategory::natural()
            ->with([
                'diamonds' => function ($q) {
                    // Determine grouping logic if needed, for now just load them
                    $q->orderBy('sieve_size');
                }
            ])
            ->orderBy('sort_order')
            ->get();

        // Calculate Totals for Stats Cards
        $totalParcels = MeleeDiamond::count();
        $totalCarats = MeleeDiamond::sum('total_carat_weight'); // Or available based on request
        $lowStockCount = MeleeDiamond::where('status', 'low_stock')->count();

        return view('melee.index', compact('labGrownCategories', 'naturalCategories', 'totalParcels', 'totalCarats', 'lowStockCount'));
    }

    /**
     * Search for diamonds (for Autocomplete/Dropdowns).
     */
    public function search(Request $request)
    {
        $term = $request->term;

        $diamonds = MeleeDiamond::where('shape', 'LIKE', "%{$term}%")
            ->orWhere('size_label', 'LIKE', "%{$term}%")
            ->with('category')
            ->limit(20)
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'text' => "{$d->category->name} - {$d->shape} - {$d->size_label} (Stock: {$d->available_pieces})",
                    'available_pieces' => $d->available_pieces,
                    'category_name' => $d->category->name,
                    'price' => $d->purchase_price_per_ct // Added for auto-fill
                ];
            });

        return response()->json($diamonds);
    }

    /**
     * Handle Stock Transactions (IN/OUT).
     */
    public function transaction(Request $request)
    {
        $request->validate([
            'melee_diamond_id' => 'required|exists:melee_diamonds,id',
            'transaction_type' => 'required|in:in,out,adjustment',
            'pieces' => 'required|integer|min:1',
            'carat_weight' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $diamond = MeleeDiamond::lockForUpdate()->find($request->melee_diamond_id);

            // Create Transaction
            // Note: Validation of OUT stock (blocking if negative) depends on business rule.
            // Current rule: "Display negative with Red highlighting", so we allow it but maybe warn.

            $transaction = MeleeTransaction::create([
                'melee_diamond_id' => $diamond->id,
                'transaction_type' => $request->transaction_type,
                'pieces' => $request->pieces, // Model boot logic handles the sign?
                // Wait, check model logic: 
                // IF IN: it adds ABS(pieces). 
                // IF OUT: it subtracts ABS(pieces).
                // So we can send positive numbers here.
                'carat_weight' => $request->carat_weight,
                'created_by' => Auth::id() ?? 1, // Fallback for dev if needed
                'notes' => $request->notes,
                'reference_type' => 'manual',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction recorded successfully.',
                'new_balance' => $diamond->fresh()->available_pieces,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Get specific stock status for a diamond ID.
     */
    public function getStock($id)
    {
        $diamond = MeleeDiamond::findOrFail($id);
        return response()->json($diamond);
    }
}
