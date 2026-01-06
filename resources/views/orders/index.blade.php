@extends('layouts.admin')

@section('title', 'Orders Management')

@php
    // Status color mapping
    $statusColors = [
        'processed' => 'info',
        'completed' => 'success',

        // Custom Diamond
        'd_diamond_in_discuss' => 'info',
        'd_diamond_in_making' => 'warning',
        'd_diamond_completed' => 'success',
        'd_diamond_in_certificate' => 'purple',
        'd_order_shipped' => 'dark',

        // Custom Jewellery
        'j_diamond_in_progress' => 'info',
        'j_diamond_completed' => 'success',
        'j_diamond_in_discuss' => 'cyan',
        'j_cad_in_progress' => 'warning',
        'j_cad_done' => 'purple',
        'j_order_completed' => 'success',
        'j_order_in_qc' => 'warning',
        'j_qc_done' => 'success',
        'j_order_shipped' => 'dark',
        'j_order_hold' => 'danger',
    ];

    $statusIcons = [
        'processed' => 'bi-arrow-repeat',
        'completed' => 'bi-check-circle',

        // Custom Diamond
        'd_diamond_in_discuss' => 'bi-chat-dots',
        'd_diamond_in_making' => 'bi-tools',
        'd_diamond_completed' => 'bi-gem',
        'd_diamond_in_certificate' => 'bi-file-earmark-text',
        'd_order_shipped' => 'bi-truck',

        // Custom Jewellery
        'j_diamond_in_progress' => 'bi-gem',
        'j_diamond_completed' => 'bi-check-circle',
        'j_diamond_in_discuss' => 'bi-chat-dots',
        'j_cad_in_progress' => 'bi-pencil-square',
        'j_cad_done' => 'bi-file-check',
        'j_order_completed' => 'bi-award',
        'j_order_in_qc' => 'bi-search',
        'j_qc_done' => 'bi-check-all',
        'j_order_shipped' => 'bi-truck',
        'j_order_hold' => 'bi-pause-circle',
    ];
@endphp



