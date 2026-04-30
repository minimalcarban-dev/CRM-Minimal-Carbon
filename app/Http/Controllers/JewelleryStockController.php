<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJewelleryStockRequest;
use App\Http\Requests\UpdateJewelleryStockRequest;
use App\Models\AppSetting;
use App\Models\JewelleryStock;
use App\Models\MetalType;
use App\Models\RingSize;
use App\Models\ClosureType;
use App\Models\StoneType;
use App\Models\StoneShape;
use App\Models\StoneColor;
use App\Models\DiamondClarity;
use App\Models\DiamondCut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CloudinaryUploadService;
use App\Services\JewelleryMaterialRateService;
use App\Services\JewelleryPricingService;

class JewelleryStockController extends Controller
{
    public function __construct(
        private CloudinaryUploadService $uploadService,
        private JewelleryPricingService $pricingService,
        private JewelleryMaterialRateService $materialRateService
    ) {
    }
    /**
     * Display a listing of jewellery stock items.
     */
    public function index(Request $request)
    {
        $query = JewelleryStock::with(['metalType', 'ringSize'])->orderBy('id', 'desc');

        // Filter by SKU (partial match)
        if ($request->filled('sku')) {
            $query->where('sku', 'like', '%' . $request->sku . '%');
        }

        // Filter by Name (partial match)
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Metal Type
        if ($request->filled('metal_type_id')) {
            $query->where('metal_type_id', $request->metal_type_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Price Range
        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', $request->max_price);
        }

        // Stats (before pagination)
        $totalItems = (clone $query)->count();
        $inStockCount = (clone $query)->where('status', 'in_stock')->count();
        $lowStockCount = (clone $query)->where('status', 'low_stock')->count();
        $outOfStockCount = (clone $query)->where('status', 'out_of_stock')->count();
        $totalValue = (clone $query)->reorder()->selectRaw('SUM(selling_price * quantity) as total')->value('total') ?? 0;

        // Pagination
        $perPage = in_array($request->per_page, [20, 50, 100]) ? $request->per_page : 20;
        $items = $query->paginate($perPage);

        // Dropdown data for filters
        $metalTypes = Cache::remember('metal_types_list', 86400, function () {
            return MetalType::where('is_active', true)->orderBy('name')->get();
        });

        return view('jewellery-stock.index', compact(
            'items',
            'metalTypes',
            'totalItems',
            'inStockCount',
            'lowStockCount',
            'outOfStockCount',
            'totalValue'
        ));
    }

    /**
     * Show the form for creating a new jewellery stock item.
     */
    public function create()
    {
        $data = $this->getLookupData();
        return view('jewellery-stock.create', $data);
    }

    /**
     * Store a newly created jewellery stock item in storage.
     */
    public function store(StoreJewelleryStockRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $pricingVariants = $validated['pricing_variants'] ?? [];
            $defaultPricingVariant = $validated['default_pricing_variant'] ?? null;
            $platinumRate = $validated['platinum_950_rate_usd_per_gram'] ?? null;
            unset($validated['pricing_variants'], $validated['default_pricing_variant'], $validated['platinum_950_rate_usd_per_gram']);
            $pricingVariants = $this->markDefaultPricingVariant($pricingVariants, $defaultPricingVariant);
            // Global platinum rate update removed - handled by .env or central settings.

            // Handle multiple images upload (same pattern as Order module)
            $uploadedImages = $this->uploadService->uploadFromRequest($request, 'images', 'jewellery-stock');
            if (!empty($uploadedImages)) {
                $validated['images'] = $uploadedImages;
                // Set primary image_url for backward compatibility
                $validated['image_url'] = $uploadedImages[0]['url'];
            }

            $jewelleryStock = JewelleryStock::create($validated);

            // Handle side stones
            if ($request->has('side_stones')) {
                foreach ($request->side_stones as $stoneData) {
                    $jewelleryStock->sideStones()->create($stoneData);
                }
            }

            if ($request->has('pricing_variants')) {
                $this->pricingService->replacePricingRows(
                    $jewelleryStock,
                    $pricingVariants,
                    auth('admin')->user()
                );
            }

            DB::commit();

            Log::info('Jewellery stock created', ['sku' => $validated['sku'], 'created_by' => auth('admin')->id()]);

            return redirect()->route('jewellery-stock.index')->with('success', 'Jewellery stock item created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Jewellery stock creation failed', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to create jewellery stock item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified jewellery stock item.
     */
    public function show(JewelleryStock $jewelleryStock)
    {
        $jewelleryStock->load([
            'metalType',
            'ringSize',
            'closureType',
            'primaryStoneType',
            'primaryStoneShape',
            'primaryStoneColor',
            'primaryStoneClarity',
            'primaryStoneCut',
            'sideStones.type',
            'sideStones.shape',
            'sideStones.color',
            'sideStones.clarity',
            'sideStones.cut',
            'pricingVariants',
        ]);

        return view('jewellery-stock.show', compact('jewelleryStock'));
    }

    /**
     * Show the form for editing the specified jewellery stock item.
     */
    public function edit(JewelleryStock $jewelleryStock)
    {
        $jewelleryStock->load('pricingVariants');
        $data = $this->getLookupData();
        $data['jewelleryStock'] = $jewelleryStock;
        $data['pricingRows'] = $this->pricingService->formRows($jewelleryStock->pricingVariants);
        $defaultPricing = $jewelleryStock->pricingVariants->firstWhere('is_default_listing', true);
        if ($defaultPricing) {
            $data['pricingDefaults']['labor_rate_usd_per_gram'] = (float) $defaultPricing->labor_rate_usd_per_gram;
            $data['pricingDefaults']['commission_percent'] = (float) $defaultPricing->commission_percent;
            $data['pricingDefaults']['profit_percent'] = (float) $defaultPricing->profit_percent;
            $data['pricingDefaults']['sales_markup_percent'] = (float) $defaultPricing->sales_markup_percent;
        }

        return view('jewellery-stock.edit', $data);
    }

    /**
     * Get lookup data for forms.
     */
    private function getLookupData(): array
    {
        return [
            'metalTypes' => Cache::remember('metal_types_list', 86400, function () {
                return MetalType::where('is_active', true)->orderBy('name')->get();
            }),
            'ringSizes' => Cache::remember('ring_sizes_list', 86400, function () {
                return RingSize::where('is_active', true)->orderBy('name')->get();
            }),
            'closureTypes' => Cache::remember('closure_types_list', 86400, function () {
                return ClosureType::where('is_active', true)->orderBy('name')->get();
            }),
            'stoneTypes' => Cache::remember('stone_types_list', 86400, function () {
                return StoneType::where('is_active', true)->orderBy('name')->get();
            }),
            'stoneShapes' => Cache::remember('stone_shapes_list', 86400, function () {
                return StoneShape::where('is_active', true)->orderBy('name')->get();
            }),
            'stoneColors' => Cache::remember('stone_colors_list', 86400, function () {
                return StoneColor::where('is_active', true)->orderBy('name')->get();
            }),
            'diamondClarities' => Cache::remember('diamond_clarities_list', 86400, function () {
                return DiamondClarity::where('is_active', true)->orderBy('name')->get();
            }),
            'diamondCuts' => Cache::remember('diamond_cuts_list', 86400, function () {
                return DiamondCut::where('is_active', true)->orderBy('name')->get();
            }),
            'pricingDefaults' => $this->pricingService->defaultsFor(auth('admin')->user()),
            'pricingRows' => $this->pricingService->formRows(),
        ];
    }

    /**
     * Update the specified jewellery stock item in storage.
     */
    public function update(UpdateJewelleryStockRequest $request, JewelleryStock $jewelleryStock)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $pricingVariants = $validated['pricing_variants'] ?? [];
            $defaultPricingVariant = $validated['default_pricing_variant'] ?? null;
            $platinumRate = $validated['platinum_950_rate_usd_per_gram'] ?? null;
            unset($validated['pricing_variants'], $validated['default_pricing_variant'], $validated['platinum_950_rate_usd_per_gram']);
            $pricingVariants = $this->markDefaultPricingVariant($pricingVariants, $defaultPricingVariant);
            // Global platinum rate update removed - handled by .env or central settings.

            // 1. Handle removals from existing images
            $currentImages = $jewelleryStock->images ?? [];
            if ($request->has('removed_images')) {
                $removedUrls = $request->removed_images;
                $currentImages = array_filter($currentImages, function ($img) use ($removedUrls) {
                    // Check if this image URL is in the removal list
                    if (in_array($img['url'], $removedUrls)) {
                        // Delete from Cloudinary
                        $this->uploadService->deleteByUrl($img['url']);
                        return false;
                    }
                    return true;
                });
                // Re-index array
                $currentImages = array_values($currentImages);
            }

            // 2. Handle new image uploads
            $newImages = $this->uploadService->uploadFromRequest($request, 'images', 'jewellery-stock');
            if (!empty($newImages)) {
                $currentImages = array_merge($currentImages, $newImages);
            }

            $validated['images'] = $currentImages;

            // 3. Update primary image_url fallback
            if (!empty($currentImages)) {
                $validated['image_url'] = $currentImages[0]['url'];
            } else {
                $validated['image_url'] = null;
            }

            $jewelleryStock->update($validated);

            // Handle side stones sync (delete old and re-create)
            if ($request->has('side_stones')) {
                $jewelleryStock->sideStones()->delete();
                foreach ($request->side_stones as $stoneData) {
                    $jewelleryStock->sideStones()->create($stoneData);
                }
            }

            if ($request->has('pricing_variants')) {
                $this->pricingService->replacePricingRows(
                    $jewelleryStock,
                    $pricingVariants,
                    auth('admin')->user()
                );
            }

            DB::commit();

            Log::info('Jewellery stock updated', ['id' => $jewelleryStock->id, 'sku' => $jewelleryStock->sku, 'updated_by' => auth('admin')->id()]);

            return redirect()->route('jewellery-stock.index')->with('success', 'Jewellery stock item updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Jewellery stock update failed', [
                'id' => $jewelleryStock->id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to update jewellery stock item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified jewellery stock item from storage (soft delete).
     */
    public function destroy(JewelleryStock $jewelleryStock)
    {
        try {
            $itemId = $jewelleryStock->id;
            $sku = $jewelleryStock->sku;

            $jewelleryStock->delete();

            Log::info('Jewellery stock deleted', ['id' => $itemId, 'sku' => $sku, 'deleted_by' => auth('admin')->id()]);

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Deleted']);
            }

            return redirect()->route('jewellery-stock.index')->with('success', 'Jewellery stock item deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Jewellery stock deletion failed', [
                'id' => $jewelleryStock->id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
            ]);
            return back()->with('error', 'Failed to delete jewellery stock item. Please try again or contact support.');
        }
    }

    /**
     * Check SKU availability (AJAX endpoint).
     */
    public function checkSku(Request $request)
    {
        $sku = trim($request->input('sku', ''));
        $excludeId = $request->input('exclude_id');

        if (empty($sku)) {
            return response()->json(['available' => false, 'message' => 'SKU is required'], 400);
        }

        $query = JewelleryStock::where('sku', $sku);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'SKU already exists' : 'SKU is available',
        ]);
    }

    public function pricingRates()
    {
        $admin = auth('admin')->user();
        $defaults = $this->pricingService->defaultsFor($admin);
        if (!$defaults['can_view_profit'] && !$defaults['can_edit_profit']) {
            $defaults['profit_percent'] = null;
        }

        $rates = $this->materialRateService->currentRates();
        $envPlatinumRate = env('JEWELLERY_PLATINUM_RATE');
        $rates['is_platinum_locked'] = $envPlatinumRate !== null && $envPlatinumRate !== '';

        return response()->json([
            'success' => true,
            'rates' => $rates,
            'defaults' => $defaults,
        ]);
    }

    private function markDefaultPricingVariant(array $pricingVariants, ?string $defaultKey): array
    {
        foreach ($pricingVariants as $key => $variant) {
            $pricingVariants[$key]['is_default_listing'] = $defaultKey === (string) $key;
        }

        return $pricingVariants;
    }


}
