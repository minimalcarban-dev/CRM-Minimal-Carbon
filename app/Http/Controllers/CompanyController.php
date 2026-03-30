<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyMonthlyTarget;
use App\Models\Order;
use App\Services\CompanySalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;
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
            'currency' => 'nullable|string|in:' . implode(',', array_keys(config('currencies', []))),
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
            'currency' => 'nullable|string|in:' . implode(',', array_keys(config('currencies', []))),
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
    // public function salesDashboard($id, Request $request, CompanySalesReportService $service)
    // {
    //     // Permission check - explicit permission required (no super admin bypass)
    //     $admin = Auth::guard('admin')->user();
    //     if (!$admin->hasExplicitPermission('sales.view')) {
    //         abort(403, 'You do not have permission to view sales dashboards.');
    //     }

    //     $company = Company::findOrFail($id);
    //     $now = Carbon::now();
    //     $year = $request->input('year', $now->year);
    //     $month = $request->input('month', $now->month);

    //     // Get periods for filter - default is current month
    //     $dateFrom = $request->input('date_from', $now->copy()->startOfMonth()->toDateString());
    //     $dateTo = $request->input('date_to', $now->toDateString());

    //     // ===== CALCULATE STATS FROM ORDERS TABLE DIRECTLY =====
    //     // Get ALL orders for this company (status does not matter - only created_at matters)
    //     $allOrders = Order::where('company_id', $company->id)->get();

    //     // Today's sales - orders created today
    //     $todaysOrders = $allOrders->filter(function ($order) {
    //         $today = Carbon::today();
    //         $createdDate = Carbon::parse($order->created_at)->startOfDay();
    //         return $createdDate->eq($today);
    //     });
    //     $todaysSales = $todaysOrders->sum('gross_sell');
    //     $todaysOrderCount = $todaysOrders->count();

    //     // Current month stats (month-to-date) - based on created_at
    //     $startOfMonth = $now->copy()->startOfMonth();
    //     $monthOrders = $allOrders->filter(function ($order) use ($startOfMonth, $now) {
    //         $createdDate = Carbon::parse($order->created_at);
    //         return $createdDate->between($startOfMonth, $now);
    //     });
    //     $monthToDate = [
    //         'order_count' => $monthOrders->count(),
    //         'total_revenue' => $monthOrders->sum('gross_sell'),
    //     ];

    //     // Target calculations
    //     $currentTarget = $company->current_month_target;
    //     $monthTotal = $monthToDate['total_revenue'];
    //     $targetProgress = $currentTarget > 0 ? min(100, round(($monthTotal / $currentTarget) * 100, 1)) : null;
    //     $projectedTotal = $service->getProjectedMonthEndFromOrders($company->id, $monthTotal, $now);

    //     // ===== FILTERED DATE RANGE STATS (for Sales History section) =====
    //     $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
    //     $dateToCarbon = Carbon::parse($dateTo)->endOfDay();

    //     $filteredOrders = $allOrders->filter(function ($order) use ($dateFromCarbon, $dateToCarbon) {
    //         $createdDate = Carbon::parse($order->created_at);
    //         return $createdDate->between($dateFromCarbon, $dateToCarbon);
    //     });

    //     // Group filtered orders by date for the history table
    //     $dailyHistoryWithTotals = $filteredOrders->groupBy(function ($order) {
    //         return Carbon::parse($order->created_at)->format('Y-m-d');
    //     })->map(function ($dayOrders, $date) use ($company) {
    //         $orderTypeBreakdown = $dayOrders->groupBy('order_type')->map->count()->toArray();
    //         return (object) [
    //             'sales_date' => Carbon::parse($date),
    //             'order_count' => $dayOrders->count(),
    //             'total_revenue' => $dayOrders->sum('gross_sell'),
    //             'order_type_breakdown' => $orderTypeBreakdown,
    //             'running_total' => 0,
    //             'target_percent' => null,
    //         ];
    //     })->sortByDesc('sales_date')->values();

    //     // Calculate running totals
    //     $runningTotal = 0;
    //     $dailyHistoryWithTotals = $dailyHistoryWithTotals->reverse()->map(function ($day) use (&$runningTotal, $currentTarget) {
    //         $runningTotal += $day->total_revenue;
    //         $day->running_total = $runningTotal;
    //         $day->target_percent = $currentTarget > 0 ? round(($runningTotal / $currentTarget) * 100, 1) : null;
    //         return $day;
    //     })->reverse()->values();

    //     // Monthly chart data - calculate from orders table
    //     $monthlySummary = $this->getMonthlySummaryFromOrders($company->id, $year);

    //     return view('companies.sales-dashboard', compact(
    //         'company',
    //         'todaysSales',
    //         'todaysOrderCount',
    //         'monthToDate',
    //         'currentTarget',
    //         'targetProgress',
    //         'projectedTotal',
    //         'monthlySummary',
    //         'dailyHistoryWithTotals',
    //         'year',
    //         'month',
    //         'dateFrom',
    //         'dateTo'
    //     ));
    // }

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

        $defaultDateFrom = $now->copy()->startOfMonth()->toDateString();
        $defaultDateTo = $now->toDateString();

        if ($request->filled('year') && !$request->filled('date_from') && $year != $now->year) {
            $defaultDateFrom = Carbon::create($year, 1, 1)->toDateString();
            $defaultDateTo = Carbon::create($year, 12, 31)->toDateString();
        }

        // Get periods for filter
        $dateFrom = $request->input('date_from', $defaultDateFrom);
        $dateTo = $request->input('date_to', $defaultDateTo);

        if ($request->filled('date_from')) {
            $year = Carbon::parse($dateFrom)->year;
        }

        $month = Carbon::parse($dateFrom)->month;
        $isCurrentMonthFilter = ($dateFrom == $now->copy()->startOfMonth()->toDateString() && $dateTo == $now->toDateString());
        $isEntireYearFilter = ($dateFrom == Carbon::create($year, 1, 1)->toDateString() && $dateTo == Carbon::create($year, 12, 31)->toDateString());

        // ===== CALCULATE STATS FROM ORDERS TABLE DIRECTLY =====
        // Get ALL orders for this company (status does not matter - only created_at matters, except cancelled orders which do matter)
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        $allOrders = Order::where('company_id', $company->id)
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereNotIn('diamond_status', $cancelledStatuses)->orWhereNull('diamond_status');
            })
            ->get();

        // Today's sales - orders created today
        $todaysOrders = $allOrders->filter(function ($order) {
            $today = Carbon::today();
            $createdDate = Carbon::parse($order->created_at)->startOfDay();
            return $createdDate->eq($today);
        });
        $todaysSales = $todaysOrders->sum('gross_sell');
        $todaysOrderCount = $todaysOrders->count();

        // Current month stats (month-to-date) - based on created_at
        $startOfMonth = $now->copy()->startOfMonth();
        $monthOrders = $allOrders->filter(function ($order) use ($startOfMonth, $now) {
            $createdDate = Carbon::parse($order->created_at);
            return $createdDate->between($startOfMonth, $now);
        });
        $monthToDate = [
            'order_count' => $monthOrders->count(),
            'total_revenue' => $monthOrders->sum('gross_sell'),
        ];

        // ===== FILTERED DATE RANGE STATS (for Sales History section AND top stats) =====
        $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
        $dateToCarbon = Carbon::parse($dateTo)->endOfDay();

        $filteredOrders = $allOrders->filter(function ($order) use ($dateFromCarbon, $dateToCarbon) {
            $createdDate = Carbon::parse($order->created_at);
            return $createdDate->between($dateFromCarbon, $dateToCarbon);
        });

        // Calculate stats for FILTERED date range
        $filteredTotal = $filteredOrders->sum('gross_sell');
        $filteredOrderCount = $filteredOrders->count();
        $avgOrderValue = $filteredOrderCount > 0 ? round($filteredTotal / $filteredOrderCount, 2) : 0;

        // Target calculations
        $startMonth = Carbon::parse($dateFrom)->month;
        $endMonth = Carbon::parse($dateTo)->month;

        $currentTarget = CompanyMonthlyTarget::where('company_id', $company->id)
            ->where('year', $year)
            ->whereBetween('month', [$startMonth, $endMonth])
            ->sum('target_amount');

        $targetProgress = $currentTarget > 0 ? min(100, round(($filteredTotal / $currentTarget) * 100, 1)) : null;
        $targetGap = $currentTarget - $filteredTotal;

        // Projected total - only show for current month filter
        $projectedTotal = $isCurrentMonthFilter ? $service->getProjectedMonthEndFromOrders($company->id, $monthToDate['total_revenue'], $now) : null;

        // Group filtered orders by date for the history table
        $dailyHistoryWithTotals = $filteredOrders->groupBy(function ($order) {
            return Carbon::parse($order->created_at)->format('Y-m-d');
        })->map(function ($dayOrders, $date) use ($company) {
            $orderTypeBreakdown = $dayOrders->groupBy('order_type')->map(fn($group) => $group->count())->toArray();
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
        $monthlySummary = $this->getMonthlySummaryFromOrders($company->id, $year);

        return view('companies.sales-dashboard', compact(
            'company',
            'todaysSales',
            'todaysOrderCount',
            'monthToDate',
            'currentTarget',
            'targetProgress',
            'targetGap',
            'projectedTotal',
            'avgOrderValue',
            'filteredTotal',
            'filteredOrderCount',
            'monthlySummary',
            'dailyHistoryWithTotals',
            'year',
            'month',
            'dateFrom',
            'dateTo',
            'isCurrentMonthFilter',
            'isEntireYearFilter'
        ));
    }

    /**
     * Calculate monthly summary directly from orders table.
     */
    private function getMonthlySummaryFromOrders(int $companyId, int $year): array
    {
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        $orders = Order::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereNotIn('diamond_status', $cancelledStatuses)
                    ->orWhereNull('diamond_status');
            })
            ->get();

        $targets = CompanyMonthlyTarget::where('company_id', $companyId)
            ->where('year', $year)
            ->pluck('target_amount', 'month')
            ->toArray();

        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthOrders = $orders->filter(function ($order) use ($month, $year) {
                $createdDate = Carbon::parse($order->created_at);
                return $createdDate->month == $month && $createdDate->year == $year;
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

    /**
     * Show sales dashboard aggregating all companies.
     */
    public function allSalesDashboard(Request $request, CompanySalesReportService $service)
    {
        // Permission check
        $admin = Auth::guard('admin')->user();
        if (!$admin->hasExplicitPermission('sales.view_all')) {
            abort(403, 'You do not have permission to view all sales dashboards.');
        }

        $now = Carbon::now();
        $year = $request->input('year', $now->year);

        $defaultDateFrom = $now->copy()->startOfMonth()->toDateString();
        $defaultDateTo = $now->toDateString();

        if ($request->filled('year') && !$request->filled('date_from') && $year != $now->year) {
            $defaultDateFrom = Carbon::create($year, 1, 1)->toDateString();
            $defaultDateTo = Carbon::create($year, 12, 31)->toDateString();
        }

        // Get periods for filter
        $dateFrom = $request->input('date_from', $defaultDateFrom);
        $dateTo = $request->input('date_to', $defaultDateTo);

        if ($request->filled('date_from')) {
            $year = Carbon::parse($dateFrom)->year;
        }

        $month = Carbon::parse($dateFrom)->month;
        $isCurrentMonthFilter = ($dateFrom == $now->copy()->startOfMonth()->toDateString() && $dateTo == $now->toDateString());
        $isEntireYearFilter = ($dateFrom == Carbon::create($year, 1, 1)->toDateString() && $dateTo == Carbon::create($year, 12, 31)->toDateString());

        // ===== CALCULATE STATS FROM ORDERS TABLE DIRECTLY =====
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        // Get ALL orders across all companies
        $allOrders = Order::where(function ($q) use ($cancelledStatuses) {
            $q->whereNotIn('diamond_status', $cancelledStatuses)->orWhereNull('diamond_status');
        })
            ->get();

        // Today's sales - orders created today
        $todaysOrders = $allOrders->filter(function ($order) {
            $today = Carbon::today();
            $createdDate = Carbon::parse($order->created_at)->startOfDay();
            return $createdDate->eq($today);
        });
        $todaysSales = $todaysOrders->sum('gross_sell');
        $todaysOrderCount = $todaysOrders->count();

        // Current month stats (month-to-date) - based on created_at
        $startOfMonth = $now->copy()->startOfMonth();
        $monthOrders = $allOrders->filter(function ($order) use ($startOfMonth, $now) {
            $createdDate = Carbon::parse($order->created_at);
            return $createdDate->between($startOfMonth, $now);
        });
        $monthToDate = [
            'order_count' => $monthOrders->count(),
            'total_revenue' => $monthOrders->sum('gross_sell'),
        ];

        // ===== FILTERED DATE RANGE STATS (for Sales History section AND top stats) =====
        $dateFromCarbon = Carbon::parse($dateFrom)->startOfDay();
        $dateToCarbon = Carbon::parse($dateTo)->endOfDay();

        $filteredOrders = $allOrders->filter(function ($order) use ($dateFromCarbon, $dateToCarbon) {
            $createdDate = Carbon::parse($order->created_at);
            return $createdDate->between($dateFromCarbon, $dateToCarbon);
        });

        // Calculate stats for FILTERED date range
        $filteredTotal = $filteredOrders->sum('gross_sell');
        $filteredOrderCount = $filteredOrders->count();
        $avgOrderValue = $filteredOrderCount > 0 ? round($filteredTotal / $filteredOrderCount, 2) : 0;

        // Target calculations - Aggregate targets across all companies
        $startMonth = Carbon::parse($dateFrom)->month;
        $endMonth = Carbon::parse($dateTo)->month;

        $combinedTarget = CompanyMonthlyTarget::where('year', $year)
            ->whereBetween('month', [$startMonth, $endMonth])
            ->sum('target_amount');

        $globalTargetRecord = GlobalMonthlyTarget::where('year', $year)
            ->whereBetween('month', [$startMonth, $endMonth])
            ->sum('target_amount');

        $globalTarget = $globalTargetRecord; // Sum returns value directly

        // Use global target if it's set (> 0), otherwise fallback to combined
        $currentTarget = $globalTarget > 0 ? $globalTarget : $combinedTarget;

        $targetProgress = $currentTarget > 0 ? min(100, round(($filteredTotal / $currentTarget) * 100, 1)) : null;
        $targetGap = $currentTarget - $filteredTotal;

        // Projected total - only show for current month filter
        $projectedTotal = null;
        if ($isCurrentMonthFilter) {
            $daysInMonth = $now->daysInMonth;
            $currentDay = $now->day;
            // Add a small fraction to avoid division by zero or unrealistic inflation right at midnight
            $progressRatio = max(1, $currentDay) / $daysInMonth;
            $projectedTotal = round($monthToDate['total_revenue'] / $progressRatio, 2);
        }

        // Prepare Company-Wise stats instead of daily
        $colors = [
            '#6366f1',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#3b82f6',
            '#8b5cf6',
            '#ec4899',
            '#14b8a6',
            '#f97316',
            '#06b6d4',
            '#64748b',
            '#84cc16',
            '#eab308',
            '#d946ef',
            '#1e293b'
        ];

        $companyWiseStats = $filteredOrders->groupBy('company_id')->map(function ($orders, $companyId) use ($filteredTotal, $colors) {
            $company = Company::find($companyId);
            $revenue = $orders->sum('gross_sell');
            $percentage = $filteredTotal > 0 ? round(($revenue / $filteredTotal) * 100, 1) : 0;
            $colorIndex = $companyId % count($colors);
            return (object) [
                'company_id' => $companyId,
                'company_name' => $company ? $company->name : 'Unknown',
                'order_count' => $orders->count(),
                'total_revenue' => $revenue,
                'percentage' => $percentage,
                'color' => $colors[$colorIndex]
            ];
        })->sortByDesc('total_revenue')->values();

        // Monthly chart data - calculate from orders table (aggregated)
        $monthlySummary = $this->getAllMonthlySummaryFromOrders($year);

        // Fetch all active companies and their targets for the targets modal
        $allCompanies = Company::all();
        $companyTargets = CompanyMonthlyTarget::where('year', $now->year)
            ->where('month', $now->month)
            ->get()
            ->keyBy('company_id');

        return view('companies.all-sales-dashboard', compact(
            'todaysSales',
            'todaysOrderCount',
            'monthToDate',
            'currentTarget',
            'globalTarget',
            'combinedTarget',
            'allCompanies',
            'companyTargets',
            'targetProgress',
            'targetGap',
            'projectedTotal',
            'avgOrderValue',
            'filteredTotal',
            'filteredOrderCount',
            'monthlySummary',
            'companyWiseStats',
            'year',
            'month',
            'dateFrom',
            'dateTo',
            'isCurrentMonthFilter',
            'isEntireYearFilter'
        ));
    }

    /**
     * Calculate monthly summary across ALL companies directly from orders table.
     */
    private function getAllMonthlySummaryFromOrders(int $year): array
    {
        $cancelledStatuses = ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled'];
        $orders = Order::whereYear('created_at', $year)
            ->where(function ($q) use ($cancelledStatuses) {
                $q->whereNotIn('diamond_status', $cancelledStatuses)
                    ->orWhereNull('diamond_status');
            })
            ->get();

        // Aggregate targets across all companies per month
        $targets = CompanyMonthlyTarget::where('year', $year)
            ->selectRaw('month, sum(target_amount) as total_target')
            ->groupBy('month')
            ->pluck('total_target', 'month')
            ->toArray();

        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthOrders = $orders->filter(function ($order) use ($month, $year) {
                $createdDate = Carbon::parse($order->created_at);
                return $createdDate->month == $month && $createdDate->year == $year;
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
     * Export ALL sales data as CSV.
     */
    public function exportAllSalesCsv(Request $request, CompanySalesReportService $service)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin->hasExplicitPermission('sales.view_all')) {
            abort(403, 'You do not have permission to view all sales data.');
        }

        $year = $request->input('year', Carbon::now()->year);

        // Reusing the aggregated monthly summary logic
        $monthlySummary = $this->getAllMonthlySummaryFromOrders($year);

        $filename = "all-company-sales-data-{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($monthlySummary, $year) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ["All Company Sales Report - {$year}"]);
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

    /**
     * Set global target and individual company targets from the all sales dashboard.
     */
    public function saveAllTargets(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin->hasExplicitPermission('sales.view_all')) {
            abort(403, 'You do not have permission to manage all targets.');
        }

        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'global_target' => 'nullable|numeric|min:0',
            'company_targets' => 'nullable|array',
            'company_targets.*' => 'nullable|numeric|min:0',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');
        $globalAmount = $request->input('global_target');

        // Save Global Target
        \App\Models\GlobalMonthlyTarget::setTarget($year, $month, (float) ($globalAmount ?: 0));

        // Save Individual Company Targets
        $companyTargets = $request->input('company_targets', []);
        foreach ($companyTargets as $companyId => $amount) {
            if ($amount !== null && $amount !== '') {
                CompanyMonthlyTarget::setTarget($companyId, $year, $month, (float) $amount);
            }
        }

        return redirect()->back()->with('success', 'Targets updated successfully.');
    }
}
