﻿@extends('layouts.admin')

@section('title', 'Orders Management')

@php
    // Status color mapping
    $statusColors = [
        'r_order_in_process' => 'info',
        'r_order_shipped' => 'success',
        'r_order_cancelled' => 'danger',

        // Custom Diamond
        'd_diamond_in_discuss' => 'info',
        'd_diamond_in_making' => 'warning',
        'd_diamond_completed' => 'success',
        'd_diamond_in_certificate' => 'purple',
        'd_order_shipped' => 'dark',
        'd_order_cancelled' => 'danger',

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
        'j_order_cancelled' => 'danger',
    ];

    $statusIcons = [
        'r_order_in_process' => 'bi-arrow-repeat',
        'r_order_shipped' => 'bi-truck',
        'r_order_cancelled' => 'bi-x-circle',

        // Custom Diamond
        'd_diamond_in_discuss' => 'bi-chat-dots',
        'd_diamond_in_making' => 'bi-tools',
        'd_diamond_completed' => 'bi-gem',
        'd_diamond_in_certificate' => 'bi-file-earmark-text',
        'd_order_shipped' => 'bi-truck',
        'd_order_cancelled' => 'bi-x-circle',

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
        'j_order_cancelled' => 'bi-x-circle',
    ];
@endphp

@section('content')

    <!-- Main Content -->
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
                    @php
                        $draftCount = \App\Models\OrderDraft::where('admin_id', auth()->guard('admin')->id())->notExpired()->count();
                    @endphp
                    @if($draftCount > 0)
                        <a href="{{ route('orders.drafts.index') }}" class="btn-drafts-custom">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Drafts</span>
                            <span class="draft-count-badge">{{ $draftCount }}</span>
                        </a>
                    @endif
                    <a href="{{ route('orders.sync-all-tracking') }}" class="btn-primary-custom" id="btnSyncAll"
                        style="margin-right: 10px; background: #6366f1;">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Sync All</span>
                    </a>
                    <a href="{{ route('orders.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Order</span>
                    </a>
                </div>
            </div>
        </div>

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
            @php $shippedHref = request('shipped') === '1' ? route('orders.index', request()->except(['shipped', 'page'])) : route('orders.index', array_merge(request()->except(['page', 'order_type', 'diamond_status', 'in_transit']), ['shipped' => '1'])); @endphp
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

            {{-- In Transit Orders --}}
            @php $inTransitHref = request('in_transit') === '1' ? route('orders.index', request()->except(['in_transit', 'page'])) : route('orders.index', array_merge(request()->except(['page', 'order_type', 'diamond_status', 'shipped']), ['in_transit' => '1'])); @endphp
            <a href="{{ $inTransitHref }}"
                class="stat-card stat-card-warning {{ request('in_transit') === '1' ? 'active-filter' : '' }}"
                style="background: #fffbeb;">
                <div class="stat-icon" style="background: #fbbf24; color: white;">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">In Transit</div>
                    <div class="stat-value" style="color: #92400e;">{{ $inTransitCount ?? 0 }}</div>
                    <div class="stat-trend" style="color: #b45309;">
                        <i class="bi bi-broadcast"></i> Live Tracking
                    </div>
                </div>
            </a>

            {{-- Today's Sales (Clickable to toggle Company Progress) - Only visible with sales.view permission --}}
            @if(auth('admin')->user()->hasExplicitPermission('sales.view'))
                <div class="stat-card stat-card-sales" id="todaysSalesCard" onclick="toggleCompanyProgress()"
                    style="cursor: pointer;">
                    <div class="stat-icon"
                        style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05)); color: #10b981;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Today's Sales <i class="bi bi-chevron-down toggle-icon" id="toggleIcon"></i>
                        </div>
                        <div class="stat-value">${{ number_format($todaysSales ?? 0, 2) }}</div>
                        <div class="stat-trend">
                            <span class="live-badge"><i class="bi bi-circle-fill"></i> Live</span>
                            {{ $todaysOrderCount ?? 0 }} shipped orders
                        </div>
                        <div class="stat-month-sales"
                            style="font-size: 0.7rem; color: #64748b; margin-top: 4px; border-top: 1px solid rgba(16, 185, 129, 0.1); padding-top: 4px;">
                            <span style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Month:</span>
                                <span style="font-weight: 600; color: #10b981;">${{ number_format($monthSales ?? 0, 2) }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Company Monthly Progress Section (Hidden by default, toggle on Today's Sales click) --}}
        @if(auth('admin')->user()->hasExplicitPermission('sales.view') && isset($companySalesStats) && $companySalesStats->count() > 0)
            <div class="company-progress-section" id="companyProgressSection" style="display: none;">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="bi bi-graph-up-arrow"></i>
                        Company Monthly Progress - {{ now()->format('F Y') }}
                    </h3>
                </div>
                <div class="company-progress-grid">
                    @foreach($companySalesStats as $company)
                        <a href="{{ route('companies.sales-dashboard', $company['id']) }}" class="company-progress-card-simple">
                            <div class="company-name-simple">{{ $company['name'] }}</div>
                            <div class="progress-ring-large">
                                @if($company['target_progress'] !== null)
                                    <svg viewBox="0 0 36 36" class="circular-chart-large">
                                        <path class="circle-bg-large"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path class="circle-large" stroke-dasharray="{{ min($company['target_progress'], 100) }}, 100"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <text x="18" y="20.35" class="percentage-large">{{ round($company['target_progress']) }}%</text>
                                    </svg>
                                @else
                                    <div class="no-target-large">
                                        <span>No Target</span>
                                    </div>
                                @endif
                            </div>
                            <span class="view-report-link-simple">
                                View Report <i class="bi bi-arrow-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Overdue Orders Alert Banner --}}
        @if(isset($overdueOrdersCount) && $overdueOrdersCount > 0 && !session('hide_overdue_banner'))
            <div class="overdue-alert-banner" id="overdueAlertBanner">
                <div class="overdue-alert-content">
                    <div class="overdue-alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="overdue-alert-text">
                        <strong>Attention!</strong> You have <span class="overdue-count">{{ $overdueOrdersCount }}</span>
                        overdue {{ Str::plural('order', $overdueOrdersCount) }} that need attention!
                    </div>
                    <a href="{{ route('orders.index', ['overdue' => '1']) }}" class="btn-view-overdue">
                        <i class="bi bi-eye"></i> View Overdue Orders
                    </a>
                </div>
                <button class="overdue-alert-close" onclick="dismissOverdueBanner()" title="Dismiss for today">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('orders.index') }}" class="filter-form" id="orderFilterForm">
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
                    <option value="r_order_cancelled" class="status-option ready_to_ship" {{ request('diamond_status') == 'r_order_cancelled' ? 'selected' : '' }}>R - Order Cancelled</option>

                    {{-- Custom Diamond Statuses --}}
                    <option value="d_diamond_in_discuss" class="status-option custom_diamond" {{ request('diamond_status') == 'd_diamond_in_discuss' ? 'selected' : '' }}>D - Diamond In Discuss
                    </option>
                    <option value="d_diamond_in_making" class="status-option custom_diamond" {{ request('diamond_status') == 'd_diamond_in_making' ? 'selected' : '' }}>D - Diamond In Making</option>
                    <option value="d_diamond_completed" class="status-option custom_diamond" {{ request('diamond_status') == 'd_diamond_completed' ? 'selected' : '' }}>D - Diamond Completed</option>
                    <option value="d_diamond_in_certificate" class="status-option custom_diamond" {{ request('diamond_status') == 'd_diamond_in_certificate' ? 'selected' : '' }}>D - Diamond In
                        Certificate</option>
                    <option value="d_order_shipped" class="status-option custom_diamond" {{ request('diamond_status') == 'd_order_shipped' ? 'selected' : '' }}>D - Order Shipped</option>
                    <option value="d_order_cancelled" class="status-option custom_diamond" {{ request('diamond_status') == 'd_order_cancelled' ? 'selected' : '' }}>D - Order Cancelled</option>

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
                    <option value="j_order_cancelled" class="status-option custom_jewellery" {{ request('diamond_status') == 'j_order_cancelled' ? 'selected' : '' }}>J - Order Cancelled</option>
                </select>

                <div class="date-range-wrapper">
                    <input type="text" id="orderDateRange" class="date-range-input" placeholder="Select Date Range"
                        readonly>
                    <input type="hidden" name="date_from" id="orderDateFrom" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" id="orderDateTo" value="{{ request('date_to') }}">
                </div>

                {{-- Overdue Filter Toggle --}}
                @php
                    $overdueActive = request('overdue') === '1';
                    $overdueParams = $overdueActive
                        ? request()->except(['overdue', 'page'])
                        : array_merge(request()->except(['page', 'shipped', 'cancelled', 'in_transit']), ['overdue' => '1']);
                @endphp
                <a href="{{ route('orders.index', $overdueParams) }}"
                    class="btn-overdue-filter {{ $overdueActive ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Overdue</span>
                    @if(isset($overdueOrdersCount) && $overdueOrdersCount > 0)
                        <span class="badge {{ $overdueActive ? 'bg-light text-danger' : 'bg-danger text-white' }}"
                            style="border-radius: 12px; padding: 2px 6px; font-size: 0.75rem;">{{ $overdueOrdersCount }}</span>
                    @endif
                </a>

                {{-- Cancelled Filter Toggle --}}
                @php
                    $cancelledActive = request('cancelled') === '1';
                    $cancelledParams = $cancelledActive
                        ? request()->except(['cancelled', 'page'])
                        : array_merge(request()->except(['page', 'shipped', 'overdue', 'in_transit']), ['cancelled' => '1']);
                @endphp
                <a href="{{ route('orders.index', $cancelledParams) }}"
                    class="btn-cancelled-filter {{ $cancelledActive ? 'active' : '' }}">
                    <i class="bi bi-x-circle-fill"></i>
                    <span>Cancelled</span>
                    @if(isset($cancelledOrdersCount) && $cancelledOrdersCount > 0)
                        <span class="badge"
                            style="background: rgb(239 68 68); border-radius: 12px; padding: 2px 6px; font-size: 0.75rem;">{{ $cancelledOrdersCount }}</span>
                    @endif
                </a>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const filterForm = document.getElementById('orderFilterForm');
                        const orderTypeSelect = document.querySelector('select[name="order_type"]');
                        const statusSelect = document.querySelector('select[name="diamond_status"]');
                        const statusOptions = statusSelect.querySelectorAll('.status-option');
                        const searchInput = document.querySelector('input[name="search"]');

                        // Debounce function for search input
                        let searchTimeout;
                        function debounceSearch(fn, delay) {
                            clearTimeout(searchTimeout);
                            searchTimeout = setTimeout(fn, delay);
                        }

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

                        // Auto-submit on order type change
                        orderTypeSelect.addEventListener('change', function () {
                            updateStatusDropdown();
                            filterForm.submit();
                        });

                        // Auto-submit on diamond status change
                        statusSelect.addEventListener('change', function () {
                            filterForm.submit();
                        });

                        // Debounced auto-submit on search input (500ms delay)
                        searchInput.addEventListener('input', function () {
                            debounceSearch(function () {
                                filterForm.submit();
                            }, 500);
                        });

                        // Also submit on Enter key for search
                        searchInput.addEventListener('keypress', function (e) {
                            if (e.key === 'Enter') {
                                clearTimeout(searchTimeout);
                                filterForm.submit();
                            }
                        });
                        // Sync All Confirm & Loader
                        const btnSyncAll = document.getElementById('btnSyncAll');
                        if (btnSyncAll) {
                            btnSyncAll.addEventListener('click', function (e) {
                                e.preventDefault();
                                const url = this.getAttribute('href');

                                Swal.fire({
                                    title: 'Sync All Orders?',
                                    text: "This process may take a few minutes depending on the number of orders.",
                                    icon: 'info',
                                    showCancelButton: true,
                                    confirmButtonColor: '#6366f1',
                                    cancelButtonColor: '#64748b',
                                    confirmButtonText: 'Yes, start sync!',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Show loading state
                                        Swal.fire({
                                            title: 'Syncing Tracking Data...',
                                            html: 'Please wait while we update tracking information from carriers.<br>Do not close this window.',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            showConfirmButton: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            }
                                        });

                                        // Redirect to sync route
                                        window.location.href = url;
                                    }
                                });
                            });
                        }
                    });
                </script>

                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i>
                    <span>Filter</span>
                </button>

                @if(request('search') || request('order_type') || request('diamond_status') || request('overdue'))
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
                                        <i class="bi bi-image"></i>
                                        <span>Product Image</span>
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
                                        <i class="bi bi-box-seam"></i>
                                        <span>Product Details</span>
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
                                        <i class="bi bi-calendar-range"></i>
                                        <span>Dates</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="bi bi-truck"></i>
                                        <span>Shipping</span>
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
                                @php
                                    // Check if order is overdue: dispatch_date is in the past AND order is NOT shipped
                                    $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];
                                    $isShipped = in_array($order->diamond_status, $shippedStatuses);
                                    $isOverdue = $order->dispatch_date &&
                                        \Carbon\Carbon::parse($order->dispatch_date)->lt(now()->startOfDay()) &&
                                        !$isShipped;
                                @endphp
                                <tr class="table-row {{ $isOverdue ? 'overdue-row' : '' }}">
                                    <td class="td-id">
                                        <span class="order-id-badge">#{{ $order->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $firstImage = null;
                                                if (!empty($order->images)) {
                                                    $imgs = is_string($order->images) ? json_decode($order->images, true) : $order->images;
                                                    $firstImage = is_array($imgs) && count($imgs) > 0 ? $imgs[0] : null;
                                                }

                                                $skus = is_array($order->diamond_skus) ? $order->diamond_skus : (!empty($order->diamond_sku) ? [$order->diamond_sku] : []);
                                                $skuText = !empty($skus) ? implode(', ', $skus) : '—';
                                            @endphp

                                            <div class="thumbnail-container">
                                                @if($firstImage)
                                                    <img src="{{ $firstImage['url'] }}" alt="Product">
                                                @else
                                                    <div
                                                        class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                                        <i class="bi bi-image" style="font-size: 1.5rem;"></i>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="order-details-column">
                                                {{-- Company --}}
                                                @if($order->company)
                                                    <span class="badge-item badge-company">
                                                        <i class="bi bi-building"></i> {{ $order->company->name }}
                                                    </span>
                                                @endif

                                                {{-- Order Type --}}
                                                @if($order->order_type == 'ready_to_ship')
                                                    <span class="badge-item badge-ready-to-ship">
                                                        <i class="bi bi-box-seam"></i> Ready to Ship
                                                    </span>
                                                @elseif($order->order_type == 'custom_diamond')
                                                    <span class="badge-item badge-custom-diamond">
                                                        <i class="bi bi-gem"></i> Custom Diamond
                                                    </span>
                                                @else
                                                    <span class="badge-item badge-custom-jewellery">
                                                        <i class="bi bi-award"></i> Custom Jewellery
                                                    </span>
                                                @endif

                                                {{-- Diamond Status --}}
                                                @php
                                                    $statusKey = $order->diamond_status ?? 'processed';
                                                    $color = $statusColors[$statusKey] ?? 'secondary';
                                                    $icon = $statusIcons[$statusKey] ?? 'bi-circle';
                                                @endphp
                                                <span class="badge-item badge-status status-{{ $color }}">
                                                    <i class="bi {{ $icon }}"></i>
                                                    {{ ucfirst(str_replace('_', ' ', preg_replace('/^[rdj]_/', '', $order->diamond_status ?? 'N/A'))) }}
                                                </span>

                                                {{-- SKU --}}
                                                @if($skuText !== '—')
                                                    <span class="badge-item badge-sku"
                                                        style="background-color: rgb(253 240 240 / 60%); border-color: rgb(212 45 45 / 50%); color: #ff0101; font-weight: normal;"
                                                        title="{{ $skuText }}">
                                                        <i class="bi bi-upc-scan"></i> {{ Str::limit($skuText, 15) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="client-info">
                                            <div class="client-name fw-bold text-dark mb-1">
                                                {{ $order->display_client_name ?? '—' }}
                                            </div>

                                            <div class="client-meta d-flex flex-column gap-1" style="font-size: 0.75rem;">

                                                @if($address = $order->display_client_address)
                                                    <div class="d-flex align-items-start text-muted">
                                                        <span
                                                            style="white-space: normal; line-height: 1.2;">{!! nl2br(e($address)) !!}</span>
                                                    </div>
                                                @endif

                                                @if($email = $order->display_client_email)
                                                    <div class="d-flex align-items-center text-muted">
                                                        {{ $email }}
                                                    </div>
                                                @endif

                                                @if($mobile = $order->display_client_mobile)
                                                    <div class="d-flex align-items-center text-muted">
                                                        {{ $mobile }}
                                                    </div>
                                                @endif

                                                @if($taxId = $order->display_client_tax_id)
                                                    <div class="d-flex align-items-center text-muted">
                                                        <span class="text-uppercase me-1" style="font-size: 0.7rem; font-weight: 600;">
                                                            {{ $order->client_tax_id_type ? (\App\Models\Order::TAX_ID_TYPES[$order->client_tax_id_type] ?? $order->client_tax_id_type) : 'Tax ID' }}:
                                                        </span>
                                                        {{ $taxId }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column gap-1" style="font-size: 0.8rem; max-width: 300px;">
                                            @if($order->jewellery_details)
                                                <div>
                                                    <strong
                                                        style="font-size: 0.7rem; color: #6366f1; display: block;">Jewellery:</strong>
                                                    <div style="white-space: pre-wrap;">{{ trim($order->jewellery_details) }}</div>
                                                </div>
                                            @endif
                                            @if($order->diamond_details)
                                                <div>
                                                    <strong style="font-size: 0.7rem; color: #6366f1; display: block;">Diamond:</strong>
                                                    <div style="white-space: pre-wrap;">{{ trim($order->diamond_details) }}</div>
                                                </div>
                                            @endif
                                            @if(!$order->jewellery_details && !$order->diamond_details)
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <span class="amount-value">
                                            @if($order->gross_sell)
                                                $ {{ number_format($order->gross_sell, 2) }}
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-card">
                                            <div class="date-card-row">
                                                <span class="date-card-label">CREATED ·
                                                    {{ $order->created_at ? $order->created_at->format('D') : '—' }}</span>
                                                <span
                                                    class="date-card-value">{{ $order->created_at ? $order->created_at->format('d M Y') : '—' }}</span>
                                            </div>
                                            <div class="date-card-divider"></div>
                                            <div class="date-card-row">
                                                <span class="date-card-label">DISPATCH ·
                                                    {{ $order->dispatch_date ? \Carbon\Carbon::parse($order->dispatch_date)->format('D') : '—' }}</span>
                                                <span
                                                    class="date-card-value">{{ $order->dispatch_date ? \Carbon\Carbon::parse($order->dispatch_date)->format('d M Y') : '—' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($order->shipping_company_name || $order->tracking_number)
                                            <div class="shipping-info-cell">
                                                <div class="shipping-main">
                                                    <span class="shipping-company">{{ $order->shipping_company_name }}</span>
                                                    @if($order->tracking_number)
                                                        <a href="javascript:void(0)" class="tracking-number-link"
                                                            onclick="showTrackingHistory({{ $order->id }}, {{ json_encode($order->tracking_number) }}, {{ json_encode($order->shipping_company_name) }}, {{ json_encode($order->tracking_history) }}, {{ json_encode($order->tracking_url) }})"
                                                            title="Click to view history">
                                                            {{ $order->tracking_number }}
                                                        </a>
                                                    @endif

                                                    @if($order->tracking_url)
                                                        <button class="btn-sync-inline" onclick="syncTracking({{ $order->id }}, this)"
                                                            title="Sync Tracking">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                                @if($order->tracking_status)
                                                    <div
                                                        class="shipping-status-label status-{{ strtolower(str_replace(' ', '_', $order->tracking_status)) }}">
                                                        {{ $order->tracking_status }}
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">&mdash;</span>
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

                                            @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->hasPermission('orders.cancel') && !in_array($order->diamond_status, ['r_order_cancelled', 'd_order_cancelled', 'j_order_cancelled']))
                                                <button type="button" class="action-btn action-btn-cancel" title="Cancel Order"
                                                    onclick="openCancelModal({{ $order->id }}, '{{ addslashes($order->client_name) }}')"
                                                    style="color: #dc3545; background: rgba(220, 53, 69, 0.1);">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
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
                    {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Tracking History Modal -->
    <div class="modal fade" id="trackingHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header bg-light border-bottom p-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-truck text-primary me-2"></i>
                        Shipment Journey: <span id="modalTrackingNumber" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body tracking-modal-content">
                    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-primary-subtle rounded">
                        <div>
                            <small class="text-muted d-block">Carrier</small>
                            <strong id="modalCarrierName" class="fs-5">-</strong>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Latest Status</small>
                            <span id="modalLatestStatus" class="badge bg-primary fs-6">-</span>
                        </div>
                    </div>

                    <div id="trackingHistoryContainer" class="tracking-history-list">
                        <!-- History items will be injected here -->
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="modalSyncBtn" class="btn btn-warning rounded-pill px-4 text-white">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync Status
                    </button>
                    <a href="#" id="modalOfficialLink" target="_blank" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Official Page
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- CSS -->
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
            padding: 0rem;
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

        .header-right {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .btn-drafts-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            position: relative;
        }

        .btn-drafts-custom:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .draft-count-badge {
            background: white;
            color: #d97706;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
        }

        /* STATS CARDS - Clean Minimal Design */
        .stats-grid {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        /* Allow wrapping on smaller screens */
        @media (max-width: 768px) {
            .stats-grid {
                flex-wrap: wrap;
            }

            .stats-grid .stat-card {
                flex: 1 1 30%;
                min-width: 90px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid .stat-card {
                flex: 1 1 45%;
            }
        }

        /* Stat Card - Clean minimal style */
        .stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            border: none;
            transition: all 0.2s ease;
            flex: 1 1 0;
            min-width: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            text-decoration: none !important;
        }

        .stat-card:hover {
            border-color: #d1d5db;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        /* Icon - Compact circular style */
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        /* Content - Clean typography */
        .stat-content {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 0.125rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-value {
            font-size: clamp(1.125rem, 2vw, 1.5rem);
            font-weight: 600;
            color: #1f2937;
            line-height: 1.2;
            white-space: nowrap;
        }

        .stat-trend {
            font-size: 0.7rem;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 0.125rem;
            margin-top: 0.125rem;
            white-space: nowrap;
        }

        /* Color variants - Subtle icon backgrounds */
        .stat-card-primary .stat-icon {
            background: #eef2ff;
            color: #6366f1;
        }

        .stat-card-success .stat-icon {
            background: #ecfdf5;
            color: #10b981;
        }

        .stat-card-warning .stat-icon {
            background: #fffbeb;
            color: #f59e0b;
        }

        .stat-card-info .stat-icon {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-card-dark .stat-icon {
            background: #f3f4f6;
            color: #374151;
        }

        /* Sales card - Subtle highlight */
        .stat-card-sales {
            border-color: #d1fae5;
            background: linear-gradient(to right, #f0fdf4, #ffffff);
        }

        .stat-card-sales .stat-icon {
            background: #d1fae5;
            color: #059669;
        }

        .stat-card-sales:hover {
            border-color: #10b981;
        }

        /* Live badge - Compact */
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            color: #10b981;
            font-weight: 600;
            font-size: 0.5rem;
        }

        .live-badge i {
            font-size: 0.4rem;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        /* Company Progress Section */
        .company-progress-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .company-progress-section .section-header {
            margin-bottom: 1.5rem;
        }

        .company-progress-section .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .company-progress-section .section-title i {
            color: var(--primary);
        }

        .company-progress-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        /* Simplified Company Progress Cards - Clean design */
        .company-progress-card-simple {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
            border: 2px solid var(--border);
            text-decoration: none;
            color: inherit;
            min-width: 140px;
            flex: 0 0 238px;
        }

        .company-progress-card-simple:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px var(--shadow-md);
        }

        .company-name-simple {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--dark);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .progress-ring-large {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .circular-chart-large {
            display: block;
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .circle-bg-large {
            fill: none;
            stroke: #eee;
            stroke-width: 3;
        }

        .circle-large {
            fill: none;
            stroke: var(--primary);
            stroke-width: 3;
            stroke-linecap: round;
            transition: stroke-dasharray 0.5s ease;
        }

        .percentage-large {
            fill: var(--dark);
            font-size: 0.35rem;
            font-weight: 700;
            text-anchor: middle;
            transform: rotate(90deg);
            transform-origin: center;
        }

        .no-target-large {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--light-gray);
            color: var(--gray);
            font-size: 0.7rem;
            font-weight: 600;
        }

        .view-report-link-simple {
            font-size: 0.8rem;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .view-report-link-simple i {
            transition: transform 0.2s;
        }

        .company-progress-card-simple:hover .view-report-link-simple i {
            transform: translateX(3px);
        }

        .toggle-icon {
            font-size: 0.7rem;
            transition: transform 0.3s;
            margin-left: 0.25rem;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        .company-progress-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: flex-start;
        }



        /* Overdue Alert Banner */
        .overdue-alert-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border: 2px solid #fca5a5;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        .overdue-alert-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .overdue-alert-icon {
            width: 40px;
            height: 40px;
            background: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .overdue-alert-text {
            color: #7f1d1d;
            font-size: 0.95rem;
        }

        .overdue-count {
            font-weight: 700;
            color: #dc2626;
            font-size: 1.1rem;
        }

        .btn-view-overdue {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-view-overdue:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .overdue-alert-close {
            background: transparent;
            border: none;
            color: #b91c1c;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .overdue-alert-close:hover {
            background: rgba(185, 28, 28, 0.1);
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

        .date-filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-input-date {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.95rem;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 140px;
        }

        .filter-input-date:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .date-separator {
            color: var(--gray);
            font-weight: 500;
            font-size: 0.875rem;
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

        /* Overdue Filter Button */
        .btn-overdue-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: 2px solid #ef4444;
            border-radius: 10px;
            background: white;
            color: #ef4444;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-overdue-filter:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .btn-overdue-filter.active {
            background: #ef4444;
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-overdue-filter.active:hover {
            background: #dc2626;
        }

        /* Cancelled Filter Button */
        .btn-cancelled-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: 2px solid #ef4444;
            border-radius: 10px;
            background: white;
            color: #ef4444;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            margin-left: 0.5rem;
        }

        .btn-cancelled-filter:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .btn-cancelled-filter.active {
            background: #ef4444;
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            border-color: #ef4444;
        }

        .btn-cancelled-filter.active:hover {
            background: #dc2626;
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
            padding: 1rem 0.6rem;
        }

        .orders-table th {
            white-space: normal;
            vertical-align: bottom;
        }

        .date-info {
            min-width: 90px;
        }

        /* Date Card Styles */
        .date-card {
            display: flex;
            flex-direction: column;
            min-width: 120px;
            border-left: 3px solid var(--primary);
            padding-left: 10px;
        }

        .date-card-row {
            display: flex;
            flex-direction: column;
            gap: 1px;
            padding: 3px 0;
        }

        .date-card-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray);
        }

        .date-card-value {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--dark);
        }

        .date-card-divider {
            border-bottom: 1.5px dashed var(--border);
            margin: 2px 0;
        }

        .order-type-badge,
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.6rem;
        }

        /* Shipping Column Styles */
        .shipping-info-cell {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            min-width: 150px;
        }

        .shipping-main {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .shipping-company {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.85rem;
        }

        .tracking-number-link {
            color: var(--primary);
            font-family: monospace;
            font-size: 0.85rem;
            text-decoration: underline dotted;
            cursor: pointer;
            transition: color 0.2s;
        }

        .tracking-number-link:hover {
            color: var(--primary-dark);
        }

        .btn-sync-inline {
            background: none;
            border: none;
            padding: 2px;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-sync-inline:hover {
            color: var(--primary);
            transform: rotate(180deg);
        }

        .btn-sync-inline.spinning i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .shipping-status-label {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            display: inline-block;
            width: fit-content;
        }

        /* Status Colors for Shipping */
        .shipping-status-label.status-delivered {
            background: #dcfce7;
            color: #166534;
        }

        .shipping-status-label.status-in_transit {
            background: #fef9c3;
            color: #854d0e;
        }

        .shipping-status-label.status-picked_up {
            background: #dbeafe;
            color: #1e40af;
        }

        .shipping-status-label.status-exception {
            background: #fee2e2;
            color: #991b1b;
        }

        .shipping-status-label.status-unknown {
            background: #f1f5f9;
            color: #475569;
        }

        /* Tracking History Modal */
        .tracking-modal-content {
            padding: 1.5rem;
        }

        .tracking-history-list {
            position: relative;
            padding-left: 2rem;
            margin-top: 1rem;
        }

        .tracking-history-list::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ef4444;
            /* Match Aramex red line */
        }

        .tracking-history-item {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .tracking-history-item:last-child {
            margin-bottom: 0;
        }

        .tracking-history-dot {
            position: absolute;
            left: -2rem;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: white;
            border: 3px solid #ef4444;
            /* Match Aramex red dot */
            z-index: 1;
        }

        .tracking-history-item:first-child .tracking-history-dot {
            background: #ef4444;
            /* Solid red for latest */
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
        }

        .tracking-history-details {
            background: transparent;
            /* Remove boxes as requested */
            padding: 0 1rem 0.5rem 1rem;
            border-radius: 0;
            border: none;
            border-bottom: 1px solid #f1f5f9;
        }

        .tracking-history-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }

        .tracking-history-status {
            font-weight: 700;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .tracking-history-date {
            color: var(--gray);
            font-size: 0.75rem;
        }

        .tracking-history-location {
            font-size: 0.8rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .tracking-history-desc {
            margin-top: 0.4rem;
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.4;
        }

        .table-row {
            border-bottom: 1px solid var(--border);
            transition: all 0.2s;
        }

        .table-row:hover {
            background: var(--light-gray);
        }

        /* Overdue order row - red background for orders past dispatch date but not shipped */
        .table-row.overdue-row {
            background: #FED4D4;
            border-left: 4px solid #ef4444;
        }

        .table-row.overdue-row:hover {
            background: #ffafafff;
        }

        .table-row.overdue-row .order-id-badge {
            background: #ffafafff;
            color: #ff0000ff;
        }

        .orders-table td {
            padding: 15px;
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
            font-weight: 800;
            font-size: 1.2rem;
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



        /* Date Card Styles */
        .date-card {
            display: flex;
            flex-direction: column;
            min-width: 120px;
            border-left: 3px solid var(--primary);
            padding-left: 10px;
        }

        .date-card-row {
            display: flex;
            flex-direction: column;
            gap: 1px;
            padding: 3px 0;
        }

        .date-card-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray);
        }

        .date-card-value {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--dark);
        }

        .date-card-divider {
            border-bottom: 1.5px dashed var(--border);
            margin: 2px 0;
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

        @media (max-width: 576px) {
            #mainContent {
                margin-left: 0px;
                padding: 0 0px;
            }

            h2.navbar-title,
            .navbar-right .notification-btn i,
            .notification-header h6,
            .notification-empty p,
            .notification-icon {
                font-size: 12px;
            }

            .navbar-left button#topSidebarToggle {
                display: none;
            }

            .notification-btn {
                width: 34px;
                height: 34px;
            }

            .notification-badge {
                font-size: 7px;
                padding: 1px 5px;
            }

            .profile-btn {
                padding: 5px 7px;
                font-size: 13px;
            }

            .profile-avatar {
                width: 23px;
                height: 23px;
                font-size: 11px;
                border-radius: 50%;
            }

            .navbar-right {
                gap: 10px;
            }

            .notification-menu {
                width: calc(100vw - 28px);
                left: 0;
                transform: translateX(-63%);
            }

            .notification-header {
                padding: 10px 10px;
            }

            .notification-empty i {
                font-size: 23px;
            }

            .notification-divider {
                padding: 6px 4px;
                font-size: 13px;
            }

            .notification-item {
                gap: 8px;
                padding: 8px;
            }

            .notification-icon {
                width: 30px;
                height: 30px;
            }

            .notification-message {
                font-size: 11PX;
            }

            .notification-time {
                font-size: 10PX;
            }

            .notification-footer a {
                font-size: 12px !important;
            }

            .profile-menu {
                width: calc(100vw - 25px);
                left: 0;
                right: 0;
                transform: translateX(-78%);
            }

            .orders-management-container {
                padding: 10px;
            }

            .breadcrumb-current,
            .breadcrumb-link {
                font-size: 11px;
            }

            .page-title {
                font-size: 17px;
            }

            .page-subtitle {
                font-size: 12px;
            }

            .header-content {
                gap: 1rem;
            }

            .page-header {
                padding: 15px;
                margin-bottom: 15px;
            }

            .stats-grid a.stat-card {
                padding: 5px 12px;
                gap: 5px;
            }

            .stat-icon {
                display: none;
            }

            .stats-grid {
                margin-bottom: 15px;
            }

            .filter-section {
                padding: 5px;
                margin-bottom: 15px;
            }

            .search-input {
                padding: 5px 10px 5px 2.75rem;
                font-size: 12px;
            }

            .filter-form {
                gap: 7px;
            }

            .filter-select,
            input#orderDateRange,
            button.btn-filter {
                padding: 5px 10px;
                font-size: 12px;
            }

            .result-count {
                font-size: 12px;
            }

            .stat-label {
                font-size: 10px;
            }

            .empty-state {
                padding: 15px 15px;
            }

            .empty-icon {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }

            .empty-title {
                font-size: 14px;
                margin: 0 0 8px 0;
            }

            .empty-description {
                margin: 0 0 15px 0;
                font-size: 14px;
            }

            .sidebar.collapsed~.top-navbar~#mainContent {
                margin-left: 0;
            }

            .btn-primary-custom,
            .btn-drafts-custom {
                padding: 10px;
                font-size: 13px;
            }

            a.action-btn.action-btn-view {
                right: 35px;
            }

            a.action-btn.action-btn-edit {
                right: 67px;
            }

            form.d-inline.delete-form {
                right: 3px;
            }

            form.d-inline.delete-form,
            a.action-btn.action-btn-edit,
            a.action-btn.action-btn-view {
                z-index: 999;
                position: fixed;
                bottom: 7px;
                width: 30px;
                height: 30px;
                font-size: 13px;
            }

            form.d-inline.delete-form button {
                width: 30px;
                height: 30px;
                font-size: 13px;
            }

            .orders-table-card {
                margin-bottom: 100px;
            }

            .orders-table th,
            .orders-table td {
                padding: 7px;
                line-height: 16px;
                text-align: start;
                font-size: 12px;
                align-items: center;
            }

            .orders-table-card .th-content span {
                font-size: 12px;
                text-transform: math-auto;
            }

            .order-type-badge,
            span.status-badge.status-info {
                padding: 5px;
                border-radius: 5px;
                font-size: 10px;
            }

            .order-id-badge {
                font-size: 11px;
            }

            .creator-avatar {
                width: 25px;
                height: 25px;
                font-size: 16px;
            }

        }

        /* Product Details Column (Global) */
        .order-details-column {
            display: flex;
            flex-direction: column;
            gap: 5px;
            /* align-items: flex-start; */
            padding-left: 5px;
        }

        .badge-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px 5px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(4px);
            border: 1.5px solid;
            transition: box-shadow 0.15s;
        }

        /* The colored dot - replaces the icon circle */
        .badge-item i {
            font-size: 12px;
            flex-shrink: 0;
        }

        /*  Company - indigo/primary accent (matches theme --primary)  */
        .badge-company {
            color: #4338ca;
            border-color: rgba(99, 102, 241, 0.35);
        }

        .badge-company i {
            color: #6366f1;
        }

        /*  Ready to Ship - emerald green  */
        .badge-ready-to-ship {
            color: #065f46;
            border-color: rgba(16, 185, 129, 0.4);
        }

        .badge-ready-to-ship i {
            color: #10b981;
        }

        /*  Custom Diamond - amber  */
        .badge-custom-diamond {
            color: #78350f;
            border-color: rgba(245, 158, 11, 0.4);
        }

        .badge-custom-diamond i {
            color: #f59e0b;
        }

        /*  Custom Jewellery - violet  */
        .badge-custom-jewellery {
            color: #4c1d95;
            border-color: rgba(168, 85, 247, 0.35);
        }

        .badge-custom-jewellery i {
            color: #a855f7;
        }

        /*  Status badges - same pill anatomy  */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px 5px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(4px);
            border: 1.5px solid;
        }

        .badge-status i {
            font-size: 12px;
            flex-shrink: 0;
        }

        /* In Process - blue */
        .badge-status.status-info {
            color: #1e40af;
            border-color: rgba(59, 130, 246, 0.4);
        }

        .badge-status.status-info i {
            color: #3b82f6;
        }

        /* Shipped / Done - green */
        .badge-status.status-success {
            color: #065f46;
            border-color: rgba(16, 185, 129, 0.4);
        }

        .badge-status.status-success i {
            color: #10b981;
        }

        /* In Progress / Making - amber */
        .badge-status.status-warning {
            color: #78350f;
            border-color: rgba(245, 158, 11, 0.4);
        }

        .badge-status.status-warning i {
            color: #f59e0b;
        }

        /* Hold / Danger - red */
        .badge-status.status-danger {
            color: #9f1239;
            border-color: rgba(239, 68, 68, 0.4);
        }

        .badge-status.status-danger i {
            color: #ef4444;
        }

        /* Certificate / Purple */
        .badge-status.status-purple {
            color: #4c1d95;
            border-color: rgba(168, 85, 247, 0.35);
        }

        .badge-status.status-purple i {
            color: #a855f7;
        }

        /* Shipped dark */
        .badge-status.status-dark {
            color: #1e293b;
            border-color: rgba(30, 41, 59, 0.3);
        }

        .badge-status.status-dark i {
            color: #475569;
        }

        /* Secondary / fallback */
        .badge-status.status-secondary {
            color: #374151;
            border-color: rgba(107, 114, 128, 0.35);
        }

        .badge-status.status-secondary i {
            color: #6b7280;
        }

        /* Cyan / teal */
        .badge-status.status-cyan {
            color: #164e63;
            border-color: rgba(6, 182, 212, 0.4);
        }

        .badge-status.status-cyan i {
            color: #06b6d4;
        }


        /* SKU */
        .sku-text {
            font-family: var(--bs-font-sans-serif);
            font-size: 11px;
            color: #6c757d;
            margin-top: 4px;
            font-weight: 600;
            letter-spacing: 0.3px;
            opacity: 0.9;
        }

        .thumbnail-container {
            width: 100px;
            height: 100px;
            position: relative;
            border-radius: 8px;
            /* overflow: hidden; Removed to allow pop-out */
            margin: 0 auto;
            border: 1px solid var(--border);
            background: #fff;
            transition: transform 0.2s ease, z-index 0s;
            z-index: 1;
        }

        .thumbnail-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        .thumbnail-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            cursor: pointer;
            border-radius: 8px;
        }

        .thumbnail-container:hover {
            transform: scale(2.5);
            z-index: 1000;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .thumbnail-container:hover .thumbnail-overlay {
            opacity: 1;
        }

        .thumbnail-overlay i {
            color: white;
            font-size: 1.5rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .product-sku {
            font-size: 11px;
            font-weight: 600;
            color: var(--primary);
            margin-top: 6px;
            display: block;
            line-height: 1.2;
        }

        .product-note {
            font-size: 10px;
            color: var(--secondary);
            margin-top: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;
            max-width: 120px;
            margin-left: auto;
            margin-right: auto;
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

        /* Text Utilities */
        .text-muted {
            color: var(--gray) !important;
        }

        .d-inline {
            display: inline !important;
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

        /* Responsive Styles */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .orders-management-container {
                padding: 0rem;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
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

            .orders-table {
                min-width: 1000px;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.5rem;
            }

            .stat-icon {
                display: none;
            }

            .orders-table-card {
                overflow-x: auto;
            }

            /* Compact actions for mobile */
            .action-buttons {
                display: flex;
                gap: 0.5rem;
            }

            .shipping-info-cell {
                min-width: 120px;
            }

            /* .client-info {
                                                                                                                                                                                                                        text-align: start;
                                                                                                                                                                                                                    } */
        }

        /* Print Styles */
        @media print {

            .page-header,
            .filter-section,
            .action-buttons,
            .pagination-container,
            .btn-sync-inline {
                display: none !important;
            }

            .orders-table-card {
                box-shadow: none;
                border: 1px solid var(--border);
            }

            .orders-table {
                font-size: 0.85rem;
            }
        }
    </style>

    <!-- Date Ranege Picker -->
    @include('partials.daterangepicker-styles')

    <!-- JavaScript -->
    @push('scripts')
        <script>
            // Initialize Date Range Picker for Orders
            $(document).ready(function () {
                var startDate = $('#orderDateFrom').val() ? moment($('#orderDateFrom').val()) : null;
                var endDate = $('#orderDateTo').val() ? moment($('#orderDateTo').val()) : null;

                $('#orderDateRange').daterangepicker({
                    autoUpdateInput: false,
                    opens: 'left',
                    showDropdowns: true,
                    linkedCalendars: false,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    locale: {
                        cancelLabel: 'Clear',
                        applyLabel: 'Apply',
                        format: 'MMM D, YYYY'
                    }
                }, function (start, end, label) {
                    $('#orderDateFrom').val(start.format('YYYY-MM-DD'));
                    $('#orderDateTo').val(end.format('YYYY-MM-DD'));
                    $('#orderDateRange').val(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
                    // Auto-submit the form when date is selected
                    $('#orderFilterForm').submit();
                });

                // Set initial value if dates exist
                if (startDate && endDate) {
                    $('#orderDateRange').val(startDate.format('MMM D, YYYY') + ' - ' + endDate.format('MMM D, YYYY'));
                }

                // Clear dates on cancel and auto-submit
                $('#orderDateRange').on('cancel.daterangepicker', function (ev, picker) {
                    $(this).val('');
                    $('#orderDateFrom').val('');
                    $('#orderDateTo').val('');
                    // Auto-submit to apply the cleared filter
                    $('#orderFilterForm').submit();
                });

                // Fix z-index issue on image hover - raise the row above others
                $('.thumbnail-container').hover(
                    function () {
                        $(this).closest('.table-row').css({ 'z-index': 9999, 'position': 'relative' });
                    },
                    function () {
                        $(this).closest('.table-row').css({ 'z-index': '', 'position': '' });
                    }
                );
            });

            // Toggle Company Progress Section
            function toggleCompanyProgress() {
                const section = document.getElementById('companyProgressSection');
                const icon = document.getElementById('toggleIcon');
                if (section) {
                    if (section.style.display === 'none') {
                        section.style.display = 'block';
                        if (icon) icon.classList.add('rotated');
                    } else {
                        section.style.display = 'none';
                        if (icon) icon.classList.remove('rotated');
                    }
                }
            }

            // Make toggleCompanyProgress available globally
            window.toggleCompanyProgress = toggleCompanyProgress;

            // Dismiss overdue banner function
            function dismissOverdueBanner() {
                const banner = document.getElementById('overdueAlertBanner');
                if (banner) {
                    banner.style.animation = 'slideUp 0.3s ease-out forwards';
                    setTimeout(() => {
                        banner.style.display = 'none';
                        // Store in sessionStorage to hide for this session
                        sessionStorage.setItem('hideOverdueBanner', 'true');
                    }, 300);
                }
            }
            window.dismissOverdueBanner = dismissOverdueBanner;

            // Check if banner should be hidden on load
            document.addEventListener('DOMContentLoaded', function () {
                if (sessionStorage.getItem('hideOverdueBanner') === 'true') {
                    const banner = document.getElementById('overdueAlertBanner');
                    if (banner) banner.style.display = 'none';
                }
            });

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

            // Tracking Support Functions
            async function syncTracking(orderId, btn) {
                const icon = btn.querySelector('i');
                btn.classList.add('spinning');
                btn.disabled = true;

                try {
                    const response = await fetch(`/admin/orders/${orderId}/sync-tracking`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                            ,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Refresh current page to show updated info
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.message);
                        btn.classList.remove('spinning');
                        btn.disabled = false;
                    }
                } catch (error) {
                    console.error('Sync failed:', error);
                    alert('Failed to sync tracking data. Please try again.');
                    btn.classList.remove('spinning');
                    btn.disabled = false;
                }
            }

            function escapeHtml(str) {
                if (!str) return '';
                return String(str).replace(/[&<>"']/g, function (m) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
                });
            }

            function showTrackingHistory(orderId, trackingNumber, carrier, history, trackingUrl) {
                document.getElementById('modalTrackingNumber').textContent = trackingNumber;
                document.getElementById('modalCarrierName').textContent = carrier;

                const container = document.getElementById('trackingHistoryContainer');
                container.innerHTML = '';

                if (!history || history.length === 0) {
                    container.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-info-circle me-2"></i>No journey history available yet. Click sync to update.</div>';
                    document.getElementById('modalLatestStatus').textContent = 'No Data';
                } else {
                    document.getElementById('modalLatestStatus').textContent = history[0].status;

                    history.forEach(item => {
                        const historyItem = document.createElement('div');
                        historyItem.className = 'tracking-history-item';
                        historyItem.innerHTML = `
                                            <div class="tracking-history-dot"></div>
                                            <div class="tracking-history-details shadow-sm">
                                                <div class="tracking-history-header">
                                                    <span class="tracking-history-status">${escapeHtml(item.status)}</span>
                                                    <span class="tracking-history-date">${escapeHtml(item.date)}</span>
                                                </div>
                                                <div class="tracking-history-location">
                                                    <i class="bi bi-geo-alt-fill"></i> ${escapeHtml(item.location)}
                                                </div>
                                                ${item.description ? `<div class="tracking-history-desc">${escapeHtml(item.description)}</div>` : ''}
                                            </div>`;
                        container.appendChild(historyItem);
                    });
                }

                const officialLink = document.getElementById('modalOfficialLink');
                if (trackingUrl && trackingUrl !== 'null' && trackingUrl !== '') {
                    officialLink.href = trackingUrl;
                    officialLink.style.display = 'inline-block';
                } else {
                    officialLink.href = `https://www.google.com/search?q=${encodeURIComponent(carrier)}+tracking+${encodeURIComponent(trackingNumber)}`;
                    officialLink.style.display = 'inline-block';
                }
                // Setup Sync Button
                const syncBtn = document.getElementById('modalSyncBtn');
                if (syncBtn) {
                    // Creating a fresh onClick handler that captures current orderId
                    syncBtn.onclick = function () {
                        syncTracking(orderId, this);
                    };
                }

                const modal = new bootstrap.Modal(document.getElementById('trackingHistoryModal'));
                modal.show();
            }

            // Cancel Order Modal Logic
            function openCancelModal(orderId, clientName) {
                document.getElementById('cancelOrderId').value = orderId;
                document.getElementById('cancelClientName').textContent = clientName;
                document.getElementById('cancelOrderForm').action = `/admin/orders/${orderId}/cancel`;
                const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
                modal.show();
            }
        </script>
    @endpush

    <!-- Cancel Order Modal -->
    <div class="modal fade border-0" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="cancelOrderForm" method="POST">
                    @csrf
                    <input type="hidden" id="cancelOrderId" name="order_id" value="">

                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title" id="cancelOrderModalLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Cancel Order
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="mb-3">Are you sure you want to cancel the order for <strong><span
                                    id="cancelClientName"></span></strong>?</p>
                        <div class="alert alert-danger py-2 d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <span class="small">This will return the associated diamond SKU(s) and melee stock to
                                inventory.</span>
                        </div>

                        <div class="form-group mb-0">
                            <label for="cancel_reason" class="form-label fw-bold small text-muted text-uppercase">Reason for
                                Cancellation <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" required
                                placeholder="Please provide a reason..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger px-4 py-2 custom-shadow">
                            <i class="bi bi-x-circle me-1"></i> Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection