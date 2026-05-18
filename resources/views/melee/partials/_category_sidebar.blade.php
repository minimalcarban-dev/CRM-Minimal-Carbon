            <!-- LEFT SIDEBAR: Categories -->
            <div class="sidebar-panel">
                <h6 class="text-uppercase text-secondary fs-7 fw-bold mb-3 ps-2">Categories</h6>

                <!-- LAB GROWN LIST -->
                <div id="sidebar-lab-grown">
                    @forelse($labGrownCategories as $category)
                        <div class="position-relative mb-1 category-item-container">
                            <button
                                class="category-nav-item cat-btn-{{ $category->id }} w-100 d-flex justify-content-between align-items-center"
                                style="padding-right: 2.5rem; margin-bottom: 0; min-height: 48px;"
                                onclick="selectCategory('{{ $category->id }}', this)">
                                <span class="text-start" style="white-space: normal; line-height: 1.2;">
                                    <i class="bi bi-gem me-2"></i>{{ $category->name }}
                                </span>
                                <span class="badge ms-2 flex-shrink-0"
                                    style="min-width: 25px;">{{ $category->diamonds->count() }}</span>
                            </button>
                            <button
                                class="btn btn-sm text-danger position-absolute top-50 end-0 translate-middle-y me-1 p-1 category-delete-btn"
                                onclick="event.stopPropagation(); deleteMeleeCategory({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                title="Delete Category">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                            <small>No categories found.<br>Run the seeder to add defaults.</small>
                        </div>
                    @endforelse
                    <div class="mt-3">
                        <button class="btn btn-sm btn-theme-outline w-100" onclick="createMeleeCategory('lab_grown')">
                            <i class="bi bi-plus-lg me-1"></i> Add Category
                        </button>
                    </div>
                </div>

                <!-- NATURAL LIST (Hidden by default) -->
                <div id="sidebar-natural" class="hidden">
                    @forelse($naturalCategories as $category)
                        <div class="position-relative mb-1 category-item-container">
                            <button
                                class="category-nav-item cat-btn-{{ $category->id }} w-100 d-flex justify-content-between align-items-center"
                                style="padding-right: 2.5rem; margin-bottom: 0; min-height: 48px;"
                                onclick="selectCategory('{{ $category->id }}', this)">
                                <span class="text-start" style="white-space: normal; line-height: 1.2;">
                                    <i class="bi bi-diamond-half me-2"></i>{{ $category->name }}
                                </span>
                                <span class="badge ms-2 flex-shrink-0"
                                    style="min-width: 25px;">{{ $category->diamonds->count() }}</span>
                            </button>
                            <button
                                class="btn btn-sm text-danger position-absolute top-50 end-0 translate-middle-y me-1 p-1 category-delete-btn"
                                onclick="event.stopPropagation(); deleteMeleeCategory({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                title="Delete Category">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                            <small>No categories found.<br>Run the seeder to add defaults.</small>
                        </div>
                    @endforelse
                    <div class="mt-3">
                        <button class="btn btn-sm btn-theme-outline w-100" onclick="createMeleeCategory('natural')">
                            <i class="bi bi-plus-lg me-1"></i> Add Category
                        </button>
                    </div>
                </div>
            </div>
