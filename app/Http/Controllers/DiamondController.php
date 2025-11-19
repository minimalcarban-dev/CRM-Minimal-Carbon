<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiamondRequest;
use App\Http\Requests\UpdateDiamondRequest;
use App\Models\Diamond;
use App\Models\Admin;
use App\Notifications\DiamondAssignedNotification;
use App\Notifications\DiamondReassignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Picqer\Barcode\BarcodeGeneratorSVG;

class DiamondController extends Controller
{
    /**
     * Show the form for creating a new diamond.
     */
    public function create()
    {
        $admins = Admin::orderBy('name')->get();
        return view('diamonds.create', compact('admins'));
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

        // Filter by Shape (exact match)
        if ($request->filled('shape')) {
            $query->where('shape', $request->shape);
        }

        // Get results
        $diamonds = $query->paginate(10);

        // Get distinct shapes for dropdown
        $shapes = Diamond::select('shape')->distinct()->pluck('shape')->filter()->sort();

        // Get all admins for reassignment dropdown
        $admins = Admin::orderBy('name')->get();

        return view('diamonds.index', compact('diamonds', 'shapes', 'admins'));
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
        $admins = Admin::orderBy('name')->get();
        return view('diamonds.edit', compact('diamond', 'admins'));
    }

    /**
     * Store a newly created diamond in storage.
     */
    public function store(StoreDiamondRequest $request)
    {
        $validated = $request->validated();

        // Use user-provided SKU instead of auto-generating
        $sku = $validated['sku'];

        // Auto-generate listing_price if not provided (25% more than price)
        $listingPrice = $validated['listing_price'] ?? ($validated['price'] * 1.25);

        // Auto-generate barcode_number: YY100000001 (year + '100' + padded stockid)
        $year = date('y'); // 2-digit year
        $brandCode = '100';
        $paddedStockid = str_pad($validated['stockid'], 6, '0', STR_PAD_LEFT);
        $barcodeNumber = $year . $brandCode . $paddedStockid;

        // Create barcode with only SKU number
        $barcodeData = $sku;

        // Generate barcode using Picqer with only SKU
        $generator = new BarcodeGeneratorSVG();
        $svgContent = $generator->getBarcode($barcodeData, $generator::TYPE_CODE_128);
        
        // Convert SVG to data URI (can be used directly in img src)
        $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svgContent);
        
        $barcodeFilename = 'barcode_' . $barcodeNumber . '.svg';
        $barcodeRelativePath = 'barcodes/' . $barcodeFilename;

        // Ensure public/barcodes directory exists
        $publicDir = public_path('barcodes');
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        // Save barcode SVG for reference (optional)
        $barcodeFullPath = $publicDir . DIRECTORY_SEPARATOR . $barcodeFilename;
        file_put_contents($barcodeFullPath, $svgContent);

        // Handle multiple image uploads
        $imageUrls = [];
        if ($request->hasFile('multi_img_upload')) {
            foreach ($request->file('multi_img_upload') as $image) {
                $path = $image->store('diamonds', 'public');
                $imageUrls[] = '/storage/' . $path;
            }
        }

        // Get current admin if assigning to someone
        $currentAdmin = auth('admin')->user();
        $assignById = $currentAdmin ? $currentAdmin->id : null;

        // Create diamond record (maintain backward compatibility with admin_id if provided)
        $adminId = $validated['admin_id'] ?? null;
        $assignedAt = $adminId ? now() : null;

