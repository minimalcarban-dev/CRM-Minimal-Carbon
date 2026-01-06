<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiamondRequest;
use App\Http\Requests\UpdateDiamondRequest;
use App\Models\Diamond;
use App\Models\Admin;
use App\Models\DiamondClarity;
use App\Models\DiamondCut;
use App\Models\JobTrack;
use App\Notifications\DiamondAssignedNotification;
use App\Notifications\DiamondReassignedNotification;
use App\Notifications\DiamondSoldNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DiamondsImport;
use App\Exports\DiamondsExport;
use App\Jobs\ProcessDiamondImport;
use App\Jobs\ProcessDiamondExport;
use App\Models\StoneColor;
use App\Models\StoneShape;
use App\Models\StoneType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\CurrencyService;

class DiamondController extends Controller
{
    /**
     * Show the form for creating a new diamond.
     */
    public function create()
    {
        $admins = Cache::remember('admins_list', 3600, function () {
            return Admin::orderBy('name')->get();
        });

        $stoneTypes = Cache::remember('stone_types_list', 86400, function () {
            return StoneType::orderBy('name')->get();
        });

        $stoneShapes = Cache::remember('stone_shapes_list', 86400, function () {
            return StoneShape::orderBy('name')->get();
        });

        $stoneColors = Cache::remember('stone_colors_list', 86400, function () {
            return StoneColor::orderBy('name')->get();
        });

        $diamondCuts = Cache::remember('diamond_cuts_list', 86400, function () {
            return DiamondCut::orderBy('name')->get();
        });

        $diamondClarities = Cache::remember('diamond_clarities_list', 86400, function () {
            return DiamondClarity::orderBy('name')->get();
        });

        return view('diamonds.create', compact('admins', 'stoneTypes', 'stoneShapes', 'stoneColors', 'diamondCuts', 'diamondClarities'));
    }

    /**
     * Display a listing of the diamonds.
     */
    public function index(Request $request)
    {
        $currentAdmin = auth('admin')->user();
        $query = Diamond::orderBy('id', 'desc');

        // If not super admin, only show diamonds assigned to them
        if ($currentAdmin && !$currentAdmin->is_super) {
            $query->where('admin_id', $currentAdmin->id);
        }

        // Filter by SKU (partial match)
        if ($request->filled('sku')) {
            $query->where('sku', 'like', '%' . $request->sku . '%');
        }

        // Filter by Lot Number (partial match)
        if ($request->filled('lot_no')) {
            $query->where('lot_no', 'like', '%' . $request->lot_no . '%');
        }

        // Filter by Shape (exact match)
        if ($request->filled('shape')) {
            $query->where('shape', $request->shape);
        }

        // Filter by Cut
        if ($request->filled('cut')) {
            $query->where('cut', $request->cut);
        }

        // Filter by Clarity
        if ($request->filled('clarity')) {
            $query->where('clarity', $request->clarity);
        }

        // Filter by Color
        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }

        // Filter by Material
        if ($request->filled('material')) {
            $query->where('material', $request->material);
        }

        // Filter by Status
        if ($request->filled('status')) {
            if ($request->status === 'IN Stock') {
                $query->where('is_sold_out', 'IN Stock');
            } elseif ($request->status === 'Sold') {
                $query->where('is_sold_out', 'Sold');
            }
        }

        // Filter by Diamond Type
        if ($request->filled('diamond_type')) {
            $query->where('diamond_type', $request->diamond_type);
        }

