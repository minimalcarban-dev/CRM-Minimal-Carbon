@extends('layouts.admin')

@section('title', 'Melee Diamond Inventory')

@section('content')

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --border: #e2e8f0;
        }

        .dashboard-header {
            display: flex;
            justify_content: space-between;
            align_items: center;
            margin-bottom: 1.5rem;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stats-card .icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-card.primary .icon-wrapper {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .stats-card.success .icon-wrapper {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .stats-card.danger .icon-wrapper {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .nav-tabs-custom {
            border-bottom: 2px solid var(--border);
            margin-bottom: 2rem;
            display: flex;
            gap: 2rem;
        }

        .nav-tab-item {
            padding: 1rem 0;
            font-weight: 600;
            color: var(--secondary);
            cursor: pointer;
            position: relative;
            transition: color 0.2s;
            text-decoration: none;
            background: none;
            border: none;
            font-size: 1.1rem;
        }

        .nav-tab-item.active {
            color: var(--primary);
        }

        .nav-tab-item.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
        }

        /* Accordion Styles */
        .category-accordion-item {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }

        .category-accordion-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .accordion-header {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            background: white;
        }

        .accordion-header:hover {
            background: #f8fafc;
        }

        .accordion-body {
            display: none;
            /* JS Toggle */
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            background: #fcfcfc;
        }

        .accordion-body.open {
            display: block;
        }

        /* Shape Grid */
        .shape-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }

        .shape-tile {
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .shape-tile:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .shape-tile.active {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
            font-weight: 600;
            color: var(--primary);
        }

        .hidden {
            display: none !important;
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <h2 class="mb-1 fw-bold text-dark">Melee Inventory</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active">Stock Management</li>
                    </ol>
                </nav>
            </div>
            <div>
                <button class="btn btn-primary" onclick="openTransactionModal('in')">
                    <i class="bi bi-plus-lg me-2"></i>Add Stock (IN)
                </button>
                <button class="btn btn-outline-danger ms-2" onclick="openTransactionModal('out')">
                    <i class="bi bi-dash-lg me-2"></i>Use Stock (OUT)
                </button>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stats-card primary">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="icon-wrapper"><i class="bi bi-box-seam"></i></div>
                            <h6 class="text-secondary mb-1">Total Parcels</h6>
                            <h2 class="fw-bold mb-0">{{ number_format($totalParcels) }}</h2>
                            <small class="text-muted">Distinct SKU groups</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card success">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="icon-wrapper"><i class="bi bi-gem"></i></div>
                            <h6 class="text-secondary mb-1">Total Carat Weight</h6>
                            <h2 class="fw-bold mb-0">{{ number_format($totalCarats, 2) }} <small
                                    class="fs-6 text-muted">ct</small></h2>
                            <small class="text-muted">Available stock</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card danger">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="icon-wrapper"><i class="bi bi-exclamation-triangle"></i></div>
                            <h6 class="text-secondary mb-1">Low Stock Alerts</h6>
                            <h2 class="fw-bold mb-0 text-danger">{{ $lowStockCount }}</h2>
                            <small class="text-muted">Items below threshold</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs-custom">
            <button class="nav-tab-item active" onclick="switchTab('lab-grown', this)">
                <i class="bi bi-stars me-2"></i>Lab Grown Diamonds
            </button>
            <button class="nav-tab-item" onclick="switchTab('natural', this)">
                <i class="bi bi-diamond me-2"></i>Natural Diamonds
            </button>
        </div>

        <!-- Tab Content: Lab Grown -->
        <div id="tab-lab-grown" class="tab-content-area">
            @foreach($labGrownCategories as $category)
                <div class="category-accordion-item" id="cat-{{ $category->id }}">
                    <div class="accordion-header" onclick="toggleAccordion('{{ $category->id }}')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light rounded p-2 text-primary">
                                <i class="bi bi-layers fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $category->name }}</h5>
                                <small class="text-muted">{{ count($category->diamonds) }} Stock Listings</small>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-secondary transition-icon" id="icon-{{ $category->id }}"></i>
                    </div>

                    <div class="accordion-body" id="body-{{ $category->id }}">
                        <!-- Allowed Shapes Grid -->
                        @php
                            // We group diamonds by shape to calculate stock counts
                            $diamondGroups = collect($category->diamonds)->groupBy('shape');
                        @endphp

                        <h6 class="text-secondary text-uppercase fs-7 mb-3 ls-1">Select Shape</h6>
                        <div class="shape-grid mb-4">
                            @if(is_array($category->allowed_shapes))
                                @foreach($category->allowed_shapes as $shapeName)
                                    @php
                                        // Get diamonds for this shape if any exist
                                        $shapeDiamonds = $diamondGroups->get($shapeName, collect());
                                        $stockCount = $shapeDiamonds->sum('available_pieces');
                                    @endphp
                                    <div class="shape-tile" onclick="filterTable('{{ $category->id }}', '{{ $shapeName }}', this)">
                                        <div class="mb-2 text-primary">
                                            <!-- Simple Icon Logic -->
                                            @if($shapeName == 'Round') <i class="bi bi-circle"></i>
                                            @elseif($shapeName == 'Pear') <i class="bi bi-droplet"></i>
                                            @elseif($shapeName == 'Oval') <i class="bi bi-egg"></i> <!-- closest to oval -->
                                            @elseif($shapeName == 'Marquise') <i class="bi bi-eye"></i>
                                                <!-- closest to marquise shape -->
                                            @elseif($shapeName == 'Baguette') <i class="bi bi-square"></i>
                                            @else <i class="bi bi-gem"></i>
                                            @endif
                                        </div>
                                        <div class="fw-bold">{{ $shapeName }}</div>
                                        <small class="text-muted">{{ $stockCount }} pcs</small>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted">No shapes defined.</div>
                            @endif
                        </div>

                        <!-- Stock Table Container (Hidden initially or shows all?) -->
                        <!-- Showing all initially is simpler, filtered by JS -->
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Shape</th>
                                        <th>Size Label</th>
                                        <th>Sieve</th>
                                        <th class="text-center">Stock (Pcs)</th>
                                        <th class="text-center">Avg $/Ct</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->diamonds as $diamond)
                                        <tr class="stock-row cat-row-{{ $category->id }}" data-shape="{{ $diamond->shape }}">
                                            <td class="ps-4 fw-medium">{{ $diamond->shape }}</td>
                                            <td>{{ $diamond->size_label }}</td>
                                            <td class="text-muted">{{ $diamond->sieve_size ?? '-' }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $diamond->available_pieces > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill">
                                                    {{ $diamond->available_pieces }}
                                                </span>
                                            </td>
                                            <td class="text-center">${{ number_format($diamond->purchase_price_per_ct, 2) }}</td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-success border"
                                                    onclick="openTransactionModal('in', '{{ $diamond->id }}', '{{ $diamond->shape }} {{ $diamond->size_label }}', '{{ $category->name }}')">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger border ms-1"
                                                    onclick="openTransactionModal('out', '{{ $diamond->id }}', '{{ $diamond->shape }} {{ $diamond->size_label }}', '{{ $category->name }}')">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tab Content: Natural (Placeholder Structure) -->
        <div id="tab-natural" class="tab-content-area hidden">
            @foreach($naturalCategories as $category)
                <div class="category-accordion-item">
                    <div class="accordion-header" onclick="toggleAccordion('{{ $category->id }}')">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light rounded p-2 text-warning">
                                <i class="bi bi-diamond-half fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $category->name }}</h5>
                                <small class="text-muted">
                                    @if($category->has_color_layer)
                                        Filter by Color & Size
                                    @else
                                        {{ count($category->diamonds) }} Stock Listings
                                    @endif
                                </small>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-secondary" id="icon-{{ $category->id }}"></i>
                    </div>
                    <div class="accordion-body" id="body-{{ $category->id }}">
                        <!-- Allowed Shapes Grid -->
                        @php
                            // We group diamonds by shape to calculate stock counts
                            $diamondGroups = collect($category->diamonds)->groupBy('shape');
                        @endphp

                        <h6 class="text-secondary text-uppercase fs-7 mb-3 ls-1">Select Shape</h6>
                        <div class="shape-grid mb-4">
                            @if(is_array($category->allowed_shapes))
                                @foreach($category->allowed_shapes as $shapeName)
                                    @php
                                        // Get diamonds for this shape if any exist
                                        $shapeDiamonds = $diamondGroups->get($shapeName, collect());
                                        $stockCount = $shapeDiamonds->sum('available_pieces');
                                    @endphp
                                    <div class="shape-tile" onclick="filterTable('{{ $category->id }}', '{{ $shapeName }}', this)">
                                        <div class="mb-2 text-primary">
                                            <!-- Simple Icon Logic -->
                                            @if($shapeName == 'Round') <i class="bi bi-circle"></i>
                                            @elseif($shapeName == 'Pear') <i class="bi bi-droplet"></i>
                                            @elseif($shapeName == 'Oval') <i class="bi bi-egg"></i>
                                            @elseif($shapeName == 'Marquise') <i class="bi bi-eye"></i>
                                            @elseif($shapeName == 'Baguette') <i class="bi bi-square"></i>
                                            @else <i class="bi bi-gem"></i>
                                            @endif
                                        </div>
                                        <div class="fw-bold">{{ $shapeName }}</div>
                                        <small class="text-muted">{{ $stockCount }} pcs</small>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted">No shapes defined.</div>
                            @endif
                        </div>

                        <!-- Stock Table -->
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Shape</th>
                                        <th>Size Label</th>
                                        <th>Sieve</th>
                                        <th class="text-center">Stock (Pcs)</th>
                                        <th class="text-center">Avg $/Ct</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->diamonds as $diamond)
                                        <tr class="stock-row cat-row-{{ $category->id }}" data-shape="{{ $diamond->shape }}">
                                            <td class="ps-4 fw-medium">{{ $diamond->shape }}</td>
                                            <td>{{ $diamond->size_label }}</td>
                                            <td class="text-muted">{{ $diamond->sieve_size ?? '-' }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $diamond->available_pieces > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill">
                                                    {{ $diamond->available_pieces }}
                                                </span>
                                            </td>
                                            <td class="text-center">${{ number_format($diamond->purchase_price_per_ct, 2) }}</td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-sm btn-outline-success border"
                                                    onclick="openTransactionModal('in', '{{ $diamond->id }}', '{{ $diamond->shape }} {{ $diamond->size_label }}', '{{ $category->name }}')">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger border ms-1"
                                                    onclick="openTransactionModal('out', '{{ $diamond->id }}', '{{ $diamond->shape }} {{ $diamond->size_label }}', '{{ $category->name }}')">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Scripts -->
    <script>
        function switchTab(tabName, btn) {
            // Hide all contents
            document.querySelectorAll('.tab-content-area').forEach(el => el.classList.add('hidden'));
            // Show selected
            document.getElementById('tab-' + tabName).classList.remove('hidden');

            // Update Buttons
            document.querySelectorAll('.nav-tab-item').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');
        }

        function toggleAccordion(id) {
            const body = document.getElementById('body-' + id);
            const icon = document.getElementById('icon-' + id);

            if (body.classList.contains('open')) {
                body.classList.remove('open');
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            } else {
                body.classList.add('open');
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            }
        }

        function filterTable(catId, shape, tileBtn) {
            // Highlight Tile
            const parent = tileBtn.closest('.shape-grid');
            parent.querySelectorAll('.shape-tile').forEach(el => el.classList.remove('active'));
            tileBtn.classList.add('active');

            // Filter Rows
            const rows = document.querySelectorAll('.cat-row-' + catId);
            rows.forEach(row => {
                if (row.dataset.shape === shape) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        function openTransactionModal(type, diamondId, diamondName, categoryName) {
            // Reset state
            document.getElementById('transactionForm').reset();
            $('#modal_diamond_select').val(null).trigger('change');

            // Set Type
            if (type === 'in') {
                document.getElementById('type_in').checked = true;
                updateModalTheme('in');
            } else {
                document.getElementById('type_out').checked = true;
                updateModalTheme('out');
            }

            if (diamondId) {
                // Pre-selected mode
                setModalSelection(diamondId, diamondName, categoryName);
            } else {
                // Search mode
                resetModalSelection();
            }

            var modal = new bootstrap.Modal(document.getElementById('transactionModal'));
            modal.show();
        }

        function setModalSelection(id, name, cat) {
            document.getElementById('modal_diamond_id').value = id;
            document.getElementById('modal_item_name').textContent = name || 'Unknown Item';
            document.getElementById('modal_item_cat').textContent = cat || 'Category';

            document.getElementById('selection_context').style.display = 'flex';
            document.getElementById('diamond_selector_container').style.display = 'none';
        }

        function resetModalSelection() {
            document.getElementById('modal_diamond_id').value = '';
            document.getElementById('selection_context').style.display = 'none';
            document.getElementById('diamond_selector_container').style.display = 'block';
        }

        // Initialize Select2 & Events
        document.addEventListener('DOMContentLoaded', function () {
            if ($.fn.select2) {
                $('#modal_diamond_select').select2({
                    dropdownParent: $('#transactionModal'), // Important for modal
                    placeholder: 'Search Melee (e.g. Round 1.25mm)',
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

                $('#modal_diamond_select').on('select2:select', function (e) {
                    var data = e.params.data;
                    // Extract info from selection (assuming data.text has format "Category - Shape - Size")
                    // data object should have those fields if controller sends them, or we parse text
                    // The controller sends: text: "Category - Shape - Size (Stock: N)"
                    // It also sends: category_name, available_pieces

                    // Ideally, we just use the ID and let the UI show the text
                    // But to fill our "Selection Context", we can use the text for now
                    setModalSelection(data.id, data.text.split(' (Stock')[0], data.category_name);
                });
            }
        });
    </script>

    @include('melee.partials.transaction_modal')
@endsection