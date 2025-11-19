@extends('layouts.admin')

@section('title', 'Diamonds')

@section('content')
    <div class="diamond-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Diamonds</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-gem"></i>
                        Diamond Management
                    </h1>
                    <p class="page-subtitle">Manage your diamond inventory and listings</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('diamond.create') }}" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Diamond</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards (visible to Super Admin only) -->
        @if(auth()->guard('admin')->user() && auth()->guard('admin')->user()->is_super)

         <div class="stats-grid">
             <div class="stat-card stat-card-primary">
                 <div class="stat-icon">
                     <i class="bi bi-gem"></i>
                 </div>
                 <div class="stat-content">
                     <div class="stat-label">Total Diamonds</div>
                     <div class="stat-value">{{ $diamonds->count() }}</div>
                     <div class="stat-trend">
                         <i class="bi bi-arrow-up"></i> In Stock
                     </div>
                 </div>
             </div>
 
             <div class="stat-card stat-card-success">
                 <div class="stat-icon">
                     <i class="bi bi-currency-dollar"></i>
                 </div>
                 <div class="stat-content">
                     <div class="stat-label">Total Value</div>
                     <div class="stat-value">${{ number_format($diamonds->sum('price'), 2) }}</div>
                     <div class="stat-trend">
                         <i class="bi bi-graph-up"></i> Inventory
                     </div>
                 </div>
             </div>
 
             <div class="stat-card stat-card-info">
                 <div class="stat-icon">
                     <i class="bi bi-tag"></i>
                 </div>
                 <div class="stat-content">
                     <div class="stat-label">Avg. Price</div>
                     <div class="stat-value">${{ $diamonds->count() > 0 ? number_format($diamonds->avg('price'), 2) : '0.00' }}</div>
                     <div class="stat-trend">
                         <i class="bi bi-calculator"></i> Per Item
                     </div>
                 </div>
             </div>
         </div>

        @endif

        <!-- Success Alert -->
        @if(session('success'))
            <div class="alert-card success">
                <div class="alert-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="alert-content">
                    <h5 class="alert-title">Success!</h5>
                    <p class="alert-message">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-controls">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input id="diamond-search" type="text" class="search-input"
                        placeholder="Search by Stock ID, SKU, or barcode...">
                </div>

                <button id="diamond-reset" class="btn-reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </button>
            </div>

            <div class="filter-info">
                <span id="diamond-count" class="result-count"></span>
            </div>
        </div>

        <!-- Diamonds Table Card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-hash"></i>
                                    <span>Stock ID</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-upc"></i>
                                    <span>SKU</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-currency-dollar"></i>
                                    <span>Price</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-tag"></i>
                                    <span>Listing Price</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-diamond"></i>
                                    <span>Shape</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-upc-scan"></i>
                                    <span>Barcode</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-person-badge"></i>
                                    <span>Assigned By</span>
                                </div>
                            </th>
                            <th>
                                <div class="th-content">
                                    <i class="bi bi-person-check"></i>
                                    <span>Assigned To</span>
                                </div>
                            </th>
                            <th class="text-end">
                                <div class="th-content">
                                    <i class="bi bi-gear"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diamonds as $d)
                            <tr class="diamond-row" data-search="{{ strtolower($d->stockid . ' ' . $d->sku . ' ' . $d->barcode_number) }}">
                                <td>
                                    <div class="cell-content">
                                        <span class="badge-custom badge-primary">{{ $d->stockid }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="text-semibold">{{ $d->sku }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="price-value">${{ number_format($d->price, 2) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="price-value listing">${{ number_format($d->listing_price, 2) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <span class="text-muted">{{ $d->shape ?: '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        @if($d->barcode_image_url)
                                            <img src="{{ $d->barcode_image_url }}" alt="barcode" class="barcode-image">
                                        @else
                                            <span class="badge-custom badge-secondary">{{ $d->barcode_number }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        @if($d->assignedByAdmin)
                                            <span class="badge-custom badge-info">{{ $d->assignedByAdmin->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content">
                                        <div class="admin-assignment">
                                            @if($d->assignedAdmin)
                                                <span class="admin-name badge-custom badge-success">{{ $d->assignedAdmin->name }}</span>
                                            @else
                                                <span class="text-muted">Unassigned</span>
                                            @endif
                                            <button type="button" class="btn-reassign" data-diamond-id="{{ $d->id }}" title="Reassign to another admin">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-content justify-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('diamond.edit', $d) }}" class="action-btn action-btn-edit" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('diamond.destroy', $d) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this diamond?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn action-btn-delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">
                                    <div class="empty-state-inline">
                                        <div class="empty-icon">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h3 class="empty-title">No diamonds found</h3>
                                        <p class="empty-description">Start by adding your first diamond to the inventory</p>
                                        <a href="{{ route('diamond.create') }}" class="btn-primary-custom">
                                            <i class="bi bi-plus-circle"></i>
                                            <span>Add First Diamond</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="empty-state d-none">
            <div class="empty-icon">
                <i class="bi bi-search"></i>
            </div>
            <h3 class="empty-title">No diamonds found</h3>
            <p class="empty-description">Try adjusting your search criteria</p>
            <button id="empty-reset" class="btn-primary-custom">
                <i class="bi bi-arrow-counterclockwise"></i>
                Reset Search
            </button>
        </div>

        <div class="mt-4">
            {{ $diamonds->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Reassign Modal -->
    <div id="reassignModal" class="modal-overlay d-none">
        <div class="reassign-modal">
            <div class="modal-header">
                <i class="bi bi-arrow-repeat"></i>
                <span>Reassign Diamond</span>
            </div>
            <div class="modal-body">
                <div>
                    <label class="modal-label">SKU: <span id="modalDiamondSku" class="text-semibold">—</span></label>
                </div>
                <div style="margin-top: 12px;">
                    <label class="modal-label" for="adminSelect">Select Admin</label>
                    <select id="adminSelect" name="admin_id" class="modal-select">
                        <option value="">-- Choose Admin --</option>
                        @foreach($admins ?? [] as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-cancel" id="cancelReassign">Cancel</button>
                <button type="button" class="btn-modal btn-modal-primary" id="confirmReassign">Reassign</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('diamond-search');
            const resetBtn = document.getElementById('diamond-reset');
            const emptyResetBtn = document.getElementById('empty-reset');
            const rows = Array.from(document.querySelectorAll('.diamond-row'));
            const countEl = document.getElementById('diamond-count');
            const emptyState = document.getElementById('empty-state');
            const tableCard = document.querySelector('.table-card');

            const applyFilters = () => {
                const term = search.value.trim().toLowerCase();

                let visibleCount = 0;
                rows.forEach(el => {
                    const searchData = el.dataset.search || '';
                    const isVisible = !term || searchData.includes(term);
                    el.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });

                // Update count
                if (countEl) {
                    countEl.textContent = `Showing ${visibleCount} of ${rows.length} diamonds`;
                }

                // Toggle empty state
                if (emptyState && tableCard) {
                    if (visibleCount === 0 && rows.length > 0) {
                        emptyState.classList.remove('d-none');
                        tableCard.style.display = 'none';
                    } else {
                        emptyState.classList.add('d-none');
                        tableCard.style.display = '';
                    }
                }
            };

            search?.addEventListener('input', applyFilters);

            resetBtn?.addEventListener('click', () => {
                search.value = '';
                applyFilters();
            });

            emptyResetBtn?.addEventListener('click', () => resetBtn?.click());

            // Initial filter application
            applyFilters();

            // Admin Reassignment Logic
            const modal = document.getElementById('reassignModal');
            const adminSelect = document.getElementById('adminSelect');
            const cancelBtn = document.getElementById('cancelReassign');
            const confirmBtn = document.getElementById('confirmReassign');
            const modalDiamondSku = document.getElementById('modalDiamondSku');
            let currentDiamondId = null;

            // Open modal when reassign button clicked
            document.querySelectorAll('.btn-reassign').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentDiamondId = this.dataset.diamondId;
                    const row = this.closest('tr');
                    const sku = row.querySelector('td:nth-child(2) .text-semibold').textContent;
                    
                    modalDiamondSku.textContent = sku;
                    adminSelect.value = '';
                    modal.classList.remove('d-none');
                });
            });

            // Close modal
            cancelBtn?.addEventListener('click', () => {
                modal.classList.add('d-none');
                currentDiamondId = null;
            });

            // Close modal on overlay click
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('d-none');
                    currentDiamondId = null;
                }
            });

            // Confirm reassignment
            confirmBtn?.addEventListener('click', async function() {
                const adminId = adminSelect.value;
                
                if (!adminId) {
                    showAlert('Please select an admin', 'warning', 'Select Admin');
                    return;
                }

                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Reassigning...';

                try {
                    const response = await fetch(`/admin/diamonds/${currentDiamondId}/assign`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({
                            admin_id: adminId
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Show success message
                        showAlert(data.message, 'success', 'Success');
                        
                        // Close modal
                        modal.classList.add('d-none');
                        
                        // Reload the page to see updated assignments
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message || 'Failed to reassign diamond', 'error', 'Error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('An error occurred while reassigning', 'error', 'Error');
                } finally {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Reassign';
                }
            });
        });
    </script>

@endsection
