@extends('layouts.admin')

@section('title', 'Invoices Management')

@section('content')
    <div class="diamond-management-container tracker-page invoice-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Invoices</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-receipt"></i>
                        Invoices Management
                    </h1>
                    <p class="page-subtitle">Manage and track all invoices and billing information</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('invoices.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Invoice</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Invoices</div>
                    <div class="stat-value">{{ $invoices->total() }}</div>
                    <div class="stat-trend">
                        <i class="bi bi-graph-up"></i> All invoices
                    </div>
                </div>
            </div>

            <!-- Region Stats Cards -->
            @foreach(\App\Models\Invoice::REGIONS as $regionCode => $regionData)
                @php
                    $regionStat = $regionStats[$regionCode] ?? null;
                    $count = $regionStat->count ?? 0;
                    $total = $regionStat->total ?? 0;
                @endphp
                <a href="{{ route('invoices.index', ['region' => $regionCode]) }}"
                    class="stat-card {{ request('region') == $regionCode ? 'stat-card-active' : 'stat-card-secondary' }}"
                    style="text-decoration: none; cursor: pointer;">
                    <div class="stat-icon">
                        <span style="font-size: 1.5rem;">{{ $regionData['flag'] }}</span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">{{ $regionData['name'] }}</div>
                        <div class="stat-value">{{ $count }}</div>
                        <div class="stat-trend">
                            {{ $regionData['symbol'] }} {{ number_format($total, 0) }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Filter Section -->
        <div class="tracker-filter">
            <form method="GET" action="{{ route('invoices.index') }}" class="tracker-filter-form">
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-search"></i> Search</label>
                    <input type="text" name="search" class="tracker-filter-input"
                        placeholder="Search by invoice no or company..." value="{{ request('search') }}">
                </div>

                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-flag"></i> Status</label>
                    <select name="status" class="tracker-filter-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                        <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-globe"></i> Region</label>
                    <select name="region" class="tracker-filter-select">
                        <option value="">All Regions</option>
                        @foreach(\App\Models\Invoice::REGIONS as $code => $data)
                            <option value="{{ $code }}" {{ request('region') == $code ? 'selected' : '' }}>
                                {{ $data['flag'] }} {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="tracker-filter-actions">
                    <span class="tracker-result-count">
                        <i class="bi bi-info-circle"></i>
                        Showing {{ $invoices->firstItem() ?? 0 }} to {{ $invoices->lastItem() ?? 0 }} of
                        {{ $invoices->total() }}
                    </span>
                    @if(request('search') || request('status') || request('region'))
                        <a href="{{ route('invoices.index') }}" class="btn-tracker-reset">
                            <i class="bi bi-arrow-counterclockwise"></i> Clear
                        </a>
                    @endif
                    <button type="submit" class="btn-tracker-apply">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Invoices Table -->
        <div class="tracker-table-card">
            <div class="table-responsive">
                @if($invoices->count() > 0)
                    <table class="tracker-table">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> ID</th>
                                <th><i class="bi bi-file-text"></i> Invoice No</th>
                                <th><i class="bi bi-building"></i> Company</th>
                                <th><i class="bi bi-calendar-event"></i> Date</th>
                                <th><i class="bi bi-currency-dollar"></i> Total</th>
                                <th><i class="bi bi-globe"></i> Region</th>
                                <th><i class="bi bi-flag"></i> Status</th>
                                <th><i class="bi bi-gear"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                                <tr class="table-row">
                                    <td class="td-id">
                                        <span class="order-id-badge">#{{ $inv->id }}</span>
                                    </td>
                                    <td>
                                        <span class="invoice-no">{{ $inv->invoice_no }}</span>
                                    </td>
                                    <td>
                                        <span class="company-name">{{ $inv->company->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        @if($inv->invoice_date)
                                            <div class="date-info">
                                                <span
                                                    class="date-main">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('M d, Y') }}</span>
                                                <span
                                                    class="date-time">{{ \Carbon\Carbon::parse($inv->invoice_date)->format('l') }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="amount-value">
                                            {{ \App\Models\Invoice::REGIONS[$inv->invoice_region]['symbol'] ?? ($inv->company->currency_symbol ?? '$') }}
                                            {{ number_format($inv->total_invoice_value, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($inv->invoice_region && isset(\App\Models\Invoice::REGIONS[$inv->invoice_region]))
                                            @php $regionInfo = \App\Models\Invoice::REGIONS[$inv->invoice_region]; @endphp
                                            <span class="region-badge" title="{{ $regionInfo['name'] }}">
                                                <span class="region-flag">{{ $regionInfo['flag'] }}</span>
                                                <span class="region-code">{{ $inv->invoice_region }}</span>
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inv->status == 'draft')
                                            <span class="tracker-badge tracker-badge-warning">
                                                <i class="bi bi-pencil-square"></i>
                                                Draft
                                            </span>
                                        @elseif($inv->status == 'done')
                                            <span class="tracker-badge tracker-badge-success">
                                                <i class="bi bi-check-circle"></i>
                                                Done
                                            </span>
                                        @elseif($inv->status == 'final')
                                            <span class="tracker-badge tracker-badge-success">
                                                <i class="bi bi-check-circle"></i>
                                                Final
                                            </span>
                                        @elseif($inv->status == 'cancelled')
                                            <span class="tracker-badge tracker-badge-danger">
                                                <i class="bi bi-x-circle"></i>
                                                Cancelled
                                            </span>
                                        @else
                                            <span class="tracker-badge tracker-badge-secondary">
                                                <i class="bi bi-circle"></i>
                                                {{ ucfirst($inv->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="tracker-actions">
                                            <a href="{{ route('invoices.show', $inv->id) }}"
                                                class="tracker-action-btn tracker-action-view" title="View Invoice">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('invoices.edit', $inv->id) }}"
                                                class="tracker-action-btn tracker-action-edit" title="Edit Invoice">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('invoices.pdf', $inv->id) }}"
                                                class="tracker-action-btn tracker-action-success" title="Download PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h3 class="empty-title">No invoices found</h3>
                        <p class="empty-description">
                            @if(request('search') || request('status'))
                                No invoices match your search criteria. Try adjusting your filters.
                            @else
                                Get started by creating your first invoice.
                            @endif
                        </p>
                        @if(request('search') || request('status'))
                            <a href="{{ route('invoices.index') }}" class="btn-primary-custom">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset Filters
                            </a>
                        @else
                            <a href="{{ route('invoices.create') }}" class="btn-primary-custom">
                                <i class="bi bi-plus-circle"></i>
                                Create First Invoice
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="pagination-container">
                    {{ $invoices->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>



@endsection