<?php

namespace App\Http\Controllers;

use App\Models\ClosureType;
use App\Models\Company;
use App\Models\MetalType;
use App\Models\Order;
use App\Models\RingSize;
use App\Models\SettingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
// use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with filters.
     */
    public function index(Request $request)
    {
        $query = Order::query()->with(['company', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_details', 'like', "%$search%")
                    ->orWhere('jewellery_details', 'like', "%$search%")
                    ->orWhere('diamond_details', 'like', "%$search%")
                    ->orWhereHas('company', fn($c) => $c->where('name', 'like', "%$search%"));
            });
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('diamond_status')) {
            $query->where('diamond_status', $request->diamond_status);
        }
        $orders = $query->latest()->paginate(10);
        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        return view('orders.create');
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateOrder($request);

        // ✅ Handle file uploads
        $images = $this->handleFileUploads($request, 'images', 'uploads/orders/images', 10);
        $pdfs = $this->handleFileUploads($request, 'order_pdfs', 'uploads/orders/pdfs', 5, true);

        // ✅ Create and save the order
        $order = new Order();
        $this->assignOrderFields($order, $validated);
        $order->images = json_encode($images);
        $order->order_pdfs = json_encode($pdfs);
        $order->submitted_by = Auth::guard('admin')->id();

        $order->save();

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    /**
     * Show the form for editing an order.
     */
    public function edit(Order $order)
    {
        return view('orders.edit', compact('order'));
    }

    /**
     * Update an existing order in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $this->validateOrder($request);

        // ✅ Handle new file uploads
        $newImages = $this->handleFileUploads($request, 'images', 'uploads/orders/images', 10);
        $newPdfs = $this->handleFileUploads($request, 'order_pdfs', 'uploads/orders/pdfs', 5, true);

        // ✅ Decode existing JSON data safely
        $existingImages = json_decode($order->images ?? '[]', true);
        $existingPdfs = json_decode($order->order_pdfs ?? '[]', true);

        // ✅ Merge old + new files
        $order->images = json_encode(array_merge($existingImages, $newImages));
        $order->order_pdfs = json_encode(array_merge($existingPdfs, $newPdfs));

        // ✅ Update other fields
        $this->assignOrderFields($order, $validated);
        $order->submitted_by = Auth::guard('admin')->id();

        $order->save();

        return redirect()->route('orders.index')->with('success', 'Order updated successfully.');
    }


    /* 
    * Show the Order details.
    */
    public function show(Order $order)
    {
        // Fetch related dropdown data if your show.blade.php uses them
        $metalTypes = MetalType::all();
        $ringSizes = RingSize::all();
        $settingTypes = SettingType::all();
        $closureTypes = ClosureType::all();
        $companies = Company::all();

        // Pass data to the show view
        return view('orders.show', compact(
            'order',
            'metalTypes',
            'ringSizes',
            'settingTypes',
            'closureTypes',
            'companies'
        ));
    }


    /**
     * Delete an order and its attached files.
     */
    public function destroy(Order $order)
    {
        // Delete attached images
        $images = is_string($order->images) ? json_decode($order->images, true) : ($order->images ?? []);
        foreach ($images as $image) {
            Storage::delete(str_replace('storage/', 'public/', $image));
        }

        // Delete attached PDFs
        $pdfs = is_string($order->order_pdfs) ? json_decode($order->order_pdfs, true) : ($order->order_pdfs ?? []);
        foreach ($pdfs as $pdf) {
            Storage::delete(str_replace('storage/', 'public/', $pdf));
        }

        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }

    /**
     * Validate form input for all order types.
     */
    /**
     * Validate form input for all order types.
     *
     * @param Request $request
     * @return array The validated data
     */
    private function validateOrder(Request $request): array
    {
        $rules = [
            'order_type' => 'required|in:ready_to_ship,custom_diamond,custom_jewellery',
            'client_details' => 'required|string',
            'diamond_status' => 'nullable|string|in:processed,completed',
            'company_id' => 'required|exists:companies,id',
            'gross_sell' => 'nullable|numeric|min:0',
            'dispatch_date' => 'nullable|date',
            'note' => 'nullable|in:priority,non_priority',
            'shipping_company_name' => 'nullable|string',
            'tracking_number' => 'nullable|string',
            'tracking_url' => 'nullable|url',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'order_pdfs.*' => 'nullable|mimes:pdf|max:10240',
        ];

        switch ($request->order_type) {
            case 'ready_to_ship':
                $rules += [
                    'jewellery_details' => 'nullable|string',
                    'diamond_details' => 'nullable|string',
                    'gold_detail_id' => 'nullable|exists:metal_types,id',
                    'ring_size_id' => 'nullable|exists:ring_sizes,id',
                    'setting_type_id' => 'nullable|exists:setting_types,id',
                    'earring_type_id' => 'nullable|exists:closure_types,id',
                ];
                break;

            case 'custom_diamond':
                $rules += [
                    'diamond_details' => 'required|string',
                ];
                break;

            case 'custom_jewellery':
                $rules += [
                    'jewellery_details' => 'required|string',
                    'diamond_details' => 'nullable|string',
                    'gold_detail_id' => 'nullable|exists:metal_types,id',
                    'ring_size_id' => 'nullable|exists:ring_sizes,id',
                    'setting_type_id' => 'nullable|exists:setting_types,id',
                    'earring_type_id' => 'nullable|exists:closure_types,id',
                ];
                break;
        }

        return $request->validate($rules);
    }

    /**
     * Assign common validated fields to Order model.
     */
    /**
     * Assign common validated fields to Order model.
     *
     * @param Order $order
     * @param array $validated
     * @return void
     */
    private function assignOrderFields(Order $order, array $validated): void
    {
        $order->order_type = $validated['order_type'];
        $order->client_details = $validated['client_details'];
        $order->jewellery_details = $validated['jewellery_details'] ?? null;
        $order->diamond_details = $validated['diamond_details'] ?? null;
        $order->gold_detail_id = $validated['gold_detail_id'] ?? null;
        $order->ring_size_id = $validated['ring_size_id'] ?? null;
        $order->setting_type_id = $validated['setting_type_id'] ?? null;
        $order->earring_type_id = $validated['earring_type_id'] ?? null;
        $order->diamond_status = $validated['diamond_status'] ?? null;
        $order->gross_sell = $validated['gross_sell'] ?? 0;
        $order->company_id = $validated['company_id'];
        $order->note = $validated['note'] ?? null;
        $order->shipping_company_name = $validated['shipping_company_name'] ?? null;
        $order->tracking_number = $validated['tracking_number'] ?? null;
        $order->tracking_url = $validated['tracking_url'] ?? null;
        $order->dispatch_date = $validated['dispatch_date'] ?? null;
    }

    /**
     * Handle file uploads (images or PDFs).
     */
    /**
     * Handle file uploads (images or PDFs).
     *
     * @return array List of stored file paths
     */
    private function handleFileUploads(Request $request, string $field, string $path, int $maxFiles, bool $isPdf = false): array
    {
        $files = [];

        if ($request->hasFile($field)) {
            foreach ($request->file($field) as $index => $file) {
                if ($index >= $maxFiles) break;

                // ✅ Get original and unique names
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $uuidName = Str::uuid() . '.' . $extension;

                // ✅ Store file
                $storedPath = $file->storeAs($path, $uuidName, 'public');

                // ✅ Compress if it's a large PDF
                if ($isPdf && $file->getSize() > 10 * 1024 * 1024) {
                    $this->compressPdf(storage_path('app/public/' . $storedPath));
                }

                // ✅ Save both path + original name
                $files[] = [
                    'path' => 'storage/' . $storedPath,
                    'name' => $originalName . '.' . $extension,
                ];
            }
        }

        return $files;
    }

    /**
     * Compress PDF files larger than 10MB using Ghostscript.
     */
    private function compressPdf($filePath): void
    {
        try {
            $tempPath = str_replace('.pdf', '_compressed.pdf', $filePath);
            $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile={$tempPath} {$filePath}";
            exec($cmd);
            if (file_exists($tempPath)) {
                rename($tempPath, $filePath);
            }
        } catch (\Exception $e) {
            // Ignore compression errors
        }
    }

    /**
     * Dynamically load form partial based on order type.
     */
    public function loadFormPartial($type, Request $request)
    {
        // Determine which partial view to load
        $view = match ($type) {
            'ready_to_ship' => 'orders.partials.ready_to_ship',
            'custom_diamond' => 'orders.partials.custom_diamond',
            'custom_jewellery' => 'orders.partials.custom_jewellery',
            default => null,
        };

        if (!$view || !view()->exists($view)) {
            return response('<div class="alert alert-danger">Invalid form type selected.</div>', 404);
        }

        // Load order for edit mode if applicable
        $order = null;
        if ($request->has('edit') && $request->edit === 'true' && $request->has('id')) {
            $order = Order::find($request->id);
        }

        // ✅ Fetch dropdown data required by the Ready-to-Ship form
        $companies = Company::all();
        $metalTypes = MetalType::all();
        $ringSizes = RingSize::all();
        $settingTypes = SettingType::all();
        $closureTypes = ClosureType::all();

        // ✅ Return the partial view with all required data
        return view($view, compact(
            'order',
            'companies',
            'metalTypes',
            'ringSizes',
            'settingTypes',
            'closureTypes'
        ))->render();
    }
}
