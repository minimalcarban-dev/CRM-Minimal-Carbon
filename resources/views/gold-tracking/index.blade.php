@extends('layouts.admin')

@section('title', 'Gold Tracking')

@section('content')
    <div class="diamond-management-container tracker-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Gold Tracking</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-coin" style="color: #f59e0b;"></i>
                        Gold Tracking
                    </h1>
                    <p class="page-subtitle">Manage gold purchases, stock and factory distribution</p>
                </div>
                <div class="header-right">
                    <div class="tracker-actions-stack">
                        <a href="{{ route('gold-tracking.purchases.create') }}" class="btn-primary-custom">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add Purchase</span>
                        </a>
                        <div class="tracker-actions-row">
                            <a href="{{ route('gold-tracking.distribute') }}" class="btn-secondary-custom">
                                <i class="bi bi-arrow-right-circle"></i> Distribute
                            </a>
                            <a href="{{ route('gold-tracking.return') }}" class="btn-secondary-custom">
                                <i class="bi bi-arrow-left-circle"></i> Return
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Owner Stock</div>
                    <div class="stat-value" style="color: #f59e0b;">{{ number_format($ownerStock, 3) }} gm</div>
                </div>
            </div>
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-buildings"></i></div>
                <div class="stat-content">
                    <div class="stat-label">In Factories</div>
                    <div class="stat-value">{{ number_format($inFactories, 3) }} gm</div>
                </div>
            </div>
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Value</div>
                    <div class="stat-value">₹{{ number_format($totalValue, 0) }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="bi bi-calendar-month"></i></div>
                <div class="stat-content">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">{{ number_format($thisMonth['weight'], 3) }} gm</div>
                </div>
            </div>
        </div>

        <!-- Factory Stock Cards -->
        @if($factories->count() > 0)
        <div class="gold-factory-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            @foreach($factories as $factory)
            <div class="gold-factory-card" style="background: white; border-radius: 12px; padding: 1rem; text-align: center; border: 2px solid #e2e8f0; transition: all 0.3s;">
                <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">{{ $factory->name }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: {{ $factory->gold_stock > 0 ? '#f59e0b' : '#94a3b8' }};">
                    {{ number_format($factory->gold_stock, 1) }} gm
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Filter Section -->
        <div class="tracker-filter">
            <form method="GET" action="{{ route('gold-tracking.index') }}" class="tracker-filter-form" id="goldFilterForm">
                <div class="tracker-filter-field date-range-field">
                    <label class="tracker-filter-label"><i class="bi bi-calendar-range"></i> Date Range</label>
                    <div class="date-range-wrapper">
                        <input type="text" id="goldDateRange" class="date-range-input" placeholder="Select Date Range" readonly>
                        <input type="hidden" name="from_date" id="goldDateFrom" value="{{ request('from_date') }}">
                        <input type="hidden" name="to_date" id="goldDateTo" value="{{ request('to_date') }}">
                    </div>
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-funnel"></i> Type</label>
                    <select name="type" class="tracker-filter-select">
                        <option value="">All</option>
                        <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                        <option value="distribute" {{ request('type') == 'distribute' ? 'selected' : '' }}>Distribute</option>
                        <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Return</option>
                    </select>
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-buildings"></i> Factory</label>
                    <select name="factory_id" class="tracker-filter-select">
                        <option value="">All Factories</option>
                        @foreach($factories as $factory)
                            <option value="{{ $factory->id }}" {{ request('factory_id') == $factory->id ? 'selected' : '' }}>
                                {{ $factory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="tracker-filter-actions">
                    <span class="tracker-result-count">
                        <i class="bi bi-info-circle"></i>
                        <strong>{{ $transactions->count() }}</strong> items
                    </span>
                    <a href="{{ route('gold-tracking.index') }}" class="btn-tracker-reset">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </a>
                    <button type="submit" class="btn-tracker-apply">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="tracker-table-card">
            <div class="table-responsive">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-calendar"></i> Date</th>
                            <th><i class="bi bi-tag"></i> Type</th>
                            <th><i class="bi bi-gem"></i> Weight</th>
                            <th><i class="bi bi-person"></i> From/To</th>
                            <th><i class="bi bi-currency-rupee"></i> Amount</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $prevDate = null; $dateHasPurchase = false; @endphp
                        @forelse($transactions as $txn)
                            @php 
                                $currentDate = $txn['date']->format('Y-m-d');
                                $isDistribution = in_array($txn['type'], ['distribute', 'return']);
                                $showDateSeparator = $prevDate !== null && $prevDate !== $currentDate;
                                
                                // Reset purchase flag when date changes
                                if ($showDateSeparator) {
                                    $dateHasPurchase = false;
                                }
                                
                                // If this is a purchase, mark date as having purchase
                                if ($txn['type'] === 'purchase') {
                                    $dateHasPurchase = true;
                                }
                                
                                // Sub-entry styling: if this is distribution AND there's a purchase on same date (before this)
                                $isSubEntry = $isDistribution && $dateHasPurchase;
                            @endphp
                            
                            @if($showDateSeparator)
                                {{-- Date separator row for visual grouping --}}
                                <tr class="tracker-date-separator">
                                    <td colspan="6" style="padding: 8px 0; background: #f8fafc;">
                                        <div style="height: 1px; background: linear-gradient(to right, transparent, #e2e8f0, transparent);"></div>
                                    </td>
                                </tr>
                            @endif
                            
                            <tr class="{{ $txn['type'] === 'purchase' ? 'tracker-income-row' : ($txn['type'] === 'return' ? '' : 'tracker-expense-row') }} {{ $isSubEntry ? 'tracker-sub-entry' : '' }}">
                                <td>
                                    @if($isSubEntry)
                                        <span style="color: #94a3b8; padding-left: 20px; display: flex; align-items: center; gap: 6px;">
                                            <span style="color: #cbd5e1;">└</span>
                                            {{ $txn['date']->format('d-M-Y') }}
                                        </span>
                                    @else
                                        {{ $txn['date']->format('d-M-Y') }}
                                    @endif
                                </td>
                                <td>
                                    @if($txn['type'] === 'purchase')
                                        <span class="tracker-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                            <i class="bi bi-plus-circle"></i> PURCHASE
                                        </span>
                                    @elseif($txn['type'] === 'distribute')
                                        <span class="tracker-badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                            <i class="bi bi-arrow-right"></i> DISTRIBUTE
                                        </span>
                                    @else
                                        <span class="tracker-badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                                            <i class="bi bi-arrow-left"></i> RETURN
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($txn['type'] === 'purchase')
                                        <span style="color: #10b981; font-weight: 600;">+{{ number_format($txn['weight'], 3) }} gm</span>
                                    @elseif($txn['type'] === 'distribute')
                                        <span style="color: #ef4444; font-weight: 600;">-{{ number_format($txn['weight'], 3) }} gm</span>
                                    @else
                                        <span style="color: #8b5cf6; font-weight: 600;">+{{ number_format($txn['weight'], 3) }} gm</span>
                                    @endif
                                </td>
                                <td>
                                    @if($txn['type'] === 'purchase')
                                        {{ $txn['from_to'] }}
                                    @elseif($txn['type'] === 'distribute')
                                        → {{ $txn['from_to'] }}
                                    @else
                                        ← {{ $txn['from_to'] }}
                                    @endif
                                </td>
                                <td>
                                    @if($txn['amount'])
                                        <strong style="color: #10b981;">₹{{ number_format($txn['amount'], 0) }}</strong>
                                    @else
                                        <span style="color: #94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tracker-actions">
                                        @if($txn['type'] === 'purchase')
                                            @if($txn['status'] === 'pending')
                                                <button type="button" class="tracker-action-btn" style="background: #fef3c7; color: #b45309;" title="Complete Payment" onclick="openCompleteModal({{ $txn['id'] }})">
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('gold-tracking.purchases.show', $txn['id']) }}" class="tracker-action-btn tracker-action-view" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('gold-tracking.purchases.edit', $txn['id']) }}" class="tracker-action-btn tracker-action-edit" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('gold-tracking.purchases.destroy', $txn['id']) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this purchase?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="tracker-action-btn tracker-action-delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @php 
                                $prevDate = $currentDate;
                            @endphp
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="tracker-empty">
                                        <div class="tracker-empty-icon"><i class="bi bi-inbox"></i></div>
                                        <h3 class="tracker-empty-title">No transactions found</h3>
                                        <p class="tracker-empty-desc">Start by adding your first gold purchase</p>
                                        <a href="{{ route('gold-tracking.purchases.create') }}" class="btn-primary-custom">
                                            <i class="bi bi-plus-circle"></i> Add Purchase
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Date Range Picker Styles --}}
    @include('partials.daterangepicker-styles')

    @push('scripts')
        <script>
            $(document).ready(function () {
                var startDate = $('#goldDateFrom').val() ? moment($('#goldDateFrom').val()) : null;
                var endDate = $('#goldDateTo').val() ? moment($('#goldDateTo').val()) : null;

                $('#goldDateRange').daterangepicker({
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
                    $('#goldDateFrom').val(start.format('YYYY-MM-DD'));
                    $('#goldDateTo').val(end.format('YYYY-MM-DD'));
                    $('#goldDateRange').val(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
                    $('#goldFilterForm').submit();
                });

                if (startDate && endDate) {
                    $('#goldDateRange').val(startDate.format('MMM D, YYYY') + ' - ' + endDate.format('MMM D, YYYY'));
                }

                $('#goldDateRange').on('cancel.daterangepicker', function (ev, picker) {
                    $(this).val('');
                    $('#goldDateFrom').val('');
                    $('#goldDateTo').val('');
                    $('#goldFilterForm').submit();
                });

                $('select[name="type"], select[name="factory_id"]').on('change', function () {
                    $('#goldFilterForm').submit();
                });
            });

            function openCompleteModal(id) {
                // Simple confirm for now - can be enhanced with modal later
                if (confirm('Complete this purchase with payment details?')) {
                    window.location.href = `/admin/gold-tracking/purchases/${id}/edit`;
                }
            }
        </script>
    @endpush
@endsection
