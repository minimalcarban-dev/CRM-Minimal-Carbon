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
        $labGrownCategories = MeleeCategory::labGrown()
            ->with([
                'diamonds' => function ($q) {
                    $q->orderBy('shape')->orderBy('size_label');
                }
            ])
            ->orderBy('sort_order')
            ->get();

        $naturalCategories = MeleeCategory::natural()
            ->with([
                'diamonds' => function ($q) {
                    $q->orderBy('shape')->orderBy('size_label');
                }
            ])
            ->orderBy('sort_order')
            ->get();

        // Calculate Totals for Stats Cards
        $totalParcels = MeleeDiamond::count();
        $totalCarats = MeleeDiamond::sum('total_carat_weight');
        $lowStockCount = MeleeDiamond::where('status', 'low_stock')->count();

        return view('melee.index', compact('labGrownCategories', 'naturalCategories', 'totalParcels', 'totalCarats', 'lowStockCount'));
    }

    /**
     * Search for diamonds (for Autocomplete/Dropdowns).
     */
    public function search(Request $request)
    {
        $term = $request->term ?? '';
        $categoryId = $request->category_id;

        $query = MeleeDiamond::with('category');

        if ($categoryId) {
            $query->where('melee_category_id', $categoryId);
        }

        if ($term !== '') {
            $keywords = explode(' ', $term);

            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    if (trim($keyword) === '')
                        continue;

                    $q->where(function ($subQ) use ($keyword) {
                        $subQ->where('shape', 'LIKE', "%{$keyword}%")
                            ->orWhere('size_label', 'LIKE', "%{$keyword}%")
                            ->orWhereHas('category', function ($catQ) use ($keyword) {
                                $catQ->where('name', 'LIKE', "%{$keyword}%")
                                    ->orWhere('type', 'LIKE', "%{$keyword}%");
                            });
                    });
                }
            });
        }

        $diamonds = $query->limit(50)
            ->get()
            ->map(function ($d) {
                $typeLabel = $d->category->type === 'lab_grown' ? 'Lab Grown' : 'Natural';
                return [
                    'id' => $d->id,
                    'text' => "[$typeLabel] {$d->category->name} - {$d->shape} - {$d->size_label} (Stock: {$d->available_pieces})",
                    'available_pieces' => $d->available_pieces,
                    'category_name' => $d->category->name,
                    'price' => $d->purchase_price_per_ct
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

            // Weighted Average Cost Logic (for Stock IN)
            if ($request->transaction_type === 'in' && $request->filled('price_per_ct') && $request->price_per_ct > 0) {
                $currentCarats = $diamond->available_carat_weight;
                $currentPrice = $diamond->purchase_price_per_ct;

                $newCarats = $request->carat_weight;
                $newPrice = $request->price_per_ct;

                $totalCarats = $currentCarats + $newCarats;

                if ($totalCarats > 0) {
                    $currentValue = $currentCarats * $currentPrice;
                    $newValue = $newCarats * $newPrice;

                    $diamond->purchase_price_per_ct = ($currentValue + $newValue) / $totalCarats;
                    $diamond->save();
                }
            }

            $transaction = MeleeTransaction::create([
                'melee_diamond_id' => $diamond->id,
                'transaction_type' => $request->transaction_type,
                'pieces' => $request->pieces,
                'carat_weight' => $request->carat_weight,
                'created_by' => Auth::id() ?? 1,
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

    /**
     * AJAX: Get transaction history for a specific diamond.
     * Shows who added/removed stock, how much, and when.
     */
    public function getHistory($id)
    {
        $diamond = MeleeDiamond::with('category')->findOrFail($id);

        $transactions = MeleeTransaction::where('melee_diamond_id', $id)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'type' => $t->transaction_type,
                    'pieces' => $t->pieces,
                    'carat_weight' => $t->carat_weight,
                    'reference_type' => $t->reference_type,
                    'reference_id' => $t->reference_id,
                    'notes' => $t->notes,
                    'user_name' => $t->createdBy->name ?? 'System',
                    'created_at' => $t->created_at->format('d M Y, h:i A'),
                    'time_ago' => $t->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'diamond' => [
                'id' => $diamond->id,
                'shape' => $diamond->shape,
                'size_label' => $diamond->size_label,
                'category_name' => $diamond->category->name ?? '-',
                'available_pieces' => $diamond->available_pieces,
                'total_pieces' => $diamond->total_pieces,
            ],
            'transactions' => $transactions,
        ]);
    }

    /**
     * AJAX: Add a new shape+size to a category.
     * size_label is stored as "shape-size" in lowercase (e.g., "round-1.5").
     */
    public function addShape(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:melee_categories,id',
            'shape' => 'required|string|max:100',
            'size' => ['required', 'string', 'max:20', 'regex:/^[0-9.*x\s]+$/i'],
        ]);

        $category = MeleeCategory::findOrFail($request->category_id);

        // Build size_label as "shape-size" lowercase
        $shape = trim($request->shape);
        $size = strtolower(trim($request->size));
        $sizeLabel = strtolower($shape) . '-' . $size;

        // Check if this shape+size already exists
        $exists = MeleeDiamond::where('melee_category_id', $category->id)
            ->where('size_label', $sizeLabel)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Shape \"{$shape}\" with size \"{$size}\" already exists in this category."
            ], 422);
        }

        // Also add the shape to allowed_shapes if it's not there yet
        $allowedShapes = $category->allowed_shapes ?? [];
        $ucShape = ucfirst(strtolower($shape));
        if (!in_array($ucShape, $allowedShapes)) {
            $allowedShapes[] = $ucShape;
            $category->allowed_shapes = $allowedShapes;
            $category->save();
        }

        $diamond = MeleeDiamond::create([
            'melee_category_id' => $category->id,
            'shape' => $ucShape,
            'size_label' => $sizeLabel,
            'color' => null,
            'sieve_size' => null,
            'total_pieces' => 0,
            'available_pieces' => 0,
            'sold_pieces' => 0,
            'total_carat_weight' => 0,
            'available_carat_weight' => 0,
            'purchase_price_per_ct' => 0,
            'listing_price_per_ct' => 0,
            'status' => 'out_of_stock',
            'low_stock_threshold' => 10,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Added {$ucShape} - {$size} to {$category->name}.",
            'diamond' => $diamond,
        ]);
    }
}
