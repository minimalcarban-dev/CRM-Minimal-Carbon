<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJewelleryStockRequest;
use App\Http\Requests\UpdateJewelleryStockRequest;
use App\Models\JewelleryStock;
use App\Models\MetalType;
use App\Models\RingSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

class JewelleryStockController extends Controller
{
    private $cloudinary;

    public function __construct()
    {
        // Initialize Cloudinary with direct configuration
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
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
        $totalValue = (clone $query)->selectRaw('SUM(selling_price * quantity) as total')->value('total') ?? 0;

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
        $metalTypes = Cache::remember('metal_types_list', 86400, function () {
            return MetalType::where('is_active', true)->orderBy('name')->get();
        });

        $ringSizes = Cache::remember('ring_sizes_list', 86400, function () {
            return RingSize::where('is_active', true)->orderBy('name')->get();
        });

        return view('jewellery-stock.create', compact('metalTypes', 'ringSizes'));
    }

    /**
     * Store a newly created jewellery stock item in storage.
     */
    public function store(StoreJewelleryStockRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Handle image upload to Cloudinary
            if ($request->hasFile('image_upload')) {
                try {
                    $uploadedFile = $this->uploadToCloudinary($request, 'image_upload', 'jewellery-stock');
                    if ($uploadedFile) {
                        $validated['image_url'] = $uploadedFile['url'];
                    }
                } catch (\Exception $e) {
                    Log::error('Cloudinary upload failed for jewellery stock (store): ' . $e->getMessage());
                    session()->flash('warning', 'Item created but image upload failed. Please try re-uploading the image.');
                }
            }

            // Remove the temporary file upload field from validated data if present
            unset($validated['image_upload']);

            JewelleryStock::create($validated);

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
        $jewelleryStock->load(['metalType', 'ringSize']);

        return view('jewellery-stock.show', compact('jewelleryStock'));
    }

    /**
     * Show the form for editing the specified jewellery stock item.
     */
    public function edit(JewelleryStock $jewelleryStock)
    {
        $metalTypes = Cache::remember('metal_types_list', 86400, function () {
            return MetalType::where('is_active', true)->orderBy('name')->get();
        });

        $ringSizes = Cache::remember('ring_sizes_list', 86400, function () {
            return RingSize::where('is_active', true)->orderBy('name')->get();
        });

        return view('jewellery-stock.edit', compact('jewelleryStock', 'metalTypes', 'ringSizes'));
    }

    /**
     * Update the specified jewellery stock item in storage.
     */
    public function update(UpdateJewelleryStockRequest $request, JewelleryStock $jewelleryStock)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Handle image upload to Cloudinary
            if ($request->hasFile('image_upload')) {
                try {
                    $uploadedFile = $this->uploadToCloudinary($request, 'image_upload', 'jewellery-stock');
                    if ($uploadedFile) {
                        $validated['image_url'] = $uploadedFile['url'];
                        // Optional: if you want to delete the old image, you'll need to store its public_id,
                        // but currently only image_url is stored in DB.
                    }
                } catch (\Exception $e) {
                    Log::error('Cloudinary upload failed for jewellery stock (update): ' . $e->getMessage());
                }
            }

            // Remove the temporary file upload field from validated data if present
            unset($validated['image_upload']);

            $jewelleryStock->update($validated);

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

    /**
     * Upload a single file to Cloudinary.
     */
    private function uploadToCloudinary(Request $request, string $field, string $folder): ?array
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);

        if (!$file->isValid()) {
            return null;
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $timestamp = time();
        $uniqueId = uniqid();

        $publicId = "{$timestamp}_{$uniqueId}";
        $uploadOptions = [
            'public_id' => $publicId,
            'folder' => $folder,
            'transformation' => [
                'quality' => 'auto:good',
                'fetch_format' => 'auto'
            ]
        ];
        $uploadApi = $this->cloudinary->uploadApi();
        $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
            'name' => $originalName . '.' . $extension,
            'format' => $extension,
            'size' => $file->getSize(),
        ];
    }
}
