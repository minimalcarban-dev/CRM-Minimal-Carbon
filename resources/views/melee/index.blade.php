@extends('layouts.admin')

@section('title', 'Melee Diamond Inventory')

@section('content')

    <style>
        /* Custom Styles meant to match orders/index.blade.php */
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --border: #e2e8f0;
            --secondary: #64748b;
        }

        .inventory-management-container {
            padding: 2rem;
            max-width: 1800px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inventory-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            min-height: 600px;
        }

        .sidebar-panel {
            width: 280px;
            border-right: 1px solid var(--border);
            padding: 1.5rem;
            background: #fff;
            flex-shrink: 0;
            max-height: 80vh;
            overflow-y: auto;
        }

        .main-panel {
            flex-grow: 1;
            padding: 0;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar Items */
        .shape-nav-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            color: var(--secondary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            margin-bottom: 0.5rem;
        }

        .shape-nav-item:hover {
            background: #f1f5f9;
            color: var(--dark);
        }

        .shape-nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .shape-nav-item .badge {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }

        .shape-nav-item.active .badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .shape-nav-item:not(.active) .badge {
            background: #f1f5f9;
            color: var(--secondary);
        }

        /* Table */
        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-custom thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--secondary);
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: #f8fafc;
        }

        .table-custom tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            color: var(--dark);
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
        }

        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .hidden {
            display: none !important;
        }
        
        /* Stats Cards Mini */
        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
        }
    </style>

    <div class="inventory-management-container">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h2 class="mb-1 fw-bold text-dark"><i class="bi bi-gem me-2 text-primary"></i>Melee Inventory</h2>
                <div class="text-secondary small">Manage your melee diamond stock</div>
            </div>
            
             <div class="d-flex gap-2">
                 <!-- Tab Switcher implemented as Buttons -->
                <button class="btn btn-outline-primary active" id="btn-tab-lab" onclick="switchMainTab('lab-grown')">
                    Lab Grown
                </button>
                <button class="btn btn-outline-secondary" id="btn-tab-natural" onclick="switchMainTab('natural')">
                    Natural
                </button>
                
                <div class="vr mx-2"></div>
                
                <button class="btn btn-primary" onclick="openTransactionModal('in')">
                    <i class="bi bi-plus-lg me-2"></i>Add Stock
                </button>
                <button class="btn btn-outline-danger" onclick="openTransactionModal('out')">
                    <i class="bi bi-dash-lg me-2"></i>Use Stock
                </button>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="inventory-card">
            
            <!-- LEFT SIDEBAR: Shapes -->
            <div class="sidebar-panel">
                <h6 class="text-uppercase text-secondary fs-7 fw-bold mb-3 ps-2">Shapes</h6>
                
                <!-- LAB GROWN LIST -->
                <div id="sidebar-lab-grown">
                    @foreach($labGrownCategories as $category)
                        <button class="shape-nav-item cat-btn-{{ $category->id }}" onclick="selectCategory('{{ $category->id }}', this)">
                            <span>
                                <i class="bi bi-gem me-2"></i> {{ $category->name }}
                            </span>
                            <span class="badge">{{ $category->diamonds->count() }}</span>
                        </button>
                    @endforeach
                </div>
                
                <!-- NATURAL LIST (Hidden by default) -->
                <div id="sidebar-natural" class="hidden">
                     @foreach($naturalCategories as $category)
                         <button class="shape-nav-item cat-btn-{{ $category->id }}" onclick="selectCategory('{{ $category->id }}', this)">
                            <span>
                                <i class="bi bi-diamond-half me-2"></i> {{ $category->name }}
                            </span>
                             <span class="badge">{{ $category->diamonds->count() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- RIGHT PANEL: Data Table -->
            <div class="main-panel">
                <!-- Dynamic Content Areas -->
                @php
                    $allCategories = $labGrownCategories->concat($naturalCategories);
                @endphp
                
                @foreach($allCategories as $category)
                    <div id="cat-view-{{ $category->id }}" class="category-view hidden h-100 flex-column">
                        <!-- Toolbar -->
                        <div class="table-header">
                            <div>
                                <h5 class="fw-bold mb-0">{{ $category->name }}</h5>
                                <small class="text-muted">Total Stock: {{ $category->diamonds->sum('available_pieces') }} pcs</small>
                            </div>
                            <div>
                                <input type="text" class="form-control form-control-sm" placeholder="Search size..."
                                       aria-label="Search diamonds by size"
                                       onkeyup="filterCategoryTable('{{ $category->id }}', this.value)">        
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Shape</th>
                                        <th>Size Label</th>
                                        <th>Sieve</th>
                                        <th>Stock Status</th>
                                        <th>Avg $/Ct</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->diamonds as $diamond)
                                        <tr class="searchable-row" data-search="{{ strtolower($diamond->size_label . ' ' . $diamond->shape) }}">
                                            <td class="fw-medium text-secondary">{{ $diamond->shape }}</td>
                                            <td class="fw-bold">{{ $diamond->size_label }}</td>
                                            <td class="text-muted">{{ $diamond->sieve_size ?? '-' }}</td>
                                            <td>
                                                @if($diamond->available_pieces > 0)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                                        {{ $diamond->available_pieces }} pcs
                                                    </span>
                                                @else
                                                     <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                                                        Out of Stock
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="fw-medium">${{ number_format($diamond->purchase_price_per_ct ?? 0, 2) }}</td>                                            <td class="text-end">
                                                 <button class="btn btn-sm btn-light text-primary border"
                                                    data-action="in"
                                                    data-diamond-id="{{ $diamond->id }}"
                                                    data-diamond-name="{{ $diamond->shape }} {{ $diamond->size_label }}"
                                                    data-category-name="{{ $category->name }}"
                                                    onclick="openTransactionModal(this.dataset.action, this.dataset.diamondId, this.dataset.diamondName, this.dataset.categoryName)">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light text-danger border ms-1"
                                                    data-action="out"
                                                    data-diamond-id="{{ $diamond->id }}"
                                                    data-diamond-name="{{ $diamond->shape }} {{ $diamond->size_label }}"
                                                    data-category-name="{{ $category->name }}"
                                                    onclick="openTransactionModal(this.dataset.action, this.dataset.diamondId, this.dataset.diamondName, this.dataset.categoryName)">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                             @if($category->diamonds->isEmpty())
                                <div class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-box-seam fs-1"></i></div>
                                    <p class="text-muted">No inventory records found for this category.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                
                <!-- Initial Empty State -->
                <div id="empty-state-placeholder" class="d-flex align-items-center justify-content-center h-100 flex-column text-muted">
                    <i class="bi bi-arrow-left-circle fs-1 mb-3"></i>
                    <h5>Select a category from the sidebar</h5>
                </div>
            </div>
        </div>
    </div>

    @include('melee.partials.transaction_modal')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-select first lab-grown category on load
            const firstBtn = document.querySelector('#sidebar-lab-grown .shape-nav-item');
            if(firstBtn) firstBtn.click();
        });

        function switchMainTab(type) {
            // Reset buttons
            document.getElementById('btn-tab-lab').className = 'btn btn-outline-secondary';
            document.getElementById('btn-tab-natural').className = 'btn btn-outline-secondary';
            
            if (type === 'lab-grown') {
                 document.getElementById('btn-tab-lab').className = 'btn btn-outline-primary active';
                 document.getElementById('sidebar-lab-grown').classList.remove('hidden');
                 document.getElementById('sidebar-natural').classList.add('hidden');
                 
                 // Auto-click first visible item in this list if nothing active
                  const first = document.querySelector('#sidebar-lab-grown .shape-nav-item');
                  if(first) first.click();
            } else {
                 document.getElementById('btn-tab-natural').className = 'btn btn-outline-primary active';
                 document.getElementById('sidebar-lab-grown').classList.add('hidden');
                 document.getElementById('sidebar-natural').classList.remove('hidden');
                 
                  const first = document.querySelector('#sidebar-natural .shape-nav-item');
                  if(first) first.click();
            }
        }

        function selectCategory(catId, btn) {
            // 1. Sidebar Active State
            document.querySelectorAll('.shape-nav-item').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');

            // 2. Hide all views
            document.querySelectorAll('.category-view').forEach(el => {
                el.classList.remove('d-flex');
                el.classList.add('hidden');
            });
            
            document.getElementById('empty-state-placeholder').classList.add('hidden');
            document.getElementById('empty-state-placeholder').classList.remove('d-flex');

            // 3. Show target view
            const target = document.getElementById('cat-view-' + catId);
            if(target) {
                target.classList.remove('hidden');
                target.classList.add('d-flex');
            }
        }
        
        function filterCategoryTable(catId, term) {
            term = term.toLowerCase();
            const rows = document.querySelectorAll('#cat-view-' + catId + ' .searchable-row');
            
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                if(searchData && searchData.includes(term)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
        
        // Transaction Modal logic
        function openTransactionModal(type, diamondId, diamondName, categoryName) {
            document.getElementById('transactionForm').reset();
            $('#modal_diamond_select').val(null).trigger('change'); // jQuery for Select2
            
            // Checkboxes
            if (type === 'in') {
                if(document.getElementById('type_in')) document.getElementById('type_in').checked = true;
            } else {
                if(document.getElementById('type_out')) document.getElementById('type_out').checked = true;
            }

            if (diamondId) {
                setModalSelection(diamondId, diamondName, categoryName);
            } else {
                resetModalSelection();
            }

            var modalEl = document.getElementById('transactionModal');
            if(modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
        
        function setModalSelection(id, name, cat) {
            if(document.getElementById('modal_diamond_id')) document.getElementById('modal_diamond_id').value = id;
            if(document.getElementById('modal_item_name')) document.getElementById('modal_item_name').textContent = name || 'Unknown Item';
            if(document.getElementById('modal_item_cat')) document.getElementById('modal_item_cat').textContent = cat || 'Category';
            
            if(document.getElementById('selection_context')) document.getElementById('selection_context').style.display = 'flex';
            if(document.getElementById('diamond_selector_container')) document.getElementById('diamond_selector_container').style.display = 'none';
        }

        function resetModalSelection() {
            if(document.getElementById('modal_diamond_id')) document.getElementById('modal_diamond_id').value = '';
            if(document.getElementById('selection_context')) document.getElementById('selection_context').style.display = 'none';
            if(document.getElementById('diamond_selector_container')) document.getElementById('diamond_selector_container').style.display = 'block';
        }
    </script>
@endsection