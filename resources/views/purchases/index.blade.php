@extends('layouts.admin')

@section('title', 'Purchase Tracker')

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
                        <span class="breadcrumb-current">Purchase Tracker</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-cart-check"></i>
                        Diamond Purchases
                    </h1>
                    <p class="page-subtitle">Track all your diamond purchases</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('purchases.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Purchase</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon"><i class="bi bi-cart"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Purchases</div>
                    <div class="stat-value">{{ $totalPurchases }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-warning">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value" style="color: #f59e0b;">{{ $pendingPurchases }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-success">
                <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                <div class="stat-content">
                    <div class="stat-label">Total Amount</div>
                    <div class="stat-value">₹{{ number_format($totalAmount, 0) }}</div>
                </div>
            </div>
            <div class="stat-card stat-card-info">
                <div class="stat-icon"><i class="bi bi-calendar-month"></i></div>
                <div class="stat-content">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">₹{{ number_format($thisMonthAmount, 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="tracker-filter">
            <form method="GET" action="{{ route('purchases.index') }}" class="tracker-filter-form" id="purchaseFilterForm">
                <div class="tracker-filter-field date-range-field">
                    <label class="tracker-filter-label"><i class="bi bi-calendar-range"></i> Date Range</label>
                    <div class="date-range-wrapper">
                        <input type="text" id="purchaseDateRange" class="date-range-input" placeholder="Select Date Range"
                            readonly>
                        <input type="hidden" name="from_date" id="purchaseDateFrom" value="{{ request('from_date') }}">
                        <input type="hidden" name="to_date" id="purchaseDateTo" value="{{ request('to_date') }}">
                    </div>
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-gem"></i> Diamond Type</label>
                    <input type="text" name="diamond_type" class="tracker-filter-input" placeholder="Search..."
                        value="{{ request('diamond_type') }}">
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-credit-card"></i> Payment</label>
                    <select name="payment_mode" class="tracker-filter-select">
                        <option value="">All</option>
                        <option value="upi" {{ request('payment_mode') == 'upi' ? 'selected' : '' }}>UPI</option>
                        <option value="cash" {{ request('payment_mode') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ request('payment_mode') == 'bank_transfer' ? 'selected' : '' }}>Bank
                            Transfer</option>
                    </select>
                </div>
                <div class="tracker-filter-field">
                    <label class="tracker-filter-label"><i class="bi bi-flag"></i> Status</label>
                    <select name="status" class="tracker-filter-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="tracker-filter-actions">
                    <span class="tracker-result-count">
                        <i class="bi bi-info-circle"></i>
                        <strong>{{ $purchases->count() }}</strong> items
                    </span>
                    <a href="{{ route('purchases.index') }}" class="btn-tracker-reset">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </a>
                    <button type="submit" class="btn-tracker-apply">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="tracker-table-card">
            <div class="table-responsive">
                <table class="tracker-table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-flag"></i> Status</th>
                            <th><i class="bi bi-calendar"></i> Date</th>
                            <th><i class="bi bi-gem"></i> Diamond Type</th>
                            <th>Weight</th>
                            <th>₹ Per CT</th>
                            <th>% Discount</th>
                            <th>₹ Total</th>
                            <th><i class="bi bi-credit-card"></i> Payment</th>
                            <th><i class="bi bi-person"></i> Party</th>
                            <th><i class="bi bi-gear"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>
                                    @if($purchase->isPending())
                                        <span class="tracker-badge" style="background: #fef3c7; color: #b45309;"><i
                                                class="bi bi-hourglass-split"></i> Pending</span>
                                    @else
                                        <span class="tracker-badge" style="background: #d1fae5; color: #065f46;"><i
                                                class="bi bi-check-circle"></i> Completed</span>
                                    @endif
                                </td>
                                <td>{{ $purchase->purchase_date->format('d-M-Y') }}</td>
                                <td><span class="tracker-badge tracker-badge-primary">{{ $purchase->diamond_type }}</span></td>
                                <td>{{ number_format($purchase->weight, 2) }} ct</td>
                                <td>₹{{ number_format($purchase->per_ct_price, 0) }}</td>
                                <td>{{ $purchase->discount_percent }}%</td>
                                <td><strong style="color: #10b981;">₹{{ number_format($purchase->total_price, 0) }}</strong>
                                </td>
                                <td>
                                    @if($purchase->payment_mode)
                                        <span
                                            class="tracker-badge {{ $purchase->payment_mode == 'upi' ? 'tracker-badge-info' : ($purchase->payment_mode == 'bank_transfer' ? 'tracker-badge-primary' : 'tracker-badge-secondary') }}">
                                            {{ $purchase->payment_mode_label }}
                                        </span>
                                    @else
                                        <span class="tracker-badge" style="background: #f3f4f6; color: #6b7280;">—</span>
                                    @endif
                                </td>
                                <td>{{ $purchase->party_name }}</td>
                                <td>
                                    <div class="tracker-actions">
                                        @if($purchase->isPending())
                                            <button type="button" class="tracker-action-btn"
                                                style="background: #fef3c7; color: #b45309;" title="Complete Payment"
                                                onclick="openCompleteModal({{ $purchase->id }})">
                                                <i class="bi bi-check2-circle"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('purchases.show', $purchase) }}"
                                            class="tracker-action-btn tracker-action-view" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('purchases.edit', $purchase) }}"
                                            class="tracker-action-btn tracker-action-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('purchases.destroy', $purchase) }}" method="POST"
                                            style="display:inline" onsubmit="return confirm('Delete this purchase?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tracker-action-btn tracker-action-delete"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="tracker-empty">
                                        <div class="tracker-empty-icon"><i class="bi bi-inbox"></i></div>
                                        <h3 class="tracker-empty-title">No purchases found</h3>
                                        <p class="tracker-empty-desc">Start by adding your first purchase</p>
                                        <a href="{{ route('purchases.create') }}" class="btn-primary-custom">
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

        @if($purchases->hasPages())
            <div class="pagination-container">
                {{ $purchases->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Date Range Picker Styles --}}
    @include('partials.daterangepicker-styles')

    @push('scripts')
        <script>
            $(document).ready(function () {
                var startDate = $('#purchaseDateFrom').val() ? moment($('#purchaseDateFrom').val()) : null;
                var endDate = $('#purchaseDateTo').val() ? moment($('#purchaseDateTo').val()) : null;

                $('#purchaseDateRange').daterangepicker({
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
                    $('#purchaseDateFrom').val(start.format('YYYY-MM-DD'));
                    $('#purchaseDateTo').val(end.format('YYYY-MM-DD'));
                    $('#purchaseDateRange').val(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
                    // Auto-submit the form when date is selected
                    $('#purchaseFilterForm').submit();
                });

                // Set initial value if dates exist
                if (startDate && endDate) {
                    $('#purchaseDateRange').val(startDate.format('MMM D, YYYY') + ' - ' + endDate.format('MMM D, YYYY'));
                }

                // Clear dates on cancel and auto-submit
                $('#purchaseDateRange').on('cancel.daterangepicker', function (ev, picker) {
                    $(this).val('');
                    $('#purchaseDateFrom').val('');
                    $('#purchaseDateTo').val('');
                    // Auto-submit to apply the cleared filter
                    $('#purchaseFilterForm').submit();
                });

                // Auto-submit on payment mode or status change
                $('select[name="payment_mode"], select[name="status"]').on('change', function () {
                    $('#purchaseFilterForm').submit();
                });
            });

            // Complete Payment Modal Functions
            let currentPurchaseId = null;

            function openCompleteModal(purchaseId) {
                currentPurchaseId = purchaseId;
                document.getElementById('completeModal').style.display = 'flex';
                document.getElementById('completeForm').action = `/admin/purchases/${purchaseId}/complete`;
                // Reset form
                document.getElementById('completeForm').reset();
                toggleBankFields();
            }

            function closeCompleteModal() {
                document.getElementById('completeModal').style.display = 'none';
                currentPurchaseId = null;
            }

            function toggleBankFields() {
                const paymentMode = document.querySelector('input[name="complete_payment_mode"]:checked')?.value;
                document.getElementById('upiFieldComplete').style.display = paymentMode === 'upi' ? 'block' : 'none';
                document.getElementById('bankFieldsComplete').style.display = paymentMode === 'bank_transfer' ? 'block' : 'none';
            }

            // Close modal on outside click
            document.getElementById('completeModal')?.addEventListener('click', function (e) {
                if (e.target === this) closeCompleteModal();
            });
        </script>
    @endpush

    {{-- Complete Payment Modal --}}
    <div id="completeModal" class="modal-overlay"
        style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div class="modal-content"
            style="background: white; border-radius: 16px; width: 100%; max-width: 500px; margin: 1rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
            <div style="padding: 1.5rem; border-bottom: 2px solid #e5e7eb;">
                <h3 style="margin: 0; font-size: 1.25rem; color: #1f2937;"><i class="bi bi-check2-circle"
                        style="color: #f59e0b;"></i> Complete Purchase Payment</h3>
            </div>
            <form id="completeForm" method="POST" action="">
                @csrf
                <div style="padding: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.75rem; font-weight: 600; color: #374151;">Payment
                            Mode <span style="color: #ef4444;">*</span></label>
                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                            <label style="cursor: pointer;">
                                <input type="radio" name="complete_payment_mode" value="upi"
                                    onchange="toggleBankFields(); document.querySelector('input[name=payment_mode]').value='upi';"
                                    required style="display: none;">
                                <span class="modal-toggle-btn"><i class="bi bi-phone"></i> UPI</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="complete_payment_mode" value="cash"
                                    onchange="toggleBankFields(); document.querySelector('input[name=payment_mode]').value='cash';"
                                    style="display: none;">
                                <span class="modal-toggle-btn"><i class="bi bi-cash"></i> Cash</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="complete_payment_mode" value="bank_transfer"
                                    onchange="toggleBankFields(); document.querySelector('input[name=payment_mode]').value='bank_transfer';"
                                    style="display: none;">
                                <span class="modal-toggle-btn"><i class="bi bi-bank"></i> Bank Transfer</span>
                            </label>
                        </div>
                        <input type="hidden" name="payment_mode" value="">
                    </div>

                    <div id="upiFieldComplete" style="display: none; margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">UPI
                            ID</label>
                        <input type="text" name="upi_id" class="form-control" placeholder="example@upi"
                            style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;">
                    </div>

                    <div id="bankFieldsComplete" style="display: none;">
                        <div style="display: grid; gap: 1rem;">
                            <div>
                                <label
                                    style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Account
                                    Holder Name</label>
                                <input type="text" name="bank_account_name" class="form-control"
                                    placeholder="Account holder name"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Bank
                                    Name</label>
                                <input type="text" name="bank_name" class="form-control" placeholder="Bank name"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;">
                            </div>
                            <div>
                                <label
                                    style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Account
                                    Number</label>
                                <input type="text" name="bank_account_number" class="form-control"
                                    placeholder="Account number"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">IFSC
                                    Code</label>
                                <input type="text" name="bank_ifsc" class="form-control" placeholder="IFSC code"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    style="padding: 1rem 1.5rem; border-top: 2px solid #e5e7eb; display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="closeCompleteModal()"
                        style="padding: 0.75rem 1.5rem; border: 2px solid #e5e7eb; border-radius: 8px; background: white; cursor: pointer; font-weight: 600;">Cancel</button>
                    <button type="submit"
                        style="padding: 0.75rem 1.5rem; border: none; border-radius: 8px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; cursor: pointer; font-weight: 600;"><i
                            class="bi bi-check-lg"></i> Complete Payment</button>
                </div>
            </form>
        </div>
    </div>

@endsection