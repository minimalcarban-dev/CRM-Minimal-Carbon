<?php

namespace App\Http\Controllers;

use App\Models\Diamond;
use App\Models\Order;
use App\Models\OrderDraft;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use \Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Cancelled order statuses (mirrors OrderController).
     */
    private array $cancelledStatuses = [
        'r_order_cancelled',
        'd_order_cancelled',
        'j_order_cancelled',
    ];

    private array $shippedStatuses = [
        'r_order_shipped',
        'd_order_shipped',
        'j_order_shipped',
        'Delivered',
    ];

    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $today = now()->toDateString();
        $month = now()->month;
        $year = now()->year;

        // ── Date range from filter ────────────────────────────────
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');
        $hasRange = $dateFrom && $dateTo;
        $rangeFrom = $hasRange ? $dateFrom->startOfDay() : null;
        $rangeTo = $hasRange ? $dateTo->endOfDay() : null;

        // ── 1. ORDERS & REVENUE ───────────────────────────────────
        if ($hasRange) {
            // Custom range — bypass cache
            $orderQBase = Order::whereNotIn('diamond_status', $this->cancelledStatuses)
                ->whereBetween('created_at', [$rangeFrom, $rangeTo]);

            $todayOrderCount = (clone $orderQBase)->count();
            $todayRevenue = (clone $orderQBase)->sum('amount_received');
            $monthRevenue = $todayRevenue; // same range
        } else {
            $todayOrderCount = Cache::remember("dash.today_orders.{$today}", 120, function () use ($today) {
                return Order::whereDate('created_at', $today)
                    ->whereNotIn('diamond_status', $this->cancelledStatuses)
                    ->count();
            }); 

            $todayRevenue = Cache::remember("dash.today_revenue.{$today}", 120, function () use ($today) {
                return Order::whereDate('created_at', $today)
                    ->whereNotIn('diamond_status', $this->cancelledStatuses)
                    ->sum('amount_received');
            });

            $monthRevenue = Cache::remember("dash.month_revenue.{$month}.{$year}", 300, function () use ($month, $year) {
                return Order::whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->whereNotIn('diamond_status', $this->cancelledStatuses)
                    ->sum('amount_received');
            });
        }

        // ── 2. DIAMOND STATS ──────────────────────────────────────
        $diamondsInStock = Cache::remember('dash.diamonds_in_stock', 300, function () {
            return Diamond::where('is_sold_out', 'IN Stock')->count();
        });

        if ($hasRange) {
            $diamondsSoldThisMonth = Diamond::where('is_sold_out', 'Sold Out')
                ->whereBetween('updated_at', [$rangeFrom, $rangeTo])
                ->count();
        } else {
            $diamondsSoldThisMonth = Cache::remember("dash.diamonds_sold.{$month}.{$year}", 300, function () use ($month, $year) {
                return Diamond::where('is_sold_out', 'Sold Out')
                    ->whereMonth('updated_at', $month)
                    ->whereYear('updated_at', $year)
                    ->count();
            });
        }

        // ── 3. ORDERS PIPELINE ────────────────────────────────────
        $activeOrders = Cache::remember('dash.active_orders', 120, function () {
            return Order::where(function ($q) {
                $q->whereNotIn('diamond_status', array_merge($this->cancelledStatuses, $this->shippedStatuses))
                    ->orWhereNull('diamond_status');
            })
                ->count();
        });

        $overdueOrders = Cache::remember("dash.overdue_orders.{$today}", 120, function () use ($today) {
            return Order::whereDate('dispatch_date', '<', $today)
                ->where(function ($q) {
                    $q->whereNotIn('diamond_status', array_merge($this->cancelledStatuses, $this->shippedStatuses))
                        ->orWhereNull('diamond_status');
                })
                ->count();
        });

        // ── 4. DRAFTS ─────────────────────────────────────────────
        $myDraftCount = 0;
        if ($admin) {
            $myDraftCount = Cache::remember("dash.drafts.{$admin->id}", 60, function () use ($admin) {
                return OrderDraft::where('admin_id', $admin->id)->count();
            });
        }

        // ── 5. LEADS ──────────────────────────────────────────────
        $leadStats = ['slaBreached' => 0, 'newLeads' => 0];
        if (class_exists(Lead::class)) {
            $leadStats = Cache::remember('dash.lead_stats', 180, function () {
                return [
                    'slaBreached' => Lead::whereNotIn('status', ['completed', 'lost'])
                        ->where('sla_deadline', '<', now())->count(),
                    'newLeads' => Lead::where('status', 'new')->count(),
                ];
            });
        }

        // ── 6. PACKAGES ───────────────────────────────────────────
        $overduePackages = 0;
        if (class_exists(Package::class)) {
            $overduePackages = Cache::remember("dash.overdue_packages.{$today}", 300, function () use ($today) {
                return Package::where('status', 'issued')
                    ->whereDate('return_date', '<', $today)->count();
            });
        }

        // ── 7. INVOICES ───────────────────────────────────────────
        if ($hasRange) {
            $invoiceStats = Invoice::whereBetween('invoice_date', [$rangeFrom, $rangeTo])
                ->selectRaw('COUNT(*) as count, SUM(total_invoice_value) as total')
                ->first();
        } else {
            $invoiceStats = Cache::remember("dash.invoices.{$month}.{$year}", 300, function () use ($month, $year) {
                return Invoice::whereMonth('invoice_date', $month)
                    ->whereYear('invoice_date', $year)
                    ->selectRaw('COUNT(*) as count, SUM(total_invoice_value) as total')
                    ->first();
            });
        }

        // ── 8. CLIENTS ────────────────────────────────────────────
        $totalClients = Cache::remember('dash.total_clients', 600, function () {
            return Client::count();
        });

        // ── 9. ALERT FLAGS ────────────────────────────────────────
        $alerts = [];
        if ($overdueOrders > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'bi-exclamation-triangle-fill',
                'message' => "{$overdueOrders} order(s) are overdue and haven't shipped yet.",
                'link' => route('orders.index', ['overdue' => 1]),
                'label' => 'View Overdue'
            ];
        }
        if ($overduePackages > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'bi-box-seam-fill',
                'message' => "{$overduePackages} package(s) are overdue for return.",
                'link' => route('packages.index'),
                'label' => 'View Packages'
            ];
        }
        if ($leadStats['slaBreached'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'bi-clock-fill',
                'message' => "{$leadStats['slaBreached']} lead(s) have breached their 24-hour SLA.",
                'link' => route('leads.index'),
                'label' => 'View Leads'
            ];
        }
        if ($myDraftCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'bi-file-earmark-text-fill',
                'message' => "You have {$myDraftCount} unsaved order draft(s).",
                'link' => route('orders.drafts.index'),
                'label' => 'Resume'
            ];
        }

        // ── 10. RECENT ACTIVITY ───────────────────────────────────
        $recentActivity = collect();
        if ($admin) {
            $recentActivity = $admin->notifications()->latest()->take(8)->get()
                ->map(function ($n) {
                    $data = $n->data ?? [];
                    $title = $data['title'] ?? 'Notification';
                    $msg = $data['message'] ?? ($data['body'] ?? '');
                    $t = strtolower($title);

                    if (str_contains($t, 'cancel')) {
                        $icon = 'bi-x-circle-fill';
                        $color = 'red';
                    } elseif (str_contains($t, 'sold') || str_contains($t, 'gem')) {
                        $icon = 'bi-gem';
                        $color = 'green';
                    } elseif (str_contains($t, 'diamond') || str_contains($t, 'melee')) {
                        $icon = 'bi-gem';
                        $color = 'purple';
                    } elseif (str_contains($t, 'mention') || str_contains($t, 'chat')) {
                        $icon = 'bi-at';
                        $color = 'blue';
                    } elseif (str_contains($t, 'export') || str_contains($t, 'import')) {
                        $icon = 'bi-arrow-down-circle-fill';
                        $color = 'blue';
                    } elseif (str_contains($t, 'reminder')) {
                        $icon = 'bi-alarm-fill';
                        $color = 'amber';
                    } elseif (str_contains($t, 'order') || str_contains($t, 'creat')) {
                        $icon = 'bi-basket-fill';
                        $color = 'green';
                    } elseif (str_contains($t, 'updat')) {
                        $icon = 'bi-arrow-repeat';
                        $color = 'blue';
                    } else {
                        $icon = 'bi-bell-fill';
                        $color = 'gray';
                    }

                    return (object) [
                        'title' => $title,
                        'message' => $msg,
                        'icon' => $icon,
                        'color' => $color,
                        'read' => !is_null($n->read_at),
                        'time' => $n->created_at->diffForHumans(),
                        'url' => $data['url'] ?? null,
                    ];
                });
        }

        return view('admin.dashboard', compact(
            'todayOrderCount',
            'todayRevenue',
            'monthRevenue',
            'diamondsInStock',
            'diamondsSoldThisMonth',
            'activeOrders',
            'overdueOrders',
            'myDraftCount',
            'leadStats',
            'overduePackages',
            'invoiceStats',
            'totalClients',
            'alerts',
            'recentActivity',
            'dateFrom',
            'dateTo',
            'hasRange'
        ));
    }
}
