<?php

namespace App\Http\Controllers;

use App\Models\MeleeCategory;
use App\Models\MeleeDiamond;
use App\Models\MeleeTransaction;
use App\Services\MeleeStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MeleeDiamondController extends Controller
{
    private MeleeStockService $meleeStockService;

    public function __construct(MeleeStockService $meleeStockService)
    {
        $this->meleeStockService = $meleeStockService;
    }

    /**
     * Display the main inventory dashboard.
     */
    public function index()
    {
        $labGrownCategories = MeleeCategory::labGrown()
            ->with([
                'diamonds' => function ($q) {
                    $q->with('latestInTransaction')
                        ->orderBy('shape')
                        ->orderBy('size_label');
                }
            ])
            ->orderBy('sort_order')
            ->get();

        $naturalCategories = MeleeCategory::natural()
            ->with([
                'diamonds' => function ($q) {
                    $q->with('latestInTransaction')
                        ->orderBy('shape')
                        ->orderBy('size_label');
                }
            ])
            ->orderBy('sort_order')
            ->get();

        // Calculate Totals for Stats Cards
        $allMeleeCount = MeleeDiamond::count();
        $inStockCount = MeleeDiamond::where('available_pieces', '>', 0)->count();
        $outOfStockCount = MeleeDiamond::where('available_pieces', '=', 0)->count();
        $negativeStockCount = MeleeDiamond::where('available_pieces', '<', 0)->count();
        $totalValue = MeleeDiamond::sum('total_price');
        $avgValue = $allMeleeCount > 0 ? $totalValue / $allMeleeCount : 0;
        $canEditMeleeCost = $this->canEditMeleeCost();

        return view('melee.index', compact(
            'labGrownCategories',
            'naturalCategories',
            'allMeleeCount',
            'inStockCount',
            'outOfStockCount',
            'negativeStockCount',
            'totalValue',
            'avgValue',
            'canEditMeleeCost'
        ));
    }

    /**
     * Search for diamonds (for Autocomplete/Dropdowns).
     */
    public function search(Request $request)
    {
        $term = $request->term ?? '';
        $categoryId = $request->category_id;
        $limit = (int) $request->input('limit', 50);
        $limit = max(1, min($limit, 100));

        $query = MeleeDiamond::with('category');

        if ($categoryId) {
            $query->where('melee_category_id', $categoryId);
        }

        if ($term !== '') {
            $keywords = explode(' ', $term);

            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    if (trim($keyword) === ' ')
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

        $diamonds = $query->limit($limit)
            ->get()
            ->map(function ($d) {
                $typeLabel = $d->category->type === 'lab_grown' ? 'Lab Grown' : 'Natural';
                $avg_carat_per_piece = $d->total_pieces > 0 ? $d->total_carat_weight / $d->total_pieces : 0;
                $avg_price_per_piece = $avg_carat_per_piece * ($d->purchase_price_per_ct ?? 0);

                return [
                    'id' => $d->id,
                    'text' => "[$typeLabel] {$d->category->name} - {$d->shape} - {$d->size_label} (Stock: {$d->available_pieces})",
                    'available_pieces' => $d->available_pieces,
                    'category_name' => $d->category->name,
                    'price' => $d->purchase_price_per_ct,
                    'avg_carat_per_piece' => $avg_carat_per_piece,
                    'avg_price_per_piece' => $avg_price_per_piece,
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
            'carat_weight' => 'nullable|numeric|min:0',
            'price_per_ct' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $result = $this->meleeStockService->recordManualTransaction([
            'melee_diamond_id' => $request->melee_diamond_id,
            'transaction_type' => $request->transaction_type,
            'pieces' => $request->pieces,
            'carat_weight' => $request->carat_weight,
            'price_per_ct' => $request->price_per_ct,
            'notes' => $request->notes,
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['status'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'new_balance' => $result['diamond']['available_pieces'] ?? null,
            'diamond' => $result['diamond'] ?? null,
        ]);
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
            ->map(function ($t) use ($diamond) {
                $costPerCt = (float) ($t->price_per_ct ?? $diamond->purchase_price_per_ct ?? 0);

                return [
                    'id' => $t->id,
                    'type' => $t->transaction_type,
                    'pieces' => $t->pieces,
                    'carat_weight' => $t->carat_weight,
                    'cost_per_ct' => $costPerCt,
                    'total_price' => ((float) ($t->carat_weight ?? 0)) * $costPerCt,
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
                'avg_price_per_ct' => $diamond->purchase_price_per_ct ?? 0,
                'total_price' => $diamond->total_price ?? 0,
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

    /**
     * AJAX: Update an existing shape/size, and optionally adjust the very last IN transaction.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shape' => 'required|string|max:100',
            'size' => ['required', 'string', 'max:20', 'regex:/^[0-9.*x\s]+$/i'],
            'last_pieces' => 'nullable|integer|min:1',
            'last_carats' => 'nullable|numeric|min:0',
            'last_price_per_ct' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $diamond = MeleeDiamond::lockForUpdate()->findOrFail($id);
            $category = MeleeCategory::findOrFail($diamond->melee_category_id);

            $shape = trim($request->shape);
            $size = strtolower(trim($request->size));
            $sizeLabel = strtolower($shape) . '-' . $size;

            // Check if this new shape+size already exists in the same category (but not this diamond)
            $exists = MeleeDiamond::where('melee_category_id', $category->id)
                ->where('size_label', $sizeLabel)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => "Shape \"{$shape}\" with size \"{$size}\" already exists in this category."
                ], 422);
            }

            $ucShape = ucfirst(strtolower($shape));

            // Also add the shape to allowed_shapes if it's not there yet
            $allowedShapes = $category->allowed_shapes ?? [];
            if (!in_array($ucShape, $allowedShapes)) {
                $allowedShapes[] = $ucShape;
                $category->allowed_shapes = $allowedShapes;
                $category->save();
            }

            // --- Handle Last Transaction Update ---
            $recalculateSnapshot = false;
            $wantsPiecesCaratsUpdate = $request->filled('last_pieces') && $request->filled('last_carats');
            $wantsPriceUpdate = $request->has('last_price_per_ct');

            if ($wantsPiecesCaratsUpdate || $wantsPriceUpdate) {
                $lastInTx = MeleeTransaction::where('melee_diamond_id', $diamond->id)
                    ->where('transaction_type', 'in')
                    ->latest('id')
                    ->first();

                if ($lastInTx) {
                    $transactionChanged = false;

                    if ($wantsPiecesCaratsUpdate) {
                        $newPieces = (int) $request->last_pieces;
                        $newCarats = (float) $request->last_carats;

                        $piecesDiff = $newPieces - $lastInTx->pieces;
                        $caratsDiff = $newCarats - $lastInTx->carat_weight;

                        if ($piecesDiff != 0 || floatval($caratsDiff) != 0.0) {
                            $lastInTx->pieces = $newPieces;
                            $lastInTx->carat_weight = $newCarats;
                            $transactionChanged = true;
                        }
                    }

                    if ($wantsPriceUpdate) {
                        $newPricePerCt = (float) $request->last_price_per_ct;
                        $currentPricePerCt = (float) ($lastInTx->price_per_ct ?? 0);
                        $priceChanged = abs($newPricePerCt - $currentPricePerCt) > 0.0001;

                        if ($priceChanged) {
                            if (!$this->canEditMeleeCost()) {
                                DB::rollBack();
                                return response()->json([
                                    'success' => false,
                                    'message' => 'You do not have permission to edit melee cost.',
                                ], 403);
                            }

                            if (!$this->isCostEditableTransaction($lastInTx)) {
                                DB::rollBack();
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Cost can only be edited for manual stock IN transactions.',
                                ], 422);
                            }

                            $lastInTx->price_per_ct = $newPricePerCt;
                            $transactionChanged = true;
                        }
                    }

                    if ($transactionChanged) {
                        $lastInTx->save();
                        $recalculateSnapshot = true;
                    }
                }
            }

            if ($recalculateSnapshot) {
                $this->meleeStockService->recalculateDiamondFromTransactions($diamond);
                $diamond->refresh();
            }

            // --- Update Core Diamond Properties ---
            $diamond->shape = $ucShape;
            $diamond->size_label = $sizeLabel;

            // Recalculate status just in case pieces changed
            if ($diamond->available_pieces <= 0) {
                $diamond->status = 'out_of_stock';
            } elseif ($diamond->available_pieces <= $diamond->low_stock_threshold) {
                $diamond->status = 'low_stock';
            } else {
                $diamond->status = 'in_stock';
            }

            $diamond->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Diamond updated successfully.',
                'diamond' => $diamond,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating diamond: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: Delete a melee diamond entry.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $diamond = MeleeDiamond::findOrFail($id);

            // Delete associated transactions first
            MeleeTransaction::where('melee_diamond_id', $id)->delete();

            // Delete the diamond entry
            $diamond->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Diamond deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting diamond: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * AJAX: Edit a specific stock history entry.
     */
    public function updateTransaction(Request $request, $id)
    {
        $request->validate([
            'pieces' => 'required|integer|min:1',
            'carat_weight' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $transaction = MeleeTransaction::findOrFail($id);
            $diamond = MeleeDiamond::lockForUpdate()->findOrFail($transaction->melee_diamond_id);

            $newPieces = (int) $request->pieces;
            $newCarats = (float) $request->carat_weight;

            $piecesDiff = $newPieces - $transaction->pieces;
            $caratsDiff = $newCarats - $transaction->carat_weight;

            if ($transaction->transaction_type === 'out' && $piecesDiff > 0 && $diamond->available_pieces < $piecesDiff) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Stock low, only {$diamond->available_pieces} pieces left for this update."
                ], 422);
            }

            $transaction->pieces = $newPieces;
            $transaction->carat_weight = $newCarats;
            $transaction->save();

            $this->meleeStockService->recalculateDiamondFromTransactions($diamond);
            $diamond->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully.',
                'diamond' => [
                    'id' => $diamond->id,
                    'available_pieces' => $diamond->available_pieces,
                    'available_carat_weight' => $diamond->available_carat_weight,
                    'purchase_price_per_ct' => $diamond->purchase_price_per_ct,
                    'total_price' => $diamond->total_price,
                    'status' => $diamond->status,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: Delete a specific stock history entry.
     */
    public function destroyTransaction($id)
    {
        try {
            DB::beginTransaction();

            $transaction = MeleeTransaction::findOrFail($id);
            $diamond = MeleeDiamond::lockForUpdate()->findOrFail($transaction->melee_diamond_id);

            $transaction->delete();
            $this->meleeStockService->recalculateDiamondFromTransactions($diamond);
            $diamond->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully.',
                'diamond' => [
                    'id' => $diamond->id,
                    'available_pieces' => $diamond->available_pieces,
                    'available_carat_weight' => $diamond->available_carat_weight,
                    'purchase_price_per_ct' => $diamond->purchase_price_per_ct,
                    'total_price' => $diamond->total_price,
                    'status' => $diamond->status,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock data for a specific melee diamond (for real-time updates).
     */
    public function getStock($id)
    {
        try {
            $diamond = MeleeDiamond::findOrFail($id);

            return response()->json([
                'success' => true,
                'diamond' => [
                    'id' => $diamond->id,
                    'available_pieces' => $diamond->available_pieces,
                    'available_carat_weight' => $diamond->available_carat_weight,
                    'purchase_price_per_ct' => $diamond->purchase_price_per_ct,
                    'total_price' => $diamond->total_price,
                    'status' => $diamond->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stock data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function canEditMeleeCost(): bool
    {
        $admin = Auth::guard('admin')->user();
        return $admin ? $admin->hasPermission('melee_diamonds.edit_cost') : false;
    }

    private function isCostEditableTransaction(MeleeTransaction $transaction): bool
    {
        return $transaction->transaction_type === 'in' && $transaction->reference_type !== 'order';
    }
}
