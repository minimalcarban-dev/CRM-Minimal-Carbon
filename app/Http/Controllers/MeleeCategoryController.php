<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MeleeCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\MeleeTransaction;
use App\Services\MeleeStockService;

class MeleeCategoryController extends Controller
{
    private MeleeStockService $meleeStockService;

    public function __construct(MeleeStockService $meleeStockService)
    {
        $this->meleeStockService = $meleeStockService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:lab_grown,natural',
        ]);

        try {
            DB::beginTransaction();

            $category = $this->meleeStockService->createCategory([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'type' => $request->type,
                'allowed_shapes' => [],
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $category = MeleeCategory::with('diamonds.transactions')->findOrFail($id);

            // Delete category (and its diamonds/transactions)
            $this->meleeStockService->deleteCategory($category);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], 500);
        }
    }
}
