@extends('layouts.admin')

@section('title', 'Jewellery Stock')

@section('content')
    @php
        $currentAdmin = auth()->guard('admin')->user();
        $canViewPricing =
            $currentAdmin && ($currentAdmin->is_super || $currentAdmin->hasPermission('jewellery_stock.view_pricing'));
    @endphp

    <div class="tracker-page">
        {{-- Page Header --}}
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Jewellery Stock</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-gem" style="color: #8b5cf6;"></i>
                        Jewellery Stock
                    </h1>
                    <p class="page-subtitle">Manage rings, earrings, tennis bracelets and other jewellery inventory</p>
                </div>
                <div class="header-right">
                    @if (auth()->guard('admin')->user() &&
                            auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.create']))
                        <a href="{{ route('jewellery-stock.create') }}" class="btn-primary-custom">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add Jewellery</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats Cards (Super Admin Only) --}}
        @if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->is_super)
            <div class="stats-grid">
                <div class="stat-card" style="border-left-color: #8b5cf6;">
                    <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Items</div>
                        <div class="stat-value" style="color: #8b5cf6;">{{ number_format($totalItems ?? 0) }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">In Stock</div>
                        <div class="stat-value">{{ number_format($inStockCount ?? 0) }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Low Stock</div>
                        <div class="stat-value">{{ number_format($lowStockCount ?? 0) }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Out of Stock</div>
                        <div class="stat-value">{{ number_format($outOfStockCount ?? 0) }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-info">
                    <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Total Value</div>
                        <div class="stat-value">${{ number_format($totalValue ?? 0, 0) }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filter Section --}}
        <div class="tracker-filter">
            <form method="GET" action="{{ route('jewellery-stock.index') }}" class="tracker-filter-form"
                id="jewelleryFilterForm">
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-upc-scan"></i> SKU</label>
                    <input type="text" name="sku" class="tracker-filter-input" placeholder="Search SKU..."
                        value="{{ request('sku') }}">
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-tag"></i> Name</label>
                    <input type="text" name="name" class="tracker-filter-input" placeholder="Search name..."
                        value="{{ request('name') }}">
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-collection"></i> Type</label>
                    <select name="type" class="tracker-filter-select">
                        <option value="">All Types</option>
                        <option value="ring" {{ request('type') == 'ring' ? 'selected' : '' }}>Ring</option>
                        <option value="earrings" {{ request('type') == 'earrings' ? 'selected' : '' }}>Earrings</option>
                        <option value="tennis_bracelet" {{ request('type') == 'tennis_bracelet' ? 'selected' : '' }}>Tennis
                            Bracelet</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-palette"></i> Metal</label>
                    <select name="metal_type_id" class="tracker-filter-select">
                        <option value="">All Metals</option>
                        @foreach ($metalTypes as $metal)
                            <option value="{{ $metal->id }}"
                                {{ request('metal_type_id') == $metal->id ? 'selected' : '' }}>
                                {{ $metal->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-circle"></i> Status</label>
                    <select name="status" class="tracker-filter-select">
                        <option value="">All Statuses</option>
                        <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock
                        </option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of
                            Stock</option>
                    </select>
                </div>
                <div class="tracker-filter-actions">
                    <span class="tracker-result-count">
                        <i class="bi bi-info-circle"></i>
                        <strong>{{ $items->total() }}</strong> item{{ $items->total() !== 1 ? 's' : '' }}
                    </span>
                    <a href="{{ route('jewellery-stock.index') }}" class="btn-tracker-reset">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </a>
                    <button type="submit" class="btn-tracker-apply">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="tracker-table-card">
            <div class="table-responsive">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-upc-scan"></i> SKU</th>
                            <th><i class="bi bi-tag"></i> Name</th>
                            <th><i class="bi bi-collection"></i> Type</th>
                            <th><i class="bi bi-palette"></i> Metal</th>
                            <th><i class="bi bi-vinyl"></i> Ring Size</th>
                            <th><i class="bi bi-speedometer"></i> Weight</th>
                            <th><i class="bi bi-box-seam"></i> Qty</th>
                            <th><i class="bi bi-cash"></i> Purchase</th>
                            <th><i class="bi bi-cash-stack"></i> Selling</th>
                            <th><i class="bi bi-circle"></i> Status</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <span class="tracker-badge"
                                        style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                                        {{ $item->sku }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        style="font-weight: 600; color: var(--dark, #1e293b);">{{ $item->name }}</span>
                                </td>
                                <td>
                                    <span class="tracker-badge"
                                        style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                                        {{ ucwords(str_replace('_', ' ', $item->type)) }}
                                    </span>
                                </td>
                                <td>{{ $item->metalType->name ?? '—' }}</td>
                                <td>{{ $item->ringSize->name ?? '—' }}</td>
                                <td>{{ number_format($item->weight, 3) }} g</td>
                                <td style="font-weight: 700;">{{ $item->quantity }}</td>
                                <td>
                                    @if ($canViewPricing)
                                        <strong
                                            style="color: #10b981;">${{ number_format($item->purchase_price, 2) }}</strong>
                                    @else
                                        <span class="text-muted" title="Restricted">Restricted</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="color: #6366f1;">${{ number_format($item->selling_price, 2) }}</strong>
                                </td>
                                <td>
                                    @if ($item->status === 'in_stock')
                                        <span class="tracker-badge"
                                            style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                            <i class="bi bi-check-circle"></i> In Stock
                                        </span>
                                    @elseif ($item->status === 'low_stock')
                                        <span class="tracker-badge"
                                            style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                            <i class="bi bi-exclamation-triangle"></i> Low Stock
                                        </span>
                                    @else
                                        <span class="tracker-badge"
                                            style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                            <i class="bi bi-x-circle"></i> Out of Stock
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tracker-actions">
                                        @if (auth()->guard('admin')->user() &&
                                                auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.view']))
                                            <a href="{{ route('jewellery-stock.show', $item) }}"
                                                class="tracker-action-btn tracker-action-view" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                        @if (auth()->guard('admin')->user() &&
                                                auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.edit']))
                                            <a href="{{ route('jewellery-stock.edit', $item) }}"
                                                class="tracker-action-btn tracker-action-edit" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if (auth()->guard('admin')->user() &&
                                                auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.delete']))
                                            <form action="{{ route('jewellery-stock.destroy', $item) }}" method="POST"
                                                style="display:inline" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="tracker-action-btn tracker-action-delete delete-btn"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11">
                                    <div class="tracker-empty">
                                        <div class="tracker-empty-icon"><i class="bi bi-inbox"></i></div>
                                        <h3 class="tracker-empty-title">No jewellery items found</h3>
                                        <p class="tracker-empty-desc">Start by adding your first jewellery item to the
                                            inventory</p>
                                        @if (auth()->guard('admin')->user() &&
                                                auth()->guard('admin')->user()->canAccessAny(['jewellery_stock.create']))
                                            <a href="{{ route('jewellery-stock.create') }}" class="btn-primary-custom">
                                                <i class="bi bi-plus-circle"></i> Add First Item
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($items->hasPages())
            <div class="pagination-container">
                {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-submit on select changes
                document.querySelectorAll('#jewelleryFilterForm select').forEach(function(el) {
                    el.addEventListener('change', function() {
                        document.getElementById('jewelleryFilterForm').submit();
                    });
                });

                // Delete confirmation
                document.querySelectorAll('.delete-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        if (confirm('Are you sure you want to delete this jewellery item?')) {
                            this.closest('.delete-form').submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
