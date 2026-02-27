<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Package;
use App\Models\Diamond;
use App\Http\Requests\PackageRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Cloudinary\Cloudinary;
use App\Notifications\PackageIssuedNotification;

class PackageController extends Controller
{
    private $cloudinary;

    public function __construct()
    {
        // Use same direct Cloudinary SDK pattern as OrderController.
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

    public function index(Request $request)
    {
        $query = Package::with('admin')->latest();

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function ($q) use ($request) {
                $q->where('person_name', 'like', '%' . $request->search . '%')
                    ->orWhere('mobile_number', 'like', '%' . $request->search . '%')
                    ->orWhere('slip_id', 'like', '%' . $request->search . '%');
            });
        }

        $packages = $query->paginate(10);

        $stats = [
            'total' => Package::count(),
            'issued' => Package::pending()->count(),
            'returned' => Package::returned()->count(),
            'overdue' => Package::overdue()->count(),
        ];

        return view('packages.index', compact('packages', 'stats'));
    }

    public function create()
    {
        return view('packages.create');
    }

    public function store(PackageRequest $request)
    {
        $validated = $request->validated();

        // Normalize custom slip id entered by user.
        $validated['slip_id'] = strtoupper(trim($validated['slip_id']));

        // Normalize stock id and auto-populate diamond snapshot details.
        if (!empty($validated['stock_id'])) {
            $validated['stock_id'] = strtoupper(trim($validated['stock_id']));

            $diamond = Diamond::where('sku', $validated['stock_id'])->first();
            if ($diamond) {
                $validated['diamond_shape'] = $diamond->shape;
                $validated['diamond_size'] = $diamond->measurement;
                $validated['diamond_color'] = $diamond->color;
                $validated['diamond_clarity'] = $diamond->clarity;
                $validated['diamond_carat'] = $diamond->weight;
            }
        }

        // Handle image upload to Cloudinary (same flow as orders).
        if ($request->hasFile('package_image')) {
            try {
                $result = $this->cloudinary->uploadApi()->upload(
                    $request->file('package_image')->getRealPath(),
                    [
                        'folder' => 'packages',
                        'resource_type' => 'image',
                    ]
                );
                $validated['package_image'] = $result['secure_url'] ?? null;
            } catch (\Throwable $e) {
                Log::error('Cloudinary package upload failed.', [
                    'message' => $e->getMessage(),
                ]);
                return back()
                    ->withInput()
                    ->with('error', 'Image upload failed. Please try again.');
            }
        }

        $validated['status'] = 'Issued';
        $validated['admin_id'] = Auth::guard('admin')->user()->id;

        $package = Package::create($validated);

        // Send Notification to the Admin who issued it (confirmation)
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            $admin->notify(new PackageIssuedNotification($package));
        }

        return redirect()->route('packages.show', $package->id)
            ->with('success', 'Package issued successfully!');
    }

    public function lookupStock(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|string|max:100',
        ]);

        $stockId = strtoupper(trim((string) $request->stock_id));
        $diamond = Diamond::where('sku', $stockId)->first();

        if (!$diamond) {
            return response()->json([
                'success' => false,
                'message' => 'Stock ID not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stock_id' => $diamond->sku,
                'shape' => $diamond->shape,
                'size' => $diamond->measurement,
                'color' => $diamond->color,
                'clarity' => $diamond->clarity,
                'carat' => $diamond->weight,
            ],
        ]);
    }

    public function show(Package $package)
    {
        return view('packages.show', compact('package'));
    }

    public function returnPackage(Request $request, Package $package)
    {
        if ($package->status === 'Returned') {
            return redirect()->back()->with('error', 'Package is already returned.');
        }

        $package->update([
            'status' => 'Returned',
            'actual_return_date' => now(),
            'actual_return_time' => now()->format('H:i'),
        ]);

        return redirect()->back()->with('success', 'Package marked as returned!');
    }

    public function destroy(Package $package)
    {
        $package->delete(); // Soft delete
        return redirect()->route('packages.index')
            ->with('success', 'Package record deleted.');
    }
}