        // Filter by Price Range
        if ($request->filled('min_price')) {
            $query->where('purchase_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('purchase_price', '<=', $request->max_price);
        }

        // Filter by Weight Range
        if ($request->filled('min_weight')) {
            $query->where('weight', '>=', $request->min_weight);
        }
        if ($request->filled('max_weight')) {
            $query->where('weight', '<=', $request->max_weight);
        }

        // Filter by Assigned Admin
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // Get total count before pagination
        $totalDiamonds = $query->count();

        // Calculate total value and average price from ALL filtered diamonds (before pagination)
        // Clone query to avoid affecting pagination
        $totalValue = (clone $query)->sum('purchase_price');
        $avgPrice = $totalDiamonds > 0 ? $totalValue / $totalDiamonds : 0;

        // Get IN Stock count
        $inStockCount = Diamond::where('is_sold_out', 'IN Stock')->count();

        // Get Sold count
        $soldCount = Diamond::where('is_sold_out', 'Sold')->count();

        // Get all diamonds count (both in stock + sold)
        $allDiamondsCount = $inStockCount + $soldCount;

        // Get per_page from request (default 20, allowed: 20, 50, 100)
        $perPage = in_array($request->per_page, [20, 50, 100]) ? $request->per_page : 20;

        // Get results
        $diamonds = $query->paginate($perPage);

        // Get distinct values for filter dropdowns
        $shapes = Diamond::select('shape')->distinct()->whereNotNull('shape')->pluck('shape')->filter()->sort();
        $cuts = Diamond::select('cut')->distinct()->whereNotNull('cut')->pluck('cut')->filter()->sort();
        $clarities = Diamond::select('clarity')->distinct()->whereNotNull('clarity')->pluck('clarity')->filter()->sort();
        $colors = Diamond::select('color')->distinct()->whereNotNull('color')->pluck('color')->filter()->sort();
        $materials = Diamond::select('material')->distinct()->whereNotNull('material')->pluck('material')->filter()->sort();
        $diamondTypes = Diamond::select('diamond_type')->distinct()->whereNotNull('diamond_type')->pluck('diamond_type')->filter()->sort();

        // Get all admins for reassignment dropdown (cached)
        $admins = Cache::remember('admins_list', 3600, function () {
            return Admin::orderBy('name')->get();
        });

        return view('diamonds.index', compact('diamonds', 'shapes', 'cuts', 'clarities', 'colors', 'materials', 'diamondTypes', 'admins', 'totalDiamonds', 'inStockCount', 'soldCount', 'allDiamondsCount', 'totalValue', 'avgPrice'));
    }

    /**
     * Display the specified diamond.
     */
    public function show(Diamond $diamond)
    {
        return view('diamonds.show', compact('diamond'));
    }

    /**
     * Show the form for editing the specified diamond.
     */
    public function edit(Diamond $diamond)
    {
        $admins = Cache::remember('admins_list', 3600, function () {
            return Admin::orderBy('name')->get();
        });

        $stoneShapes = Cache::remember('stone_shapes_list', 86400, function () {
            return StoneShape::orderBy('name')->get();
        });

        $stoneColors = Cache::remember('stone_colors_list', 86400, function () {
            return StoneColor::orderBy('name')->get();
        });

        $diamondCuts = Cache::remember('diamond_cuts_list', 86400, function () {
            return DiamondCut::orderBy('name')->get();
        });

        $diamondClarities = Cache::remember('diamond_clarities_list', 86400, function () {
            return DiamondClarity::orderBy('name')->get();
        });

        return view('diamonds.edit', compact('diamond', 'admins', 'stoneShapes', 'stoneColors', 'diamondCuts', 'diamondClarities'));
    }