@section('content')
    <div class="orders-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Orders</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-cart-check-fill"></i>
                        Orders Management
                    </h1>
                    <p class="page-subtitle">Manage and track all orders (Ready to Ship, Custom Diamond, Custom Jewellery)
                    </p>
                </div>
                <div class="header-right">
                    <a href="{{ route('orders.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Order</span>
                    </a>
                </div>
            </div>
        </div>
        <style>
            .stats-grid a.stat-card {
                display: flex;
                gap: 1rem;
                text-decoration: none;
                color: inherit;
                padding: 1rem;
                border-radius: 12px;
            }

            .stats-grid .stat-card .stat-icon {
                flex-shrink: 0;
            }

            .stats-grid a.stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            }

            .stats-grid a.active-filter {
                outline: 3px solid rgba(99, 102, 241, 0.12);
                box-shadow: 0 6px 20px rgba(99, 102, 241, 0.08);
            }
        </style>

        <!-- Stats Cards -->
        <div class="stats-grid">
            {{-- Total Orders - clicking clears order_type filter --}}
            @php
                $baseParams = request()->except(['order_type', 'page']);
            @endphp
            <a href="{{ route('orders.index', $baseParams) }}"
                class="stat-card stat-card-primary {{ request('order_type') ? '' : 'active-filter' }}">
                <div class="stat-icon">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value">{{ $totalOrders }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-graph-up"></i> All orders
                    </div>
                </div>
            </a>

            {{-- Ready to Ship --}}
            @php $paramsReady = array_merge(request()->except(['page']), ['order_type' => 'ready_to_ship']); @endphp
            @php $readyHref = request('order_type') === 'ready_to_ship' ? route('orders.index', request()->except(['order_type', 'page'])) : route('orders.index', $paramsReady); @endphp
            <a href="{{ $readyHref }}"
                class="stat-card stat-card-info {{ request('order_type') === 'ready_to_ship' ? 'active-filter' : '' }}">
                <div class="stat-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Ready to Ship</div>
                    <div class="stat-value">{{ $orderTypeCounts['ready_to_ship'] ?? 0 }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-truck"></i> In stock
                    </div>
                </div>
            </a>

            {{-- Custom Diamond --}}
            @php $paramsDiamond = array_merge(request()->except(['page']), ['order_type' => 'custom_diamond']); @endphp
            @php $diamondHref = request('order_type') === 'custom_diamond' ? route('orders.index', request()->except(['order_type', 'page'])) : route('orders.index', $paramsDiamond); @endphp
            <a href="{{ $diamondHref }}"
                class="stat-card stat-card-warning {{ request('order_type') === 'custom_diamond' ? 'active-filter' : '' }}">
                <div class="stat-icon">
                    <i class="bi bi-gem"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Custom Diamond</div>
                    <div class="stat-value">{{ $orderTypeCounts['custom_diamond'] ?? 0 }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-star"></i> Custom
                    </div>
                </div>
            </a>

            {{-- Custom Jewellery --}}
            @php $paramsJewellery = array_merge(request()->except(['page']), ['order_type' => 'custom_jewellery']); @endphp
            @php $jewelleryHref = request('order_type') === 'custom_jewellery' ? route('orders.index', request()->except(['order_type', 'page'])) : route('orders.index', $paramsJewellery); @endphp
            <a href="{{ $jewelleryHref }}"
                class="stat-card stat-card-success {{ request('order_type') === 'custom_jewellery' ? 'active-filter' : '' }}">
                <div class="stat-icon">
                    <i class="bi bi-award"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Custom Jewellery</div>
                    <div class="stat-value">{{ $orderTypeCounts['custom_jewellery'] ?? 0 }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-hammer"></i> Crafted
                    </div>
                </div>
            </a>

            {{-- Total Shipped Orders --}}
            @php $shippedHref = request('shipped') === '1' ? route('orders.index', request()->except(['shipped', 'page'])) : route('orders.index', array_merge(request()->except(['page', 'order_type', 'diamond_status']), ['shipped' => '1'])); @endphp
            <a href="{{ $shippedHref }}"
                class="stat-card stat-card-dark {{ request('shipped') === '1' ? 'active-filter' : '' }}">
                <div class="stat-icon">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Shipped</div>
                    <div class="stat-value">{{ $shippedOrdersCount ?? 0 }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-check-all"></i> Delivered
                    </div>
                </div>
            </a>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('orders.index') }}" class="filter-form">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="search" class="search-input"
                        placeholder="Search by client, company, or jewellery..." value="{{ request('search') }}">
                </div>

                <select name="order_type" class="filter-select">
                    <option value="">All Order Types</option>
                    <option value="ready_to_ship" {{ request('order_type') == 'ready_to_ship' ? 'selected' : '' }}>
                        Ready to Ship
                    </option>
                    <option value="custom_diamond" {{ request('order_type') == 'custom_diamond' ? 'selected' : '' }}>
                        Custom Diamond
                    </option>
                    <option value="custom_jewellery" {{ request('order_type') == 'custom_jewellery' ? 'selected' : '' }}>
                        Custom Jewellery
                    </option>
                </select>

                <select name="diamond_status" class="filter-select">
                    <option value="">All Diamond Status</option>

                    {{-- Ready to Ship Statuses --}}
                    <option value="r_order_in_process" class="status-option ready_to_ship" {{ request('diamond_status') == 'r_order_in_process' ? 'selected' : '' }}>R - Order In Process</option>
                    <option value="r_order_shipped" class="status-option ready_to_ship" {{ request('diamond_status') == 'r_order_shipped' ? 'selected' : '' }}>R - Order Shipped</option>

                    {{-- Custom Diamond Statuses --}}
                    <option value="d_diamond_in_discuss" class="status-option custom_diamond" {{ request('diamond_status') == 'd_diamond_in_discuss' ? 'selected' : '' }}>D - Diamond In Discuss
                    </option>
                    <option value="d_diamond_in_making" class="status-option custom_diamond" {{ request('diamond_status') == 'd_diamond_in_making' ? 'selected' : '' }}>D - Diamond In Making</option>
                    <option value="d_diamond_completed" class="status-option custom_diamond" {{ request('diamond_status') == 'd_diamond_completed' ? 'selected' : '' }}>D - Diamond Completed</option>
                    <option value="d_diamond_in_certificate" class="status-option custom_diamond" {{ request('diamond_status') == 'd_diamond_in_certificate' ? 'selected' : '' }}>D - Diamond In
                        Certificate</option>
                    <option value="d_order_shipped" class="status-option custom_diamond" {{ request('diamond_status') == 'd_order_shipped' ? 'selected' : '' }}>D - Order Shipped</option>

                    {{-- Custom Jewellery Statuses --}}
                    <option value="j_diamond_in_progress" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_diamond_in_progress' ? 'selected' : '' }}>J - Diamond In Progress
                    </option>
                    <option value="j_diamond_completed" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_diamond_completed' ? 'selected' : '' }}>J - Diamond Completed</option>
                    <option value="j_diamond_in_discuss" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_diamond_in_discuss' ? 'selected' : '' }}>J - Diamond In Discuss
                    </option>
                    <option value="j_cad_in_progress" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_cad_in_progress' ? 'selected' : '' }}>J - CAD In Progress</option>
                    <option value="j_cad_done" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_cad_done' ? 'selected' : '' }}>J - CAD Done</option>
                    <option value="j_order_completed" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_order_completed' ? 'selected' : '' }}>J - Order Completed</option>
                    <option value="j_order_in_qc" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_order_in_qc' ? 'selected' : '' }}>J - Order IN QC</option>
                    <option value="j_qc_done" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_qc_done' ? 'selected' : '' }}>J - QC Done</option>
                    <option value="j_order_shipped" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_order_shipped' ? 'selected' : '' }}>J - Order Shipped</option>
                    <option value="j_order_hold" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_order_hold' ? 'selected' : '' }}>J - Order Hold</option>
                </select>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const orderTypeSelect = document.querySelector('select[name="order_type"]');
                        const statusSelect = document.querySelector('select[name="diamond_status"]');
                        const statusOptions = statusSelect.querySelectorAll('.status-option');

                        function updateStatusDropdown() {
                            const selectedType = orderTypeSelect.value;

                            // 1. If Ready to Ship selected, hide the status dropdown completely (as per user request)
                            if (selectedType === 'ready_to_ship') {
                                statusSelect.style.display = 'none';
                                statusSelect.value = ''; // Clear selection
                            } else {
                                statusSelect.style.display = 'block';
                            }

                            // 2. Filter options based on type
                            statusOptions.forEach(option => {
                                if (!selectedType) {
                                    // If no type selected, show all
                                    option.style.display = 'block';
                                } else if (selectedType === 'custom_diamond') {
                                    if (option.classList.contains('custom_diamond')) {
                                        option.style.display = 'block';
                                    } else {
                                        option.style.display = 'none';
                                    }
                                } else if (selectedType === 'custom_jewellery') {
                                    if (option.classList.contains('custom_jewellery')) {
                                        option.style.display = 'block';
                                    } else {
                                        option.style.display = 'none';
                                    }
                                } else if (selectedType === 'ready_to_ship') {
                                    if (option.classList.contains('ready_to_ship')) {
                                        option.style.display = 'block';
                                    } else {
                                        option.style.display = 'none';
                                    }
                                }
                            });

                            // If current selected status is hidden/invalid for the new type, reset to 'All'
                            const currentSelected = statusSelect.selectedOptions[0];
                            if (currentSelected && currentSelected.style.display === 'none' && currentSelected.value !== '') {
                                statusSelect.value = '';
                            }
                        }

                        // Run on load
                        updateStatusDropdown();

                        // Run on change
                        orderTypeSelect.addEventListener('change', updateStatusDropdown);
                    });
                </script>

                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i>
                    <span>Filter</span>
                </button>

                @if(request('search') || request('order_type') || request('diamond_status'))
                    <a href="{{ route('orders.index') }}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            <div class="filter-info">
                <span class="result-count">Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of
                    {{ $orders->total() }} orders</span>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="orders-table-card">
            <div class="table-container">
                @if($orders->count() > 0)
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th class="th-id">
                                    <div class="th-content">
                                        <i class="bi bi-hash"></i>
                                        <span>ID</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-person"></i>
                                        <span>Client</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-building"></i>
                                        <span>Company</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-tag"></i>
                                        <span>Order Type</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-gem"></i>
                                        <span>Diamond Status</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-currency-dollar"></i>
                                        <span>Gross Sell</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-calendar-plus"></i>
                                        <span>Created Date</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>Dispatch Date</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-person-badge"></i>
                                        <span>Created By</span>
                                    </div>
                                </th>
                                <th class="th-actions">
                                    <div class="th-content">
                                        <i class="bi bi-gear"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr class="table-row">
                                    <td class="td-id">
                                        <span class="order-id-badge">#{{ $order->id }}</span>
                                    </td>
                                    <td>
                                        <div class="client-info">
                                            <span
                                                class="client-name">{{ Str::limit($order->display_client_name ?? ($order->client_details ?? ''), 30) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="company-name">{{ $order->company->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if($order->order_type == 'ready_to_ship')
                                            <span class="order-type-badge badge-info">
                                                <i class="bi bi-box-seam"></i>
                                                Ready to Ship
                                            </span>
                                        @elseif($order->order_type == 'custom_diamond')
                                            <span class="order-type-badge badge-warning">
                                                <i class="bi bi-gem"></i>
                                                Custom Diamond
                                            </span>
                                        @else
                                            <span class="order-type-badge badge-primary">
                                                <i class="bi bi-award"></i>
                                                Custom Jewellery
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusKey = $order->diamond_status ?? 'processed';
                                            $color = $statusColors[$statusKey] ?? 'secondary';
                                            $icon = $statusIcons[$statusKey] ?? 'bi-circle';
                                        @endphp
                                        <span class="status-badge status-{{ $color }}">
                                            <i class="bi {{ $icon }}"></i>
                                            {{ ucfirst(str_replace('_', ' ', $order->diamond_status ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="amount-value">
                                            @if($order->gross_sell)
                                                $ {{ number_format($order->gross_sell, 2) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->created_at)
                                            <div class="date-info">
                                                <span class="date-main">{{ $order->created_at->format('M d, Y') }}</span>
                                                <span class="date-time">{{ $order->created_at->format('l') }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->dispatch_date)
                                            <div class="date-info">
                                                <span
                                                    class="date-main">{{ \Carbon\Carbon::parse($order->dispatch_date)->format('M d, Y') }}</span>
                                                <span
                                                    class="date-time">{{ \Carbon\Carbon::parse($order->dispatch_date)->format('l') }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="creator-info">
                                            <div class="creator-avatar">
                                                {{ substr($order->creator?->name ?? '?', 0, 1) }}
                                            </div>
                                            <span class="creator-name">{{ $order->creator?->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="td-actions">
                                        <div class="action-buttons">
                                            <a href="{{ route('orders.show', $order->id) }}" class="action-btn action-btn-view"
                                                title="View Order">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['orders.edit']))
                                                <a href="{{ route('orders.edit', $order->id) }}" class="action-btn action-btn-edit"
                                                    title="Edit Order">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif

                                            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->canAccessAny(['orders.delete']))
                                                <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn action-btn-delete" title="Delete Order">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-cart-x"></i>
                        </div>
                        <h3 class="empty-title">No orders found</h3>
                        <p class="empty-description">
                            @if(request('search') || request('order_type') || request('diamond_status'))
                                No orders match your search criteria. Try adjusting your filters.
                            @else
                                Get started by creating your first order.
                            @endif
                        </p>
                        @if(request('search') || request('order_type') || request('diamond_status'))
                            <a href="{{ route('orders.index') }}" class="btn-primary-custom">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset Filters
                            </a>
                        @else
                            <a href="{{ route('orders.create') }}" class="btn-primary-custom">
                                <i class="bi bi-plus-circle"></i>
                                Create First Order
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="pagination-container">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --purple: #a855f7;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
            --shadow-md: rgba(0, 0, 0, 0.1);
            --shadow-lg: rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
        }

        .orders-management-container {
            padding: 2rem;
            max-width: 1800px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .breadcrumb-link {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-link:hover {
            color: var(--primary);
        }

        .breadcrumb-separator {
            font-size: 0.75rem;
        }

        .breadcrumb-current {
            color: var(--dark);
            font-weight: 500;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary);
        }

        .page-subtitle {
            color: var(--gray);
            margin: 0;
            font-size: 1rem;
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            color: white;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px var(--shadow-md);
        }

        .stat-card-primary {
            border-color: rgba(99, 102, 241, 0.1);
        }

        .stat-card-primary:hover {
            border-color: var(--primary);
        }

        .stat-card-success {
            border-color: rgba(16, 185, 129, 0.1);
        }

        .stat-card-success:hover {
            border-color: var(--success);
        }

        .stat-card-warning {
            border-color: rgba(245, 158, 11, 0.1);
        }

        .stat-card-warning:hover {
            border-color: var(--warning);
        }

        .stat-card-info {
            border-color: rgba(59, 130, 246, 0.1);
        }

        .stat-card-info:hover {
            border-color: var(--info);
        }

        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .stat-card-primary .stat-icon {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
        }

        .stat-card-success .stat-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: var(--success);
        }

        .stat-card-warning .stat-icon {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            color: var(--warning);
        }

        .stat-card-info .stat-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            color: var(--info);
        }

        .stat-card-dark {
            border-color: rgba(30, 41, 59, 0.1);
        }

        .stat-card-dark:hover {
            border-color: var(--dark);
        }

        .stat-card-dark .stat-icon {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.1), rgba(30, 41, 59, 0.05));
            color: var(--dark);
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-trend {
            font-size: 0.875rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .filter-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: var(--light-gray);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 180px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-filter,
        .btn-reset {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            background: white;
            color: var(--gray);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-filter:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .btn-reset:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        .filter-info {
            display: flex;
            justify-content: flex-end;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border);
        }

        .result-count {
            color: var(--gray);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Orders Table */
        .orders-table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px var(--shadow);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead {
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
        }

        .orders-table th {
            padding: 1.25rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .th-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .th-content i {
            color: var(--primary);
            font-size: 1rem;
        }

        .th-id {
            width: 80px;
        }


        .th-actions {
            width: 140px;
            text-align: center;
        }

        .th-actions .th-content {
            justify-content: center;
        }

        /* Compact column widths to prevent horizontal scroll */
        .orders-table th,
        .orders-table td {
            padding: 1rem 0.75rem;
        }

        .date-info {
            min-width: 90px;
        }

        .order-type-badge,
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.6rem;
        }

        .table-row {
            border-bottom: 1px solid var(--border);
            transition: all 0.2s;
        }

        .table-row:hover {
            background: var(--light-gray);
        }

        .orders-table td {
            padding: 1.25rem 1.5rem;
            color: var(--dark);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .td-id {
            font-weight: 600;
        }

        .order-id-badge {
            display: inline-block;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            color: var(--primary);
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .client-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .client-name {
            font-weight: 500;
            color: var(--dark);
        }

        .company-name {
            font-weight: 500;
            color: var(--dark);
        }

        .order-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge-primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-info {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }

        .status-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .status-secondary {
            background: rgba(100, 116, 139, 0.1);
            color: var(--gray);
        }

        .status-purple {
            background: rgba(168, 85, 247, 0.1);
            color: var(--purple);
        }

        .status-dark {
            background: rgba(30, 41, 59, 0.1);
            color: var(--dark);
        }

        .status-cyan {
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
        }

        .amount-value {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--success);
            white-space: nowrap;
        }

        .creator-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .creator-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .creator-name {
            font-weight: 500;
            color: var(--dark);
        }

        .date-info {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .date-main {
            font-weight: 500;
            color: var(--dark);
            font-size: 0.8rem;
        }

        .date-time {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .td-actions {
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border);
            background: white;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-md);
        }

        .action-btn-view:hover {
            border-color: var(--info);
            color: var(--info);
            background: rgba(59, 130, 246, 0.05);
        }

        .action-btn-edit:hover {
            border-color: var(--warning);
            color: var(--warning);
            background: rgba(245, 158, 11, 0.05);
        }

        .action-btn-delete:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary);
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
        }

        .empty-description {
            color: var(--gray);
            margin: 0 0 2rem 0;
        }

        /* Pagination */
        .pagination-container {
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
        }

        .pagination-container .pagination {
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .orders-management-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
            }

            .header-right {
                width: 100%;
            }

            .btn-primary-custom {
                width: 100%;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-form {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            .filter-select {
                width: 100%;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .orders-table {
                min-width: 1200px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            animation: fadeIn 0.4s ease forwards;
            opacity: 0;
        }

        .stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stat-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .table-row {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Print Styles */
        @media print {

            .page-header,
            .filter-section,
            .action-buttons,
            .pagination-container {
                display: none;
            }

            .orders-table-card {
                box-shadow: none;
                border: 1px solid var(--border);
            }

            .table-row:hover {
                background: white;
            }

            .orders-table {
                font-size: 0.85rem;
            }
        }

        /* Custom Scrollbar */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray);
        }

        /* Text Utilities */
        .text-muted {
            color: var(--gray) !important;
        }

        .d-inline {
            display: inline !important;
        }

        /* Bootstrap Pagination Override */
        .pagination-container .pagination .page-link {
            color: var(--primary);
            border: 2px solid var(--border);
            border-radius: 8px;
            margin: 0 0.25rem;
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .pagination-container .pagination .page-link:hover {
            background: rgba(99, 102, 241, 0.05);
            border-color: var(--primary);
            color: var(--primary);
        }

        .pagination-container .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .pagination-container .pagination .page-item.disabled .page-link {
            color: var(--gray);
            border-color: var(--border);
            opacity: 0.5;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add stagger animation to table rows
            const rows = document.querySelectorAll('.table-row');
            rows.forEach((row, index) => {
                row.style.animationDelay = `${(index % 10) * 0.05}s`;
            });

            // Initialize stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.opacity = '1';
                }, 100 * (index + 1));
            });

            // Handle delete confirmations with SweetAlert2
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    const confirmed = await showConfirm(
                        'Are you sure you want to delete this order?',
                        'This action cannot be undone',
                        'Yes, Delete',
                        'Cancel'
                    );

                    if (confirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>

@endsection