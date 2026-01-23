<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyMonthlyTarget;
use App\Services\CompanySalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Company Resource Controller with custom search filters
 */
class CompanyController extends BaseResourceController
{
    private $cloudinary = null;

    /**
     * Get Cloudinary instance (lazy initialization)
     */
    private function getCloudinary(): Cloudinary
    {
        if ($this->cloudinary === null) {
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
        return $this->cloudinary;
    }

    protected function getModelClass(): string
    {
        return Company::class;
    }

    protected function getViewPath(): string
    {
        return 'companies';
    }

    protected function getRouteName(): string
    {
        return 'companies';
    }

    protected function getPermissionPrefix(): ?string
    {
        return null; // No permission checks
    }

    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:companies,name',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gst_no' => 'nullable|string|max:50',
            'state_code' => 'nullable|string|max:50',
            'ein_cin_no' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:50',
            'ad_code' => 'nullable|string|max:50',
            'sort_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'account_holder_name' => 'nullable|string|max:255',
            // US Bank Details
            'beneficiary_name' => 'nullable|string|max:255',
            'aba_routing_number' => 'nullable|string|max:9',
            'us_account_no' => 'nullable|string|max:50',
            'account_type' => 'nullable|in:checking,savings',
            'beneficiary_address' => 'nullable|string|max:500',
            'currency' => 'nullable|string|in:USD,GBP,INR,EUR',
            'status' => 'required|in:active,inactive',
        ];
    }

    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:companies,name,' . $id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gst_no' => 'nullable|string|max:50',
            'state_code' => 'nullable|string|max:50',
            'ein_cin_no' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:50',
            'ad_code' => 'nullable|string|max:50',
            'sort_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'account_holder_name' => 'nullable|string|max:255',
            // US Bank Details
            'beneficiary_name' => 'nullable|string|max:255',
            'aba_routing_number' => 'nullable|string|max:9',
            'us_account_no' => 'nullable|string|max:50',
            'account_type' => 'nullable|in:checking,savings',
            'beneficiary_address' => 'nullable|string|max:500',
            'currency' => 'nullable|string|in:USD,GBP,INR,EUR',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Handle logo file upload for store - uploads to Cloudinary
     */
    protected function prepareDataForStore(array $validated, Request $request): array
    {
        // Handle logo upload to Cloudinary
        if ($request->hasFile('logo')) {
            $logoUrl = $this->uploadLogoToCloudinary($request->file('logo'));
            if ($logoUrl) {
                $validated['logo'] = $logoUrl;
            }
        }

        return parent::prepareDataForStore($validated, $request);
    }

    /**
     * Handle logo file upload for update - uploads to Cloudinary
     */
    protected function prepareDataForUpdate(array $validated, Request $request, $item): array
    {
        // Handle logo upload to Cloudinary
        if ($request->hasFile('logo')) {
            // Delete old logo from Cloudinary if exists
            if ($item->logo && str_contains($item->logo, 'cloudinary.com')) {
                $this->deleteLogoFromCloudinary($item->logo);
            }

            // Upload new logo
            $logoUrl = $this->uploadLogoToCloudinary($request->file('logo'));
            if ($logoUrl) {
                $validated['logo'] = $logoUrl;
            }
        }

        return parent::prepareDataForUpdate($validated, $request, $item);
    }

    /**
     * Upload logo to Cloudinary
     */
    private function uploadLogoToCloudinary($file): ?string
    {
        try {
            $timestamp = time();
            $uniqueId = uniqid();
            $publicId = "companies/logos/{$timestamp}_{$uniqueId}";

            $uploadOptions = [
                'public_id' => $publicId,
                'folder' => 'companies/logos',
                'transformation' => [
                    'quality' => 'auto:good',
                    'fetch_format' => 'auto'
                ]
            ];

            Log::info("Uploading company logo to Cloudinary", [
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            $uploadApi = $this->getCloudinary()->uploadApi();
            $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);

            Log::info("Successfully uploaded company logo to Cloudinary", [
                'url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ]);

            return $result['secure_url'];

        } catch (\Exception $e) {
            Log::error('Cloudinary logo upload failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Delete logo from Cloudinary
     */
    private function deleteLogoFromCloudinary(string $url): bool
    {
        try {
            // Extract public_id from URL
            // URL format: https://res.cloudinary.com/cloud_name/image/upload/v123/companies/logos/xxx.jpg
            $pattern = '/\/upload\/(?:v\d+\/)?(.+?)(?:\.[^.]+)?$/';
            if (preg_match($pattern, $url, $matches)) {
                $publicId = $matches[1];

                $uploadApi = $this->getCloudinary()->uploadApi();
                $uploadApi->destroy($publicId, ['resource_type' => 'image']);

                Log::info('Deleted company logo from Cloudinary', ['public_id' => $publicId]);
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete company logo from Cloudinary', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
        return false;
    }

    /**
     * Override index to add custom search/filter
     */
    public function index(Request $request)
    {
        $query = Company::query();

        // Multi-field search - includes commonly searched fields
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('gst_no', 'like', "%{$search}%")
                    ->orWhere('ein_cin_no', 'like', "%{$search}%")
                    ->orWhere('account_holder_name', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        // Use 'companies' variable name for backward compatibility with views
        $companies = $items;

        return view('companies.index', compact('companies'));
    }

    /**
     * Override edit to use 'company' variable name in view
     */
    public function edit($id)
    {
        $this->checkPermission('edit');

        $company = Company::findOrFail($id);

        return view('companies.edit', compact('company'));
    }

    /**
     * Show company details as JSON for modal
     */
    public function show($id)
    {
        try {
            $company = Company::findOrFail($id);

            return response()->json([
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'logo' => $company->logo ? (str_starts_with($company->logo, 'http') ? $company->logo : asset($company->logo)) : null,
                'gst_no' => $company->gst_no,
                'ein_cin_no' => $company->ein_cin_no,
                'state_code' => $company->state_code,
                'address' => $company->address,
                'country' => $company->country,
                'bank_name' => $company->bank_name,
                'account_holder_name' => $company->account_holder_name,
                'account_no' => $company->account_no,
                'ifsc_code' => $company->ifsc_code,
                'iban' => $company->iban,
                'swift_code' => $company->swift_code,
                'sort_code' => $company->sort_code,
                'ad_code' => $company->ad_code,
                // US Bank Details
                'beneficiary_name' => $company->beneficiary_name,
                'aba_routing_number' => $company->aba_routing_number,
                'us_account_no' => $company->us_account_no,
                'account_type' => $company->account_type,
                'beneficiary_address' => $company->beneficiary_address,
                'currency' => $company->currency,
                'currency_symbol' => $company->currency_symbol,
                'status' => $company->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return response()->json(['error' => 'Company not found'], 404);
        }
    }

    // ===== SALES DASHBOARD METHODS =====

    /**
     * Show sales dashboard for a company.
     */
    public function salesDashboard($id, Request $request, CompanySalesReportService $service)
    {
        // Permission check - explicit permission required (no super admin bypass)
        $admin = Auth::guard('admin')->user();
        if (!$admin->hasExplicitPermission('sales.view')) {
            abort(403, 'You do not have permission to view sales dashboards.');
        }

        $company = Company::findOrFail($id);
        $now = Carbon::now();
        $year = $request->input('year', $now->year);
        $month = $request->input('month', $now->month);

        // Get periods for filter - default is current month
        $dateFrom = $request->input('date_from', $now->copy()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', $now->toDateString());

        $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];

        // ===== CALCULATE STATS FROM ORDERS TABLE DIRECTLY =====
        // Get ALL shipped orders for this company (for accurate total stats)
        $allShippedOrders = \App\Models\Order::where('company_id', $company->id)
            ->whereIn('diamond_status', $shippedStatuses)
            ->get();

        // Today's sales - orders created OR dispatched today
        $todaysOrders = $allShippedOrders->filter(function ($order) {
            $today = Carbon::today();
            $dispatchDate = $order->dispatch_date ? Carbon::parse($order->dispatch_date)->startOfDay() : null;
            $createdDate = Carbon::parse($order->created_at)->startOfDay();
            return ($dispatchDate && $dispatchDate->eq($today)) || (!$dispatchDate && $createdDate->eq($today));
        });
        $todaysSales = $todaysOrders->sum('gross_sell');
        $todaysOrderCount = $todaysOrders->count();

        // Current month stats (month-to-date) - based on dispatch_date or created_at
        $startOfMonth = $now->copy()->startOfMonth();
        $monthOrders = $allShippedOrders->filter(function ($order) use ($startOfMonth, $now) {
            $dispatchDate = $order->dispatch_date ? Carbon::parse($order->dispatch_date) : null;
            $createdDate = Carbon::parse($order->created_at);
            $checkDate = $dispatchDate ?? $createdDate;
            return $checkDate->between($startOfMonth, $now);
        });
        $monthToDate = [
            'order_count' => $monthOrders->count(),
            'total_revenue' => $monthOrders->sum('gross_sell'),
        ];

        // Target calculations
        $currentTarget = $company->current_month_target;
        $monthTotal = $monthToDate['total_revenue'];
        $targetProgress = $currentTarget > 0 ? min(100, round(($monthTotal / $currentTarget) * 100, 1)) : null;
        $projectedTotal = $service->getProjectedMonthEndFromOrders($company->id, $monthTotal, $now);

        // ===== FILTERED DATE RANGE STATS (for Sales History section) =====
        $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
        $dateToCarbon = Carbon::parse($dateTo)->endOfDay();

        $filteredOrders = $allShippedOrders->filter(function ($order) use ($dateFromCarbon, $dateToCarbon) {
            $dispatchDate = $order->dispatch_date ? Carbon::parse($order->dispatch_date) : null;
            $createdDate = Carbon::parse($order->created_at);
            $checkDate = $dispatchDate ?? $createdDate;
            return $checkDate->between($dateFromCarbon, $dateToCarbon);
        });

        // Group filtered orders by date for the history table
        $dailyHistoryWithTotals = $filteredOrders->groupBy(function ($order) {
            $dispatchDate = $order->dispatch_date ? Carbon::parse($order->dispatch_date)->format('Y-m-d') : null;
            return $dispatchDate ?? Carbon::parse($order->created_at)->format('Y-m-d');
        })->map(function ($dayOrders, $date) use ($company) {
            $orderTypeBreakdown = $dayOrders->groupBy('order_type')->map->count()->toArray();
            return (object) [
                'sales_date' => Carbon::parse($date),
                'order_count' => $dayOrders->count(),
                'total_revenue' => $dayOrders->sum('gross_sell'),
                'order_type_breakdown' => $orderTypeBreakdown,
                'running_total' => 0,
                'target_percent' => null,
            ];
        })->sortByDesc('sales_date')->values();

        // Calculate running totals
        $runningTotal = 0;
        $dailyHistoryWithTotals = $dailyHistoryWithTotals->reverse()->map(function ($day) use (&$runningTotal, $currentTarget) {
            $runningTotal += $day->total_revenue;
            $day->running_total = $runningTotal;
            $day->target_percent = $currentTarget > 0 ? round(($runningTotal / $currentTarget) * 100, 1) : null;
            return $day;
        })->reverse()->values();

        // Monthly chart data - calculate from orders table
        $monthlySummary = $this->getMonthlySummaryFromOrders($company->id, $year, $shippedStatuses);

        return view('companies.sales-dashboard', compact(
            'company',
            'todaysSales',
            'todaysOrderCount',
            'monthToDate',
            'currentTarget',
            'targetProgress',
            'projectedTotal',
            'monthlySummary',
            'dailyHistoryWithTotals',
            'year',
            'month',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Calculate monthly summary directly from orders table.
     */
    private function getMonthlySummaryFromOrders(int $companyId, int $year, array $shippedStatuses): array
    {
        $orders = \App\Models\Order::where('company_id', $companyId)
            ->whereIn('diamond_status', $shippedStatuses)
            ->whereYear(DB::raw('COALESCE(dispatch_date, created_at)'), $year)
            ->get();

        $targets = CompanyMonthlyTarget::where('company_id', $companyId)
            ->where('year', $year)
            ->pluck('target_amount', 'month')
            ->toArray();

        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthOrders = $orders->filter(function ($order) use ($month, $year) {
                $dispatchDate = $order->dispatch_date ? Carbon::parse($order->dispatch_date) : null;
                $createdDate = Carbon::parse($order->created_at);
                $checkDate = $dispatchDate ?? $createdDate;
                return $checkDate->month == $month && $checkDate->year == $year;
            });

            $result[$month] = [
                'month' => $month,
                'month_name' => Carbon::create()->month($month)->format('M'),
                'orders' => $monthOrders->count(),
                'revenue' => $monthOrders->sum('gross_sell'),
                'target' => (float) ($targets[$month] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Set or update monthly target for a company.
     */
    public function setTarget(Request $request, $id)
    {
        // Permission check - explicit permission required (no super admin bypass)
        $admin = Auth::guard('admin')->user();
        if (!$admin->hasExplicitPermission('sales.set_targets')) {
            abort(403, 'You do not have permission to set sales targets.');
        }

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'target_amount' => 'required|numeric|min:0',
        ]);

        $company = Company::findOrFail($id);

        CompanyMonthlyTarget::setTarget(
            $company->id,
            $validated['year'],
            $validated['month'],
            $validated['target_amount']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Target set successfully',
            ]);
        }

        return back()->with('success', 'Monthly target updated successfully!');
    }

    /**
     * Export sales report as PDF.
     */
    public function exportPdf($id, Request $request, CompanySalesReportService $service)
    {
        $company = Company::findOrFail($id);
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month');

        $monthlySummary = $service->getMonthlySummary($company->id, $year);
        $currentTarget = $company->current_month_target;

        $pdf = Pdf::loadView('companies.exports.sales-report-pdf', compact(
            'company',
            'monthlySummary',
            'currentTarget',
            'year',
            'month'
        ));

        $filename = "sales-report-{$company->name}-{$year}" . ($month ? "-{$month}" : '') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Export sales data as CSV.
     */
    public function exportCsv($id, Request $request, CompanySalesReportService $service)
    {
        $company = Company::findOrFail($id);
        $year = $request->input('year', Carbon::now()->year);

        $monthlySummary = $service->getMonthlySummary($company->id, $year);

        $filename = "sales-data-{$company->name}-{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($monthlySummary, $company, $year) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ["Sales Report - {$company->name} - {$year}"]);
            fputcsv($file, []); // Empty row
            fputcsv($file, ['Month', 'Orders', 'Revenue', 'Target', 'Achieved %']);

            foreach ($monthlySummary as $month) {
                $achieved = $month['target'] > 0
                    ? round(($month['revenue'] / $month['target']) * 100, 1) . '%'
                    : 'N/A';

                fputcsv($file, [
                    $month['month_name'],
                    $month['orders'],
                    number_format($month['revenue'], 2),
                    number_format($month['target'], 2),
                    $achieved,
                ]);
            }

            // Totals
            $totalOrders = array_sum(array_column($monthlySummary, 'orders'));
            $totalRevenue = array_sum(array_column($monthlySummary, 'revenue'));
            fputcsv($file, []);
            fputcsv($file, ['TOTAL', $totalOrders, number_format($totalRevenue, 2), '', '']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}