        $diamond = Diamond::create([
            'stockid' => $validated['stockid'],
            'sku' => $sku,
            'price' => $validated['price'],
            'listing_price' => $listingPrice,
            'cut' => $validated['cut'] ?? null,
            'shape' => $validated['shape'] ?? null,
            'measurement' => $validated['measurement'] ?? null,
            'number_of_pics' => $validated['number_of_pics'] ?? 0,
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

        // Send notification if diamond is assigned to an admin
        if ($adminId && $currentAdmin) {
            $assignedAdmin = Admin::find($adminId);
            if ($assignedAdmin) {
                Notification::sendNow($assignedAdmin, new DiamondAssignedNotification($diamond, $currentAdmin));
            }
        }

        return redirect()->route('diamond.index')->with('success', 'Diamond created');
    }

    /**
     * Update the specified diamond in storage.
     */
    public function update(UpdateDiamondRequest $request, Diamond $diamond)
    {
        $validated = $request->validated();
        $currentAdmin = auth('admin')->user();

        // If stockid changed, regenerate SKU and barcode
        if ($validated['stockid'] != $diamond->stockid) {
            $sku = $validated['sku'];

            $year = date('y');
            $brandCode = '100';
            $paddedStockid = str_pad($validated['stockid'], 6, '0', STR_PAD_LEFT);
            $newBarcodeNumber = $year . $brandCode . $paddedStockid;
            
            // Create barcode with only SKU
            $barcodeData = $sku;
            
            // Generate new barcode using Picqer with only SKU
            $generator = new BarcodeGeneratorSVG();
            $svgContent = $generator->getBarcode($barcodeData, $generator::TYPE_CODE_128);
            
            // Convert SVG to data URI for inline display
            $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svgContent);
            
            $barcodeFilename = 'barcode_' . $newBarcodeNumber . '.svg';
            $publicDir = public_path('barcodes');
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }
            
            // Save barcode SVG for reference (optional)
            $barcodeFullPath = $publicDir . DIRECTORY_SEPARATOR . $barcodeFilename;
            file_put_contents($barcodeFullPath, $svgContent);

            // remove old barcode image if exists
            if ($diamond->barcode_number) {
                $oldSvgFile = $publicDir . DIRECTORY_SEPARATOR . 'barcode_' . $diamond->barcode_number . '.svg';
                $oldPngFile = $publicDir . DIRECTORY_SEPARATOR . 'barcode_' . $diamond->barcode_number . '.png';
                if (file_exists($oldSvgFile)) {
                    @unlink($oldSvgFile);
                }
                if (file_exists($oldPngFile)) {
                    @unlink($oldPngFile);
                }
            }

            $diamond->barcode_number = $newBarcodeNumber;
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

        // listing price default
        $listingPrice = $validated['listing_price'] ?? ($validated['price'] * 1.25);

        $diamond->stockid = $validated['stockid'];
        $diamond->price = $validated['price'];
        $diamond->listing_price = $listingPrice;
        $diamond->cut = $validated['cut'] ?? null;
        $diamond->shape = $validated['shape'] ?? null;
        $diamond->measurement = $validated['measurement'] ?? null;
        $diamond->number_of_pics = $validated['number_of_pics'] ?? 0;
        $diamond->description = $validated['description'] ?? null;
        $diamond->note = $validated['note'] ?? null;
        $diamond->diamond_type = $validated['diamond_type'] ?? null;

        $diamond->save();

        if ($request->wantsJson()) {
            return response()->json($diamond);
        }

        return redirect()->route('diamond.index')->with('success', 'Diamond updated');
    }

    /**
     * Remove the specified diamond from storage.
     */
    public function destroy(Diamond $diamond)
    {
        // delete barcode image if present
        if ($diamond->barcode_number) {
            // Try both PNG and SVG
            $pngFile = public_path('barcodes') . DIRECTORY_SEPARATOR . 'barcode_' . $diamond->barcode_number . '.png';
            $svgFile = public_path('barcodes') . DIRECTORY_SEPARATOR . 'barcode_' . $diamond->barcode_number . '.svg';
            if (file_exists($pngFile)) {
                @unlink($pngFile);
            }
            if (file_exists($svgFile)) {
                @unlink($svgFile);
            }
        }

        $diamond->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Deleted']);
        }

        return redirect()->route('diamond.index')->with('success', 'Diamond deleted');
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

        // Send reassignment notification to previous admin if exists
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
}
