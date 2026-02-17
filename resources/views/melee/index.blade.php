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
            width: 300px;
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

        /* Sidebar Category Items */
        .category-nav-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.85rem 1.25rem;
            border-radius: 12px;
            color: var(--secondary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .category-nav-item:hover {
            background: #f1f5f9;
            color: var(--dark);
        }

        .category-nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .category-nav-item .badge {
            font-weight: 600;
            font-size: 0.7rem;
            padding: 0.3em 0.6em;
        }

        .category-nav-item.active .badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .category-nav-item:not(.active) .badge {
            background: #f1f5f9;
            color: var(--secondary);
        }

        /* Shape Accordion Group inside main panel */
        .shape-group {
            border-bottom: 1px solid var(--border);
        }

        .shape-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            cursor: pointer;
            transition: background 0.2s;
            background: #fff;
            border: none;
            width: 100%;
            text-align: left;
        }

        .shape-group-header:hover {
            background: #f8fafc;
        }

        .shape-group-header .shape-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .shape-group-header .shape-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .shape-chevron {
            transition: transform 0.25s ease;
            color: var(--secondary);
        }

        .shape-group.open .shape-chevron {
            transform: rotate(180deg);
        }

        .shape-group-body {
            display: none;
            padding: 0;
            background: #fafbfc;
        }

        .shape-group.open .shape-group-body {
            display: block;
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
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: #f8fafc;
        }

        .table-custom tbody td {
            padding: 0.75rem 1.5rem;
            vertical-align: middle;
            color: var(--dark);
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .hidden {
            display: none !important;
        }

        /* Add size row */
        .add-size-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            background: #f1f5f9;
            border-top: 1px dashed var(--border);
        }

        .add-size-row input {
            max-width: 130px;
        }

        /* Add new shape bar */
        .add-shape-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-top: 2px dashed var(--border);
        }

        .add-shape-bar input, .add-shape-bar select {
            max-width: 170px;
        }

        /* ── Select2 Dropdown Styling for Transaction Modal ── */
        #transactionModal .select2-container--bootstrap-5 .select2-selection--single {
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 0.55rem 0.85rem;
            height: auto;
            min-height: 44px;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection--single:focus,
        #transactionModal .select2-container--bootstrap-5.select2-container--focus .select2-selection--single,
        #transactionModal .select2-container--bootstrap-5.select2-container--open .select2-selection--single {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--dark);
            font-weight: 500;
            line-height: 1.5;
            padding: 0;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 10px;
        }

        /* Dropdown panel */
        #transactionModal .select2-container--bootstrap-5 .select2-dropdown {
            border: 2px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 4px;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-search--dropdown {
            padding: 0.75rem;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
            outline: none;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-results__option {
            padding: 0.6rem 0.85rem;
            font-size: 0.9rem;
            color: var(--dark);
            border-radius: 6px;
            margin: 2px 6px;
            transition: background-color 0.15s, color 0.15s;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)) !important;
            color: #fff !important;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-results__option--selected {
            background: rgba(99, 102, 241, 0.08);
            color: var(--primary);
            font-weight: 600;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-results {
            max-height: 220px;
            padding: 4px 0;
        }

        /* Clear button */
        #transactionModal .select2-container--bootstrap-5 .select2-selection__clear {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-right: 4px;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection__clear:hover {
            color: var(--danger);
        }

        /* Size count pill */
        .size-count-pill {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2em 0.6em;
            border-radius: 20px;
        }

        .stock-total-pill {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2em 0.6em;
            border-radius: 20px;
        }

        /* Toast notification */
        .melee-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9999;
            min-width: 300px;
        }
        @media (max-width: 575px) {
            .inventory-management-container{
                padding: 0;
            }
            .page-header {
                padding: 8px;
                border-radius: 7px;
                display: block;
                margin-bottom: 15px;
            }
            .page-header div:first-child {
                margin-bottom: 0.75rem;
            }
            .page-header div:last-child {
                flex-flow: column;
                gap: 5px !important;
            }
            .page-header div:last-child div {
                display: none;
            }
            .inventory-card {
                flex-direction: column;
            }
            .sidebar-panel {
                padding: 7px;
            }
            #cat-view-1 .table-header {
                padding: 10px;
                flex-direction: column;
                text-align: left;
                justify-content: left;
                width: 100%;
            }
            #cat-view-1 .table-header div:last-child {
                margin-top: 5px;
            }
            #cat-view-1 .table-header div {
                width: 100%;
            }
            #shapes-container-1 .table-custom thead th, #shapes-container-1 .table-custom tbody td{
                padding: 3px;
                font-size: 12px;
                white-space: nowrap;
            }
            #shapes-container-1 .rounded-pill{
                white-space: nowrap;
                font-size: 12px;
                padding: 4px !important;
                border-radius: 8px !important;
            }
            #shapes-container-1 td.text-end {
                display: flex;
            }
            .shape-group.open .shape-group-body {
                overflow-y: auto;
            }
            #history-table.table-custom thead th,#history-table.table-custom tbody td{
                padding: 10px;
                white-space: nowrap;
            }
            .add-size-row {
                padding: 9px;
            }
            .add-size-row input {
                padding: 5px;
            }
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
            
            <!-- LEFT SIDEBAR: Categories -->
            <div class="sidebar-panel">
                <h6 class="text-uppercase text-secondary fs-7 fw-bold mb-3 ps-2">Categories</h6>
                
                <!-- LAB GROWN LIST -->
                <div id="sidebar-lab-grown">
                    @forelse($labGrownCategories as $category)
                        <button class="category-nav-item cat-btn-{{ $category->id }}" onclick="selectCategory('{{ $category->id }}', this)">
                            <span>
                                <i class="bi bi-gem me-2"></i> {{ $category->name }}
                            </span>
                            <span class="badge">{{ $category->diamonds->count() }}</span>
                        </button>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                            <small>No categories found.<br>Run the seeder to add defaults.</small>
                        </div>
                    @endforelse
                </div>
                
                <!-- NATURAL LIST (Hidden by default) -->
                <div id="sidebar-natural" class="hidden">
                     @forelse($naturalCategories as $category)
                         <button class="category-nav-item cat-btn-{{ $category->id }}" onclick="selectCategory('{{ $category->id }}', this)">
                            <span>
                                <i class="bi bi-diamond-half me-2"></i> {{ $category->name }}
                            </span>
                             <span class="badge">{{ $category->diamonds->count() }}</span>
                        </button>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                            <small>No categories found.<br>Run the seeder to add defaults.</small>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- RIGHT PANEL: Shapes → Sizes -->
            <div class="main-panel">
                <!-- Dynamic Content Areas -->
                @php
                    $allCategories = $labGrownCategories->concat($naturalCategories);
                @endphp
                
                @foreach($allCategories as $category)
                    @php
                        // Group diamonds by shape
                        $shapeGroups = $category->diamonds->groupBy('shape');
                    @endphp

                    <div id="cat-view-{{ $category->id }}" class="category-view hidden h-100 flex-column">
                        <!-- Toolbar -->
                        <div class="table-header">
                            <div>
                                <h5 class="fw-bold mb-0">{{ $category->name }}</h5>
                                <small class="text-muted">
                                    {{ $shapeGroups->count() }} shapes
                                    · {{ $category->diamonds->count() }} sizes
                                    · {{ $category->diamonds->sum('available_pieces') }} pcs total stock
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Search..."
                                       aria-label="Search diamonds"
                                       onkeyup="filterCategoryTable('{{ $category->id }}', this.value)"
                                       style="width: 180px;">
                            </div>
                        </div>

                        <!-- Shapes Accordion -->
                        <div class="flex-grow-1 overflow-auto" id="shapes-container-{{ $category->id }}">
                            @forelse($shapeGroups as $shapeName => $diamonds)
                                <div class="shape-group" data-shape="{{ strtolower($shapeName) }}">
                                    <button class="shape-group-header" onclick="toggleShapeGroup(this)">
                                        <div class="shape-name">
                                            <i class="bi bi-diamond-fill text-primary" style="font-size:0.8rem;"></i>
                                            {{ $shapeName }}
                                            <span class="size-count-pill">{{ $diamonds->count() }} sizes</span>
                                            @php
                                                $totalPcs = $diamonds->sum('available_pieces');
                                            @endphp
                                            @if($totalPcs != 0)
                                                <span class="stock-total-pill {{ $totalPcs < 0 ? 'bg-danger text-white' : '' }}">{{ $totalPcs }} pcs</span>
                                            @endif
                                        </div>
                                        <div class="shape-meta">
                                            <i class="bi bi-chevron-down shape-chevron"></i>
                                        </div>
                                    </button>
                                    <div class="shape-group-body">
                                        <table class="table table-custom mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Size</th>
                                                    <th>Size Label</th>
                                                    <th>Stock Status</th>
                                                    <th>Avg $/Ct</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($diamonds as $diamond)
                                                    @php
                                                        // Extract size number from size_label (format: "shape-size")
                                                        $sizeParts = explode('-', $diamond->size_label);
                                                        $sizeNum = end($sizeParts);
                                                    @endphp
                                                    <tr class="searchable-row" data-search="{{ strtolower($diamond->size_label . ' ' . $diamond->shape) }}">
                                                        <td class="fw-bold">{{ $sizeNum }}</td>
                                                        <td class="text-muted small">{{ str_replace('-', ' ', $diamond->size_label) }}</td>
                                                        <td>
                                                            @if($diamond->available_pieces != 0)
                                                                <span class="badge {{ $diamond->available_pieces > 0 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} border px-3 py-2 rounded-pill" style="cursor:pointer" onclick="openHistoryModal({{ $diamond->id }})" title="Click to view history">
                                                                    {{ $diamond->available_pieces }} pcs
                                                                </span>
                                                            @else
                                                                 <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill" style="cursor:pointer" onclick="openHistoryModal({{ $diamond->id }})" title="Click to view history">
                                                                    Out of Stock
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="fw-medium">${{ number_format($diamond->purchase_price_per_ct ?? 0, 2) }}</td>
                                                        <td class="text-end">
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
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <!-- Add Size Row (inside each shape) -->
                                        <div class="add-size-row">
                                            <i class="bi bi-plus-circle text-primary"></i>
                                            <input type="text" class="form-control form-control-sm add-size-input"
                                                   placeholder="e.g. 1.5 or 4*2"
                                                   data-category-id="{{ $category->id }}"
                                                   data-shape="{{ $shapeName }}">
                                            <button class="btn btn-sm btn-primary"
                                                    onclick="addSizeToShape(this)">
                                                <i class="bi bi-plus-lg me-1"></i>Add Size
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                {{-- Show available shapes from allowed_shapes even if no diamonds exist --}}
                            @endforelse

                            {{-- Show empty shapes (from allowed_shapes with no diamonds yet) --}}
                            @if($category->allowed_shapes)
                                @foreach($category->allowed_shapes as $allowedShape)
                                    @if(!$shapeGroups->has($allowedShape))
                                        <div class="shape-group" data-shape="{{ strtolower($allowedShape) }}">
                                            <button class="shape-group-header" onclick="toggleShapeGroup(this)">
                                                <div class="shape-name">
                                                    <i class="bi bi-diamond text-secondary" style="font-size:0.8rem;"></i>
                                                    {{ $allowedShape }}
                                                    <span class="size-count-pill">0 sizes</span>
                                                </div>
                                                <div class="shape-meta">
                                                    <i class="bi bi-chevron-down shape-chevron"></i>
                                                </div>
                                            </button>
                                            <div class="shape-group-body">
                                                <div class="text-center py-4 text-muted">
                                                    <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                                                    <small>No sizes added yet. Add one below.</small>
                                                </div>

                                                <!-- Add Size Row -->
                                                <div class="add-size-row">
                                                    <i class="bi bi-plus-circle text-primary"></i>
                                                    <input type="text" class="form-control form-control-sm add-size-input"
                                                           placeholder="e.g. 1.5 or 4*2"
                                                           data-category-id="{{ $category->id }}"
                                                           data-shape="{{ $allowedShape }}">
                                                    <button class="btn btn-sm btn-primary"
                                                            onclick="addSizeToShape(this)">
                                                        <i class="bi bi-plus-lg me-1"></i>Add Size
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <!-- Add New Shape Bar at the bottom -->
                            <div class="add-shape-bar" id="add-shape-bar-{{ $category->id }}">
                                <i class="bi bi-plus-square-dotted text-primary fs-5"></i>
                                <input type="text" class="form-control form-control-sm new-shape-name"
                                       placeholder="New shape name"
                                       data-category-id="{{ $category->id }}">
                                <input type="text" class="form-control form-control-sm new-shape-size"
                                       placeholder="Size (e.g. 1.0 or 4*2)"
                                       data-category-id="{{ $category->id }}">
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="addNewShape(this, '{{ $category->id }}')">
                                    <i class="bi bi-plus-lg me-1"></i>Add Shape
                                </button>
                            </div>
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

    <!-- Toast Container -->
    <div id="meleeToastContainer" class="melee-toast"></div>

    <!-- Stock History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; border:0;">
                    <h5 class="modal-title" id="historyModalLabel">
                        <i class="bi bi-clock-history me-2"></i>Stock History
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Diamond Info Header -->
                    <div id="history-diamond-info" class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <strong id="history-diamond-name">Loading...</strong>
                            <div class="text-muted small" id="history-diamond-detail"></div>
                        </div>
                        <div>
                            <span id="history-stock-badge" class="badge bg-primary-subtle text-primary px-3 py-2 fs-6 rounded-pill"></span>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div id="history-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Loading history...</p>
                    </div>
                    <div id="history-empty" class="text-center py-5 hidden">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted">No transactions recorded yet.</p>
                    </div>
                    <table class="table table-custom mb-0" id="history-table" style="display:none;">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>User</th>
                                <th>Pieces</th>
                                <th>Carat</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="history-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Order View Modal -->
    <div class="modal fade" id="quickOrderModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:12px; overflow:hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title"><i class="bi bi-card-checklist me-2"></i>Order Overview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="quick-order-content">
                    <div class="text-center py-5" id="quick-order-loading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Fetching order details...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <a href="#" id="quick-order-full-link" class="btn btn-primary w-100 py-2 fw-bold">View Full Order Details</a>
                </div>
            </div>
        </div>
    </div>

    @include('melee.partials.transaction_modal')

    <script>
        let activeCategoryId = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-select first lab-grown category on load
            const firstBtn = document.querySelector('#sidebar-lab-grown .category-nav-item');
            if(firstBtn) firstBtn.click();

            // Initialize Select2 for the melee diamond search dropdown
            if ($ && $.fn.select2) {
                $('#modal_diamond_select').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#transactionModal'),
                    placeholder: 'Search Melee Diamond (Shape, Size, etc.)',
                    allowClear: true,
                    ajax: {
                        url: '{{ route("melee.search") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) { return { term: params.term }; },
                        processResults: function (data) { return { results: data }; },
                        cache: true
                    },
                    minimumInputLength: 0
                });

                // When a diamond is selected from dropdown, populate the selection context
                $('#modal_diamond_select').on('select2:select', function (e) {
                    var data = e.params.data;
                    setModalSelection(data.id, data.text.split(' (Stock')[0], data.category_name);
                });
            }
        });

        function switchMainTab(type) {
            // Reset buttons
            document.getElementById('btn-tab-lab').className = 'btn btn-outline-secondary';
            document.getElementById('btn-tab-natural').className = 'btn btn-outline-secondary';
            
            if (type === 'lab-grown') {
                 document.getElementById('btn-tab-lab').className = 'btn btn-outline-primary active';
                 document.getElementById('sidebar-lab-grown').classList.remove('hidden');
                 document.getElementById('sidebar-natural').classList.add('hidden');
                 
                  const first = document.querySelector('#sidebar-lab-grown .category-nav-item');
                  if(first) first.click();
            } else {
                 document.getElementById('btn-tab-natural').className = 'btn btn-outline-primary active';
                 document.getElementById('sidebar-lab-grown').classList.add('hidden');
                 document.getElementById('sidebar-natural').classList.remove('hidden');
                 
                  const first = document.querySelector('#sidebar-natural .category-nav-item');
                  if(first) first.click();
            }
        }

        function selectCategory(catId, btn) {
            activeCategoryId = catId;

            // 1. Sidebar Active State
            document.querySelectorAll('.category-nav-item').forEach(el => el.classList.remove('active'));
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
            const container = document.getElementById('shapes-container-' + catId);
            if (!container) return;

            const shapeGroups = container.querySelectorAll('.shape-group');
            
            shapeGroups.forEach(group => {
                const shapeName = group.getAttribute('data-shape') || '';
                const rows = group.querySelectorAll('.searchable-row');
                let hasVisibleRow = false;

                if (term === '') {
                    // Show all
                    group.classList.remove('hidden');
                    rows.forEach(row => row.classList.remove('hidden'));
                    return;
                }

                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search');
                    if(searchData && searchData.includes(term)) {
                        row.classList.remove('hidden');
                        hasVisibleRow = true;
                    } else {
                        row.classList.add('hidden');
                    }
                });

                // Also match on shape name
                if (shapeName.includes(term)) {
                    group.classList.remove('hidden');
                    rows.forEach(row => row.classList.remove('hidden'));
                } else if (hasVisibleRow) {
                    group.classList.remove('hidden');
                } else {
                    group.classList.add('hidden');
                }
            });
        }

        function toggleShapeGroup(btn) {
            const group = btn.closest('.shape-group');
            group.classList.toggle('open');
        }

        // Add a new size to an existing shape
        function addSizeToShape(btn) {
            const container = btn.closest('.add-size-row');
            const input = container.querySelector('.add-size-input');
            const size = input.value.trim();
            const categoryId = input.dataset.categoryId;
            const shape = input.dataset.shape;

            if (!size) {
                showMeleeToast('Please enter a valid size value.', 'warning');
                input.focus();
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch("{{ route('melee.add-shape') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    category_id: categoryId,
                    shape: shape,
                    size: size
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMeleeToast(data.message, 'success');
                    location.reload();
                } else {
                    showMeleeToast(data.message || 'Error adding size.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                showMeleeToast('An error occurred.', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Add Size';
            });
        }

        // Add a completely new shape with initial size
        function addNewShape(btn, categoryId) {
            const bar = btn.closest('.add-shape-bar');
            const nameInput = bar.querySelector('.new-shape-name');
            const sizeInput = bar.querySelector('.new-shape-size');
            const shape = nameInput.value.trim();
            const size = sizeInput.value.trim();

            if (!shape) {
                showMeleeToast('Please enter a shape name.', 'warning');
                nameInput.focus();
                return;
            }
            if (!size) {
                showMeleeToast('Please enter a valid size value.', 'warning');
                sizeInput.focus();
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch("{{ route('melee.add-shape') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    category_id: categoryId,
                    shape: shape,
                    size: size
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMeleeToast(data.message, 'success');
                    location.reload();
                } else {
                    showMeleeToast(data.message || 'Error adding shape.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                showMeleeToast('An error occurred.', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i>Add Shape';
            });
        }

        // Simple toast helper
        function showMeleeToast(msg, type = 'info') {
            const container = document.getElementById('meleeToastContainer');
            const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : type === 'warning' ? 'bg-warning text-dark' : 'bg-primary';
            const iconClass = type === 'success' ? 'bi-check-circle' : type === 'danger' ? 'bi-exclamation-triangle' : type === 'warning' ? 'bi-exclamation-circle' : 'bi-info-circle';
            
            const toastEl = document.createElement('div');
            toastEl.className = `toast show align-items-center text-white ${bgClass} border-0 mb-2`;
            toastEl.setAttribute('role', 'alert');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body"><i class="bi ${iconClass} me-2"></i>${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
                </div>
            `;
            container.appendChild(toastEl);
            setTimeout(() => toastEl.remove(), 4000);
        }
        
        // Transaction Modal logic
        function openTransactionModal(type, diamondId, diamondName, categoryName) {
            document.getElementById('transactionForm').reset();
            $('#modal_diamond_select').val(null).trigger('change'); // Reset Select2
            
            // Set type & update theme
            if (type === 'in') {
                if(document.getElementById('type_in')) document.getElementById('type_in').checked = true;
                updateModalTheme('in');
            } else {
                if(document.getElementById('type_out')) document.getElementById('type_out').checked = true;
                updateModalTheme('out');
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

        // ── Stock History Modal ──
        function openHistoryModal(diamondId) {
            // Show modal & loading state
            document.getElementById('history-loading').classList.remove('hidden');
            document.getElementById('history-empty').classList.add('hidden');
            document.getElementById('history-table').style.display = 'none';
            document.getElementById('history-diamond-name').textContent = 'Loading...';
            document.getElementById('history-diamond-detail').textContent = '';
            document.getElementById('history-stock-badge').textContent = '';

            var historyModalEl = document.getElementById('historyModal');
            var historyModal = new bootstrap.Modal(historyModalEl);
            historyModal.show();

            // Fetch history
            fetch(`/admin/melee/history/${diamondId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('history-loading').classList.add('hidden');

                // Populate diamond info header
                const d = data.diamond;
                document.getElementById('history-diamond-name').textContent = `${d.category_name} — ${d.shape}`;
                document.getElementById('history-diamond-detail').textContent = `Size: ${d.size_label.replace('-', ' ')}`;
                document.getElementById('history-stock-badge').textContent = `${d.available_pieces} pcs available`;

                // Populate transactions
                const txns = data.transactions;
                const tbody = document.getElementById('history-tbody');
                tbody.innerHTML = '';

                if (!txns || txns.length === 0) {
                    document.getElementById('history-empty').classList.remove('hidden');
                    return;
                }

                document.getElementById('history-table').style.display = '';

                txns.forEach(t => {
                    const typeBadge = t.type === 'in'
                        ? '<span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-arrow-down-circle me-1"></i>Stock IN</span>'
                        : t.type === 'out'
                        ? '<span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-arrow-up-circle me-1"></i>Stock OUT</span>'
                        : '<span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1"><i class="bi bi-arrow-repeat me-1"></i>Adjust</span>';

                    const refText = t.reference_type === 'order' && t.reference_id
                        ? `<a href="javascript:void(0)" onclick="viewOrderQuick(${t.reference_id})" class="text-primary text-decoration-none fw-bold"><i class="bi bi-link-45deg"></i>Order #${t.reference_id}</a>`
                        : (t.reference_type || 'Manual');

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${typeBadge}</td>
                        <td class="fw-medium">${t.user_name}</td>
                        <td class="fw-bold">${Math.abs(t.pieces)}</td>
                        <td>${t.carat_weight || '-'}</td>
                        <td>${refText}</td>
                        <td class="text-muted small">${t.notes || '-'}</td>
                        <td>
                            <span class="small">${t.created_at}</span>
                            <br><span class="text-muted small">${t.time_ago}</span>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(err => {
                console.error(err);
                document.getElementById('history-loading').classList.add('hidden');
                document.getElementById('history-empty').classList.remove('hidden');
                document.getElementById('history-empty').querySelector('p').textContent = 'Error loading history.';
            });
        }

        // ── Quick Order View ──
        function viewOrderQuick(orderId) {
            const content = document.getElementById('quick-order-content');
            const loading = document.getElementById('quick-order-loading');
            const link = document.getElementById('quick-order-full-link');
            
            // Show modal first
            const modal = new bootstrap.Modal(document.getElementById('quickOrderModal'));
            modal.show();
            
            // Clear old content & show loading
            loading.style.display = 'block';
            const oldDetails = content.querySelector('.order-quick-details');
            if (oldDetails) oldDetails.remove();
            link.classList.add('disabled');

            fetch(`/admin/orders/${orderId}/quick-view`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                link.classList.remove('disabled');
                link.href = data.url;

                const detailsHtml = `
                    <div class="order-quick-details">
                        <div class="p-3 bg-light border-bottom">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h6 class="mb-0 fw-bold">${data.client_name}</h6>
                                    <small class="text-muted">${data.company} • ${data.created_at}</small>
                                </div>
                                <div class="col-4 text-end">
                                    <span class="badge bg-primary rounded-pill px-3">${data.status.replace('_', ' ').toUpperCase()}</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold d-block mb-1">Product Details</label>
                                <p class="mb-1 fw-medium">${data.jewellery_details || 'No jewellery details'}</p>
                                <small class="text-muted">${data.diamond_details || ''}</small>
                            </div>
                            
                            ${data.diamond_sku ? `
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold d-block mb-1">Diamond SKU</label>
                                <code class="fs-6 text-primary fw-bold">${data.diamond_sku}</code>
                            </div>` : ''}

                            ${data.melee_details ? `
                            <div class="p-3 border rounded bg-light mb-3">
                                <label class="text-muted small text-uppercase fw-bold d-block mb-1">Melee Component</label>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>${data.melee_details.name}</span>
                                    <span class="fw-bold text-dark">${data.melee_details.pieces} pcs / ${data.melee_details.carat} ct</span>
                                </div>
                            </div>` : ''}

                            <div class="row pt-3 border-top">
                                <div class="col-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Total Value</label>
                                    <h5 class="mb-0 fw-bold text-success">$ ${data.gross_sell}</h5>
                                </div>
                                <div class="col-6 text-end">
                                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Submitted By</label>
                                    <span class="fw-medium">${data.submitted_by}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                content.insertAdjacentHTML('beforeend', detailsHtml);
            })
            .catch(err => {
                console.error(err);
                loading.innerHTML = '<div class="alert alert-danger m-3">Failed to load order details.</div>';
            });
        }
    </script>
@endsection