    /**
     * Store a newly created diamond in storage.
     */
    public function store(StoreDiamondRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $currentAdmin = auth('admin')->user();

            // Convert price fields from INR to USD
            $currencyService = app(CurrencyService::class);
            $validated = $currencyService->convertPriceFields($validated);

            // Use user-provided SKU instead of auto-generating
            $sku = $validated['sku'];

            // Auto-generate listing_price if not provided: purchase_price + margin%
            // Note: purchase_price is already converted to USD at this point
            $listingPrice = $validated['listing_price'] ?? (
                (isset($validated['purchase_price']) && isset($validated['margin']))
                ? ($validated['purchase_price'] * (1 + ($validated['margin'] / 100)))
                : null
            );

            // Generate barcode number and image
            $barcodeNumber = $this->buildBarcodeNumber($validated['lot_no']);
            $dataUri = $this->generateBarcodeDataUri($sku, $barcodeNumber);

            // Handle multiple image uploads
            $imageUrls = [];
            if ($request->hasFile('multi_img_upload')) {
                foreach ($request->file('multi_img_upload') as $image) {
                    $path = $image->store('diamonds', 'public');
                    $imageUrls[] = '/storage/' . $path;
                }
            }

            $assignById = $currentAdmin ? $currentAdmin->id : null;

            // Create diamond record (maintain backward compatibility with admin_id if provided)
            $adminId = $validated['admin_id'] ?? null;
            $assignedAt = $adminId ? now() : null;

            $diamond = Diamond::create([
                'lot_no' => $validated['lot_no'],
                'sku' => $sku,
                'margin' => $validated['margin'],
                'listing_price' => $listingPrice,
                'cut' => $validated['cut'] ?? null,
                'shape' => $validated['shape'] ?? null,
                'measurement' => $validated['measurement'] ?? null,
                'weight' => $validated['weight'] ?? 0,
                'per_ct' => $validated['per_ct'] ?? null,
                'purchase_price' => $validated['purchase_price'] ?? null,
                'shipping_price' => $validated['shipping_price'] ?? 0,
                'purchase_date' => $validated['purchase_date'] ?? null,
                'sold_out_date' => $validated['sold_out_date'] ?? null,
                'is_sold_out' => $validated['is_sold_out'] ?? 'IN Stock',
                'duration_days' => $validated['duration_days'] ?? 0,
                'duration_price' => $validated['duration_price'] ?? 0,
                'sold_out_price' => $validated['sold_out_price'] ?? null,
                'profit' => $validated['profit'] ?? null,
                'sold_out_month' => $validated['sold_out_month'] ?? null,
                'barcode_number' => $barcodeNumber,
                'barcode_image_url' => $dataUri,
                'description' => $validated['description'] ?? null,
                'admin_id' => $adminId,
                'note' => $validated['note'] ?? null,
                'diamond_type' => $validated['diamond_type'] ?? null,
                'multi_img_upload' => $imageUrls ?: null,
                'assign_by' => $assignById,
                'assigned_at' => $assignedAt,
            ]);

            // NOTE: is_sold_out, duration_days, duration_price, sold_out_month are calculated
            // automatically by model's boot event (recalculateDerivedFields) - no need for manual calc

            // If user manually provided sold_out_price, set it (profit will be calculated by model)
            if (!empty($validated['sold_out_price'])) {
                $diamond->sold_out_price = $validated['sold_out_price'];
                $diamond->save();
            }

            // Send notification if diamond is assigned to an admin
            if ($adminId && $currentAdmin) {
                $assignedAdmin = Admin::find($adminId);
                if ($assignedAdmin) {
                    Notification::sendNow($assignedAdmin, new DiamondAssignedNotification($diamond, $currentAdmin));
                }
            }

            DB::commit();
            Log::info('Diamond created successfully', ['diamond_id' => $diamond->id, 'sku' => $diamond->sku, 'created_by' => $currentAdmin?->id]);

            return redirect()->route('diamond.index')->with('success', 'Diamond created');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Diamond creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth('admin')->id()
            ]);
            return back()->withInput()->with('error', 'Failed to create diamond: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified diamond in storage.
     */
    public function update(UpdateDiamondRequest $request, Diamond $diamond)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $currentAdmin = auth('admin')->user();

            // Track original sold status to detect if diamond is being marked as sold
            $wasNotSold = $diamond->is_sold_out !== 'Sold';

            // Detect changes for lot_no and SKU independently
            $lotNoChanged = $validated['lot_no'] != $diamond->lot_no;
            $skuChanged = $validated['sku'] != $diamond->sku;

            // If lot_no changed, regenerate barcode_number and image (image uses SKU)
            if ($lotNoChanged) {
                $sku = $validated['sku'];
                $newBarcodeNumber = $this->buildBarcodeNumber($validated['lot_no']);
                $dataUri = $this->generateBarcodeDataUri($sku, $newBarcodeNumber);

                // Remove old barcode image files if they exist
                $this->deleteOldBarcodeFiles($diamond->barcode_number);

                $diamond->barcode_number = $newBarcodeNumber;
                $diamond->sku = $sku;
                $diamond->barcode_image_url = $dataUri;
            } elseif ($skuChanged) {
                // SKU changed but lot_no same: regenerate only barcode image
                $sku = $validated['sku'];
                $dataUri = $this->generateBarcodeDataUri($sku, $diamond->barcode_number);
                $diamond->sku = $sku;
                $diamond->barcode_image_url = $dataUri;
            }

            // Handle single admin assignment
            $oldAdminId = $diamond->admin_id;
            $newAdminId = $validated['admin_id'] ?? null;

            if ($newAdminId != $oldAdminId) {
                // Send reassignment notification to previous admin
                if ($oldAdminId) {
                    $oldAdmin = Admin::find($oldAdminId);
                    if ($oldAdmin) {
                        Notification::sendNow($oldAdmin, new DiamondReassignedNotification($diamond, $currentAdmin, $oldAdmin));
                    }
                }

                // Send assignment notification to new admin
                if ($newAdminId && $currentAdmin) {
                    $newAdmin = Admin::find($newAdminId);
                    if ($newAdmin) {
                        Notification::sendNow($newAdmin, new DiamondAssignedNotification($diamond, $currentAdmin));
                    }
                }

                $diamond->admin_id = $newAdminId;
                $diamond->assign_by = $currentAdmin ? $currentAdmin->id : null;
                $diamond->assigned_at = $newAdminId ? now() : null;
            }

            // Handle multi image uploads
            if ($request->hasFile('multi_img_upload')) {
                $imageUrls = [];
                foreach ($request->file('multi_img_upload') as $image) {
                    $path = $image->store('diamonds', 'public');
                    $imageUrls[] = '/storage/' . $path;
                }
                $diamond->multi_img_upload = $imageUrls;
            }

            // listing price default based on purchase_price and margin
            $listingPrice = $validated['listing_price'] ?? (
                (isset($validated['purchase_price']) && isset($validated['margin']))
                ? ($validated['purchase_price'] * (1 + ($validated['margin'] / 100)))
                : $diamond->listing_price
            );

            $diamond->lot_no = $validated['lot_no'];
            $diamond->margin = $validated['margin'];
            $diamond->listing_price = $listingPrice;
            $diamond->cut = $validated['cut'] ?? null;
            $diamond->shape = $validated['shape'] ?? null;
            $diamond->measurement = $validated['measurement'] ?? null;
            $diamond->weight = $validated['weight'] ?? 0;
            $diamond->per_ct = $validated['per_ct'] ?? null;
            $diamond->purchase_price = $validated['purchase_price'] ?? null;
            $diamond->shipping_price = $validated['shipping_price'] ?? 0;
            $diamond->purchase_date = $validated['purchase_date'] ?? null;
            $diamond->sold_out_date = $validated['sold_out_date'] ?? null;
            $diamond->is_sold_out = $validated['is_sold_out'] ?? $diamond->is_sold_out;
            $diamond->duration_days = $validated['duration_days'] ?? $diamond->duration_days;
            $diamond->duration_price = $validated['duration_price'] ?? $diamond->duration_price;
            $diamond->sold_out_price = $validated['sold_out_price'] ?? $diamond->sold_out_price;
            $diamond->profit = $validated['profit'] ?? $diamond->profit;
            $diamond->sold_out_month = $validated['sold_out_month'] ?? $diamond->sold_out_month;
            $diamond->description = $validated['description'] ?? null;
            $diamond->note = $validated['note'] ?? null;
            $diamond->diamond_type = $validated['diamond_type'] ?? null;

            // Track who modified this diamond
            $diamond->last_modified_by = $currentAdmin ? $currentAdmin->id : null;

            $diamond->save();

            // NOTE: is_sold_out, duration_days, duration_price, sold_out_month are calculated
            // automatically by model's boot event (recalculateDerivedFields) - no need for manual calc

            DB::commit();
            Log::info('Diamond updated successfully', ['diamond_id' => $diamond->id, 'sku' => $diamond->sku, 'updated_by' => $currentAdmin?->id]);

            // Send notification to all admins if diamond was marked as sold
            if ($wasNotSold && $diamond->is_sold_out === 'Sold' && $currentAdmin) {
                $allAdmins = Admin::where('id', '!=', $currentAdmin->id)->get();
                foreach ($allAdmins as $admin) {
                    try {
                        Notification::sendNow($admin, new DiamondSoldNotification($diamond, $currentAdmin));
                    } catch (\Throwable $e) {
                        Log::error('Failed to send diamond sold notification', [
                            'admin_id' => $admin->id,
                            'diamond_id' => $diamond->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            if ($request->wantsJson()) {
                return response()->json($diamond);
            }

            return redirect()->route('diamond.index')->with('success', 'Diamond updated');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Diamond update failed', [
                'diamond_id' => $diamond->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth('admin')->id()
            ]);
            return back()->withInput()->with('error', 'Failed to update diamond: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified diamond from storage.
     */
    public function destroy(Diamond $diamond)
    {
        try {
            $diamondId = $diamond->id;
            $sku = $diamond->sku;

            // Delete barcode files
            $this->deleteOldBarcodeFiles($diamond->barcode_number);

            $diamond->delete();

            Log::info('Diamond deleted successfully', ['diamond_id' => $diamondId, 'sku' => $sku, 'deleted_by' => auth('admin')->id()]);

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Deleted']);
            }

            return redirect()->route('diamond.index')->with('success', 'Diamond deleted');
        } catch (\Exception $e) {
            Log::error('Diamond deletion failed', [
                'diamond_id' => $diamond->id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);
            return back()->with('error', 'Failed to delete diamond: ' . $e->getMessage());
        }
    }

    /**
     * Assign diamond to an admin with notification
     */
    public function assignToAdmin(Request $request, Diamond $diamond)
    {
        $request->validate([
            'admin_id' => 'required|exists:admins,id',
        ]);

        $currentAdmin = auth('admin')->user();
        $oldAdminId = $diamond->admin_id;
        $newAdminId = $request->admin_id;

        // Avoid duplicate notifications when assigning to the same admin
        if ($newAdminId == $oldAdminId) {
            return response()->json([
                'success' => true,
                'message' => 'Assignment unchanged',
                'assigned_to' => $diamond->assignedAdmin?->name,
            ]);
        }

        // Send reassignment notification to previous admin if exists and different
        if ($oldAdminId) {
            $oldAdmin = Admin::find($oldAdminId);
            if ($oldAdmin) {
                Notification::sendNow($oldAdmin, new DiamondReassignedNotification($diamond, $currentAdmin, $oldAdmin));
            }
        }

        // Send assignment notification to new admin
        $newAdmin = Admin::find($newAdminId);
        if ($newAdmin && $currentAdmin) {
            Notification::sendNow($newAdmin, new DiamondAssignedNotification($diamond, $currentAdmin));
        }

        // Update diamond assignment
        $diamond->update([
            'admin_id' => $newAdminId,
            'assign_by' => $currentAdmin ? $currentAdmin->id : null,
            'assigned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diamond assigned successfully',
            'assigned_to' => $newAdmin->name,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            // Store uploaded file
            $file = $request->file('file');
            $fileName = 'import_' . now()->format('Ymd_His') . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('imports', $fileName, 'local');
            $fullPath = storage_path('app/' . $filePath);

            // Create job track record
            $jobTrack = JobTrack::create([
                'admin_id' => auth()->guard('admin')->id(),
                'type' => 'import',
                'status' => 'queued',
                'file_name' => $fileName,
                'file_path' => $fullPath,
            ]);

            // Dispatch background job
            ProcessDiamondImport::dispatch(
                $jobTrack->id,
                $fullPath,
                auth()->guard('admin')->id()
            );

            return redirect()
                ->route('diamond.job.status', $jobTrack->id)
                ->with('success', '🚀 Import job queued! Job ID: #' . $jobTrack->id);

        } catch (\Exception $e) {
            Log::error('Diamond Import Queue Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Failed to queue import: ' . $e->getMessage());
        }
    }

    public function importResult()
    {
        if (!session()->has('import_success')) {
            return redirect()->route('diamond.index');
        }

        return view('diamonds.import-result');
    }

    public function downloadErrorReport($fileName)
    {
        $filePath = storage_path('app/public/imports/errors/' . $fileName);

        if (!file_exists($filePath)) {
            return back()->with('error', 'Error report file not found.');
        }

        return response()->download($filePath, $fileName);
    }

    public function export(Request $request)
    {
        try {
            // Create job track record
            $jobTrack = JobTrack::create([
                'admin_id' => auth()->guard('admin')->id(),
                'type' => 'export',
                'status' => 'queued',
                'filters' => $request->except(['_token']),
            ]);

            // Dispatch background job
            ProcessDiamondExport::dispatch(
                $jobTrack->id,
                $request->except(['_token']),
                auth()->guard('admin')->id()
            );

            return redirect()
                ->route('diamond.job.status', $jobTrack->id)
                ->with('success', '🚀 Export job queued! Job ID: #' . $jobTrack->id);

        } catch (\Exception $e) {
            Log::error('Diamond Export Queue Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to queue export: ' . $e->getMessage());
        }
    }

    /**
     * Mark diamond as sold out when its SKU is used in an order.
     * Sets is_sold_out, sold_out_date, sold_out_price, profit, sold_out_month,
     * and recomputes duration_days and duration_price based on purchase_date.
     */
    public function markSoldOutBySku(string $sku, float $soldOutPrice): ?Diamond
    {
        // Validate sold price
        if ($soldOutPrice < 0) {
            Log::warning('Negative sold price attempted', ['sku' => $sku, 'price' => $soldOutPrice]);
            return null;
        }

        $diamond = Diamond::where('sku', $sku)->first();
        if (!$diamond) {
            Log::warning('Diamond not found for SKU', ['sku' => $sku]);
            return null;
        }

        try {
            $diamond->markAsSold($soldOutPrice);
            $diamond->save();

            Log::info('Diamond marked as sold', ['diamond_id' => $diamond->id, 'sku' => $sku, 'sold_price' => $soldOutPrice]);
            return $diamond;
        } catch (\Exception $e) {
            Log::error('Failed to mark diamond as sold', [
                'sku' => $sku,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Restock a sold diamond by creating a new copy with an incremented SKU suffix.
     * Original diamond remains Sold (so audit history is intact).
     */
    public function restockAction(Request $request, Diamond $diamond)
    {
        // Check if diamond is sold using sold_out_date (consistent with status display logic)
        if (empty($diamond->sold_out_date)) {
            return back()->with('error', 'Only sold diamonds can be restocked.');
        }

        // Determine next SKU suffix (e.g., DIA-001 -> DIA-001-A -> DIA-001-B ...)
        $newSku = $this->generateRestockSku($diamond->sku);

        // Derive the next lot number - using PHP to extract numeric parts (MySQL 5.7 compatible)
        $allLotNos = Diamond::pluck('lot_no')->toArray();
        $maxLotNo = 0;
        foreach ($allLotNos as $lotNo) {
            $numericPart = (int) preg_replace('/[^0-9]/', '', $lotNo);
            if ($numericPart > $maxLotNo) {
                $maxLotNo = $numericPart;
            }
        }
        $newLotNo = $maxLotNo + 1;

        // Generate unique barcode number by finding max existing barcode and incrementing
        $barcodeNumber = $this->generateUniqueBarcodeNumber($newLotNo);
        $barcodeData = $this->generateBarcodeDataUri($newSku, $barcodeNumber);

        // Duplicate the diamond and reset inventory-specific fields
        $newDiamond = $diamond->replicate([
            'lot_no',
            'sku',
            'barcode_number',
            'barcode_image_url',
            'is_sold_out',
            'sold_out_date',
            'sold_out_month',
            'sold_out_price',
            'profit',
            'duration_days',
            'duration_price',
        ]);

        $newDiamond->lot_no = $newLotNo;
        $newDiamond->sku = $newSku;
        $newDiamond->barcode_number = $barcodeNumber;
        $newDiamond->barcode_image_url = $barcodeData;
        $newDiamond->is_sold_out = 'IN Stock';
        $newDiamond->sold_out_date = null;
        $newDiamond->sold_out_month = null;
        $newDiamond->sold_out_price = null;
        $newDiamond->profit = null;
        $newDiamond->purchase_date = now()->toDateString();
        $newDiamond->duration_days = 0;
        $base = (float) ($newDiamond->purchase_price ?? 0);
        $newDiamond->duration_price = $base; // At day 0, duration_price = purchase_price
        $newDiamond->assigned_at = $newDiamond->admin_id ? now() : null;
        $newDiamond->save();

        // Ensure original diamond stays in Sold state with proper meta
        $soldAt = now();
        if (empty($diamond->sold_out_date)) {
            $diamond->sold_out_date = $soldAt->toDateString();
        }
        $diamond->is_sold_out = 'Sold';
        $diamond->sold_out_month = $diamond->sold_out_date
            ? \Carbon\Carbon::parse($diamond->sold_out_date)->format('Y-m')
            : $soldAt->format('Y-m');
        if ($diamond->purchase_date) {
            $pd = \Carbon\Carbon::parse($diamond->purchase_date);
            $diamond->duration_days = max(0, $pd->diffInDays($diamond->sold_out_date ? \Carbon\Carbon::parse($diamond->sold_out_date) : $soldAt));
        }
        $baseOriginal = (float) ($diamond->purchase_price ?? 0);
        $shipping = (float) ($diamond->shipping_price ?? 0);
        $days = (int) ($diamond->duration_days ?? 0);
        $dailyRate = 0.0005; // 0.05% per day
        $diamond->duration_price = round($baseOriginal * pow(1 + $dailyRate, $days), 2);
        if (!empty($diamond->sold_out_price) && $baseOriginal > 0) {
            $diamond->profit = round(($diamond->sold_out_price ?? 0) - $baseOriginal - $shipping, 2);
        }
        $diamond->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Diamond restocked as {$newSku}",
                'new_diamond' => $newDiamond,
            ]);
        }

        return back()->with('success', "Diamond restocked as SKU {$newSku} (Lot {$newLotNo}).");
    }

    /**
     * Generate the next SKU for restocked diamonds.
     */
    protected function generateRestockSku(string $baseSku): string
    {
        $pattern = '/^(.*?)-(?:[A-Z]+)$/';
        $root = preg_match($pattern, $baseSku, $matches) ? $matches[1] : $baseSku;

        $suffix = preg_match('/-([A-Z]+)$/', $baseSku, $suffixMatch) ? $suffixMatch[1] : '';

        do {
            $suffix = $this->incrementAlphaSuffix($suffix);
            $candidate = $root . '-' . $suffix;
        } while (Diamond::where('sku', $candidate)->exists());

        return $candidate;
    }

    protected function incrementAlphaSuffix(?string $suffix): string
    {
        if (empty($suffix)) {
            return 'A';
        }

        $chars = str_split($suffix);
        $index = count($chars) - 1;
        $carry = true;

        while ($index >= 0 && $carry) {
            if ($chars[$index] === 'Z') {
                $chars[$index] = 'A';
                $index--;
            } else {
                $chars[$index] = chr(ord($chars[$index]) + 1);
                $carry = false;
            }
        }

        if ($carry) {
            array_unshift($chars, 'A');
        }

        return implode('', $chars);
    }

    /**
     * Build barcode number from lot no (YY + brand + padded lot).
     * Extracts numeric part from lot_no if it contains letters.
     * Ensures uniqueness by appending a suffix if barcode already exists.
     * 
     * Real-life uses:
     * - Multi-brand identification (Brand A: 100, Brand B: 200)
     * - Store/location tracking (Mumbai: 101, Delhi: 102)
     * - Category separation (Natural: 100, Lab-grown: 200)
     * - Supplier identification (Supplier A: 501, Supplier B: 502)
     */
    protected function buildBarcodeNumber(string $lotNo): string
    {
        $year = date('y');

        // Get brand code from environment variable
        $brandCode = env('DIAMOND_BRAND_CODE', '100');

        // Extract numeric part from lot_no (e.g., "L0010078" -> "10078")
        $numericLot = preg_replace('/[^0-9]/', '', $lotNo);

        // If no numbers found, use 0
        $numericLot = $numericLot ?: '0';

        $paddedLot = str_pad($numericLot, 6, '0', STR_PAD_LEFT);
        $baseBarcode = $year . $brandCode . $paddedLot;

        // Check if this barcode already exists, if so, append a unique suffix
        $barcode = $baseBarcode;
        $counter = 1;
        while (Diamond::where('barcode_number', $barcode)->exists()) {
            $barcode = $baseBarcode . $counter;
            $counter++;
        }

        return $barcode;
    }

    /**
     * Generate a unique barcode number that doesn't exist in the database.
     * Falls back to incrementing if the calculated barcode already exists.
     */
    protected function generateUniqueBarcodeNumber($lotNo): string
    {
        $barcodeNumber = $this->buildBarcodeNumber((string) $lotNo);

        // If barcode already exists, find the max numeric barcode and increment
        if (Diamond::where('barcode_number', $barcodeNumber)->exists()) {
            // Get max barcode number from database (last 6 digits are the lot number)
            $maxBarcode = Diamond::max('barcode_number');

            if ($maxBarcode) {
                $year = date('y');
                $brandCode = env('DIAMOND_BRAND_CODE', '100');

                // Extract the numeric lot portion (last 6 digits)
                $existingLotPart = (int) substr($maxBarcode, -6);
                $newLotPart = $existingLotPart + 1;

                $barcodeNumber = $year . $brandCode . str_pad($newLotPart, 6, '0', STR_PAD_LEFT);
            }
        }

        // Final safety check: keep incrementing if still not unique
        $attempts = 0;
        while (Diamond::where('barcode_number', $barcodeNumber)->exists() && $attempts < 100) {
            $year = date('y');
            $brandCode = env('DIAMOND_BRAND_CODE', '100');
            $numericPart = (int) substr($barcodeNumber, -6) + 1;
            $barcodeNumber = $year . $brandCode . str_pad($numericPart, 6, '0', STR_PAD_LEFT);
            $attempts++;
        }

        return $barcodeNumber;
    }

    /**
     * Delete old barcode files (SVG and PNG).
     */
    protected function deleteOldBarcodeFiles(?string $barcodeNumber): void
    {
        if (!$barcodeNumber) {
            return;
        }

        $publicDir = public_path('barcodes');
        $svgFile = $publicDir . DIRECTORY_SEPARATOR . 'barcode_' . $barcodeNumber . '.svg';
        $pngFile = $publicDir . DIRECTORY_SEPARATOR . 'barcode_' . $barcodeNumber . '.png';

        try {
            if (file_exists($svgFile)) {
                unlink($svgFile);
            }
            if (file_exists($pngFile)) {
                unlink($pngFile);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete barcode files', [
                'barcode_number' => $barcodeNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate barcode SVG data URI and persist SVG file.
     */
    protected function generateBarcodeDataUri(string $sku, string $barcodeNumber): string
    {
        try {
            $generator = new BarcodeGeneratorSVG();
            $svgContent = $generator->getBarcode($sku, $generator::TYPE_CODE_128);
            $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svgContent);

            $publicDir = public_path('barcodes');
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            $filePath = $publicDir . DIRECTORY_SEPARATOR . 'barcode_' . $barcodeNumber . '.svg';
            file_put_contents($filePath, $svgContent);

            return $dataUri;
        } catch (\Exception $e) {
            Log::error('Barcode generation failed', [
                'sku' => $sku,
                'barcode_number' => $barcodeNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Show job status page
     */
    public function jobStatus($id)
    {
        $jobTrack = JobTrack::with('admin')->findOrFail($id);

        // Only allow admin to view their own jobs or super admins
        if ($jobTrack->admin_id !== auth()->guard('admin')->id() && !auth()->guard('admin')->user()->is_super) {
            abort(403, 'Unauthorized');
        }

        return view('diamonds.job-status', compact('jobTrack'));
    }

    /**
     * Get job status as JSON (for AJAX polling)
     */
    public function jobStatusJson($id)
    {
        $jobTrack = JobTrack::findOrFail($id);

        if ($jobTrack->admin_id !== auth()->guard('admin')->id() && !auth()->guard('admin')->user()->is_super) {
            abort(403);
        }

        return response()->json([
            'id' => $jobTrack->id,
            'status' => $jobTrack->status,
            'progress_percentage' => $jobTrack->progress_percentage,
            'processed_rows' => $jobTrack->processed_rows,
            'total_rows' => $jobTrack->total_rows,
            'successful_rows' => $jobTrack->successful_rows,
            'failed_rows' => $jobTrack->failed_rows,
            'is_complete' => $jobTrack->isComplete(),
            'is_running' => $jobTrack->isRunning(),
            'processing_speed' => $jobTrack->getProcessingSpeed(),
            'estimated_time_remaining' => $jobTrack->getFormattedTimeRemaining(),
            'error_message' => $jobTrack->error_message,
        ]);
    }

    /**
     * Download export file
     */
    public function jobDownload($id)
    {
        $jobTrack = JobTrack::findOrFail($id);

        if ($jobTrack->admin_id !== auth()->guard('admin')->id() && !auth()->guard('admin')->user()->is_super) {
            abort(403);
        }

        if ($jobTrack->type !== 'export' || !$jobTrack->result_file_path) {
            abort(404, 'Export file not found');
        }

        $filePath = storage_path('app/public/' . $jobTrack->result_file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found or expired');
        }

        return response()->download($filePath);
    }

    /**
     * Job history page
     */
    public function jobHistory()
    {
        $jobs = JobTrack::with('admin')
            ->where('admin_id', auth()->guard('admin')->id())
            ->orWhere(function ($query) {
                $query->whereHas('admin', function ($q) {
                    $q->where('is_super', true);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('diamonds.job-history', compact('jobs'));
    }

    /**
     * Get diamonds list for bulk edit modal with filters and pagination
     */
    public function bulkEditDiamonds(Request $request)
    {
        $currentAdmin = auth('admin')->user();
        $query = Diamond::select(['id', 'sku', 'lot_no', 'shape', 'weight', 'purchase_price', 'is_sold_out'])
            ->orderBy('id', 'desc');

        // If not super admin, only show assigned diamonds
        if ($currentAdmin && !$currentAdmin->is_super) {
            $query->where('admin_id', $currentAdmin->id);
        }

        // Apply filters
        if ($request->filled('shape')) {
            $query->where('shape', $request->shape);
        }

        if ($request->filled('status')) {
            $query->where('is_sold_out', $request->status);
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('lot_no', 'like', "%{$search}%");
            });
        }

        // Get total count before pagination
        $total = $query->count();

        // Pagination
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 50);
        $diamonds = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'success' => true,
            'diamonds' => $diamonds,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => ($page * $perPage) < $total
        ]);
    }

    /**
     * Bulk edit multiple diamonds
     */
    public function bulkEdit(Request $request)
    {
        $validated = $request->validate([
            'diamond_ids' => 'required|array|min:1|max:100',
            'diamond_ids.*' => 'exists:diamonds,id',
            'fields' => 'required|array|min:1',
            'values' => 'required|array',
            'confirmation' => 'required|in:CONFIRM',
        ]);

        // Define allowed fields for bulk edit
        $allowedFields = [
            'margin',
            'shipping_price',
            'shape',
            'cut',
            'clarity',
            'color',
            'material',
            'diamond_type',
            'admin_id',
            'is_sold_out',
            'note'
        ];

        // Filter to only allowed fields
        $updateData = [];
        foreach ($validated['fields'] as $field) {
            if (in_array($field, $allowedFields) && isset($validated['values'][$field])) {
                $value = $validated['values'][$field];

                // Sanitize values
                if ($field === 'margin' || $field === 'shipping_price') {
                    $value = is_numeric($value) ? (float) $value : null;
                }
                if ($field === 'admin_id') {
                    $value = $value ? (int) $value : null;
                }

                if ($value !== null && $value !== '') {
                    $updateData[$field] = $value;
                }
            }
        }

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid fields to update'
            ], 400);
        }

        $currentAdmin = auth('admin')->user();

        try {
            DB::beginTransaction();

            // Get diamonds that belong to current admin (or all if super admin)
            $query = Diamond::whereIn('id', $validated['diamond_ids']);
            if ($currentAdmin && !$currentAdmin->is_super) {
                $query->where('admin_id', $currentAdmin->id);
            }

            // Track who made the change
            $updateData['last_modified_by'] = $currentAdmin ? $currentAdmin->id : null;

            $count = $query->update($updateData);

            // Log the bulk edit
            Log::info('Bulk edit performed', [
                'admin_id' => $currentAdmin?->id,
                'diamond_ids' => $validated['diamond_ids'],
                'fields' => array_keys($updateData),
                'count' => $count
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => "Successfully updated {$count} diamonds"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk edit failed', [
                'error' => $e->getMessage(),
                'admin_id' => $currentAdmin?->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update diamonds: ' . $e->getMessage()
            ], 500);
        }
    }
}

