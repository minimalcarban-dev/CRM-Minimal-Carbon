            <!-- RIGHT PANEL: Shapes → Sizes -->
            <div class="main-panel">
                <!-- Dynamic Content Areas -->
                @php
                    $allCategories = $labGrownCategories->concat($naturalCategories);
                @endphp

                @foreach ($allCategories as $category)
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
                                <button class="btn btn-sm btn-theme-outline" type="button"
                                    onclick="focusAddShape('{{ $category->id }}')">
                                    Add Shape
                                </button>
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
                                            @if ($totalPcs != 0)
                                                <span
                                                    class="stock-total-pill {{ $totalPcs < 0 ? 'bg-danger text-white' : '' }}">{{ $totalPcs }}
                                                    pcs</span>
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
                                                    <th>Total Carats</th>
                                                    <th>Total Price</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($diamonds as $diamond)
                                                    @php
                                                        // Extract size number from size_label (format: "shape-size")
                                                        $sizeParts = explode('-', $diamond->size_label);
                                                        $sizeNum = end($sizeParts);
                                                    @endphp
                                                    <tr class="searchable-row"
                                                        data-search="{{ strtolower($diamond->size_label . ' ' . $diamond->shape) }}"
                                                        data-diamond-id="{{ $diamond->id }}">
                                                        <td class="fw-bold">{{ $sizeNum }}</td>
                                                        <td class="text-muted small">
                                                            {{ str_replace('-', ' ', $diamond->size_label) }}
                                                        </td>
                                                        <td>
                                                            @if ($diamond->available_pieces != 0)
                                                                <span data-stock-badge="1"
                                                                    class="badge {{ $diamond->available_pieces > 0 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} border px-3 py-2 rounded-pill"
                                                                    style="cursor:pointer"
                                                                    onclick="openHistoryModal({{ $diamond->id }})"
                                                                    title="Click to view history">
                                                                    {{ $diamond->available_pieces }} pcs
                                                                </span>
                                                            @else
                                                                <span data-stock-badge="1"
                                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill"
                                                                    style="cursor:pointer"
                                                                    onclick="openHistoryModal({{ $diamond->id }})"
                                                                    title="Click to view history">
                                                                    Out of Stock
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="fw-medium">
                                                            ${{ number_format($diamond->purchase_price_per_ct ?? 0, 2) }}
                                                        </td>
                                                        <td class="fw-medium" data-stock-carat="1">
                                                            {{ number_format($diamond->available_carat_weight ?? 0, 3) }}
                                                            ct
                                                        </td>
                                                        <td class="fw-bold" data-stock-price="1">
                                                            ${{ number_format($diamond->total_price ?? 0, 2) }}</td>
                                                        <td class="text-end">
                                                            <button class="btn btn-sm btn-theme-icon btn-theme-icon-in"
                                                                data-action="in" data-diamond-id="{{ $diamond->id }}"
                                                                data-diamond-name="{{ $diamond->shape }} {{ $diamond->size_label }}"
                                                                data-category-name="{{ $category->name }}"
                                                                title="Add Stock"
                                                                onclick="openTransactionModal(this.dataset.action, this.dataset.diamondId, this.dataset.diamondName, this.dataset.categoryName)">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                            <button
                                                                class="btn btn-sm btn-theme-icon btn-theme-icon-out ms-1"
                                                                data-action="out" data-diamond-id="{{ $diamond->id }}"
                                                                data-diamond-name="{{ $diamond->shape }} {{ $diamond->size_label }}"
                                                                data-category-name="{{ $category->name }}"
                                                                title="Use Stock"
                                                                onclick="openTransactionModal(this.dataset.action, this.dataset.diamondId, this.dataset.diamondName, this.dataset.categoryName)">
                                                                <i class="bi bi-dash-lg"></i>
                                                            </button>
                                                            @php
                                                                $lastTx = $diamond->transactions->first();
                                                                $lastTxPieces = $lastTx ? $lastTx->pieces : '';
                                                                $lastTxCarats = $lastTx ? $lastTx->carat_weight : '';
                                                            @endphp
                                                            <button
                                                                class="btn btn-sm btn-theme-icon btn-theme-icon-edit ms-1"
                                                                title="Edit Melee"
                                                                onclick="openEditModal({{ $diamond->id }}, '{{ $diamond->shape }}', '{{ explode('-', $diamond->size_label)[1] ?? str_replace(strtolower($diamond->shape) . '-', '', $diamond->size_label) }}', '{{ $lastTxPieces }}', '{{ $lastTxCarats }}')">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button
                                                                class="btn btn-sm btn-theme-icon btn-theme-icon-delete ms-1"
                                                                title="Delete Melee"
                                                                onclick="deleteMeleeDiamond({{ $diamond->id }}, '{{ $diamond->shape }} {{ $diamond->size_label }}')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <!-- Add Size Row (inside each shape) -->
                                        <div class="add-size-row">
                                            <input type="text" class="form-control form-control-sm add-size-input"
                                                placeholder="e.g. 1.5 or 4*2" data-category-id="{{ $category->id }}"
                                                data-shape="{{ $shapeName }}">
                                            <button class="btn btn-sm btn-primary" onclick="addSizeToShape(this)">
                                                Add Size
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                {{-- Show available shapes from allowed_shapes even if no diamonds exist --}}
                            @endforelse

                            {{-- Show empty shapes (from allowed_shapes with no diamonds yet) --}}
                            @if ($category->allowed_shapes)
                                @foreach ($category->allowed_shapes as $allowedShape)
                                    @if (!$shapeGroups->has($allowedShape))
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
                                                    <input type="text"
                                                        class="form-control form-control-sm add-size-input"
                                                        placeholder="e.g. 1.5 or 4*2"
                                                        data-category-id="{{ $category->id }}"
                                                        data-shape="{{ $allowedShape }}">
                                                    <button class="btn btn-sm btn-primary" onclick="addSizeToShape(this)">
                                                        Add Size
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <!-- Add New Shape Bar at the bottom -->
                            <div class="add-shape-bar" id="add-shape-bar-{{ $category->id }}">
                                <input type="text" class="form-control form-control-sm new-shape-name"
                                    placeholder="New shape name" data-category-id="{{ $category->id }}">
                                <input type="text" class="form-control form-control-sm new-shape-size"
                                    placeholder="Size (e.g. 1.0 or 4*2)" data-category-id="{{ $category->id }}">
                                <button class="btn btn-sm btn-theme-outline"
                                    onclick="addNewShape(this, '{{ $category->id }}')">
                                    Add Shape
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Initial Empty State -->
                <div id="empty-state-placeholder"
                    class="d-flex align-items-center justify-content-center h-100 flex-column text-muted">
                    <i class="bi bi-arrow-left-circle fs-1 mb-3"></i>
                    <h5>Select a category from the sidebar</h5>
                </div>
            </div>
