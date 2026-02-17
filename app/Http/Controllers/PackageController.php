<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Package;
use App\Http\Requests\PackageRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Notifications\PackageIssuedNotification;

class PackageController extends Controller
{
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

        // Handle Image Upload to Cloudinary
        if ($request->hasFile('package_image')) {
            $uploadedFileUrl = Cloudinary::upload($request->file('package_image')->getRealPath(), [
                'folder' => 'packages'
            ])->getSecurePath();
            $validated['package_image'] = $uploadedFileUrl;
        }

        // Generate Slip ID: PKG-YYYYMMDD-Random
        $validated['slip_id'] = 'PKG-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $validated['status'] = 'Issued';
        $validated['admin_id'] = Auth::guard('admin')->user()->id;

        $package = Package::create($validated);

        // Send Notification to the Admin who issued it (confirmation)
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            $admin->notify(new PackageIssuedNotification($package));
        }

        return redirect()->route('packages.show', $package->id)
            ->with('success', 'Package issued successfully! Slip generated.');
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
