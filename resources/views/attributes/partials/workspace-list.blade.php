<section class="attribute-workspace-card">
    <div class="attribute-workspace-header">
        <div>
            <p class="attribute-eyebrow">Selected Module</p>
            <h2 class="attribute-workspace-title">{{ $module['label'] }}</h2>
            <p class="attribute-workspace-subtitle">{{ $module['description'] }}</p>
        </div>

        <div class="attribute-workspace-header-actions">
            @if ($canCreate)
                <button type="button" class="attribute-btn attribute-btn-primary"
                    data-hub-action="create" data-hub-module="{{ $module['key'] }}">
                    <i class="bi bi-plus-circle"></i>
                    Add New
                </button>
            @endif
        </div>
    </div>

    <div class="attribute-stats-grid">
        <div class="attribute-stat-card attribute-stat-primary">
            <div class="attribute-stat-icon"><i class="bi bi-collection"></i></div>
            <div class="attribute-stat-content">
                <div class="attribute-stat-label">Total</div>
                <div class="attribute-stat-value">{{ number_format($totalCount) }}</div>
            </div>
        </div>
        <div class="attribute-stat-card attribute-stat-success">
            <div class="attribute-stat-icon"><i class="bi bi-check-circle"></i></div>
            <div class="attribute-stat-content">
                <div class="attribute-stat-label">Active</div>
                <div class="attribute-stat-value">{{ number_format($activeCount) }}</div>
            </div>
        </div>
        <div class="attribute-stat-card attribute-stat-warning">
            <div class="attribute-stat-icon"><i class="bi bi-pause-circle"></i></div>
            <div class="attribute-stat-content">
                <div class="attribute-stat-label">Inactive</div>
                <div class="attribute-stat-value">{{ number_format($inactiveCount) }}</div>
            </div>
        </div>
    </div>

    <div class="attribute-filter-section">
        <form method="GET" action="{{ route('attributes.fragment') }}" class="attribute-filter-form"
            data-hub-search-form>
            <input type="hidden" name="module" value="{{ $module['key'] }}">
            <input type="hidden" name="view" value="list">
            <div class="attribute-search-box">
                <i class="bi bi-search attribute-search-icon"></i>
                <input type="search" name="search" class="attribute-search-input" placeholder="Search {{ strtolower($module['label']) }}..."
                    value="{{ $search }}">
            </div>
            <button type="submit" class="attribute-btn attribute-btn-secondary">
                <i class="bi bi-funnel"></i>
                Filter
            </button>
            @if ($search !== '')
                <button type="button" class="attribute-btn attribute-btn-ghost" data-hub-action="list"
                    data-hub-module="{{ $module['key'] }}">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </button>
            @endif
        </form>

        <div class="attribute-filter-meta">
            Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }}
        </div>
    </div>

    <div class="attribute-table-card">
        @if ($items->count())
            <div class="table-responsive">
                <table class="attribute-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr class="attribute-row">
                                <td>
                                    <span class="attribute-id-badge">#{{ $item->id }}</span>
                                </td>
                                <td>
                                    <button type="button" class="attribute-name-button"
                                        data-hub-action="toggle-detail" data-detail-target="detail-{{ $item->id }}">
                                        {{ $item->name }}
                                    </button>
                                </td>
                                <td>
                                    <span class="attribute-status-badge {{ $item->is_active ? 'active' : 'inactive' }}">
                                        <i class="bi bi-{{ $item->is_active ? 'check-circle' : 'x-circle' }}"></i>
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $item->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="attribute-actions">
                                    @if ($canView)
                                        <button type="button" class="attribute-icon-btn"
                                            data-hub-action="toggle-detail" data-detail-target="detail-{{ $item->id }}"
                                            title="Details">
                                            <i class="bi bi-arrows-collapse"></i>
                                        </button>
                                    @endif

                                    @if ($canEdit)
                                        <button type="button" class="attribute-icon-btn attribute-icon-btn-edit"
                                            data-hub-action="edit" data-hub-module="{{ $module['key'] }}"
                                            data-hub-id="{{ $item->id }}" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif

                                    @if ($canDelete)
                                        <form action="{{ route($module['destroy_route_name'], $item->id) }}" method="POST"
                                            class="attribute-delete-form" data-hub-delete-form>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="attribute-icon-btn attribute-icon-btn-delete"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            <tr class="attribute-detail-row {{ $expandedItemId === $item->id ? '' : 'd-none' }}"
                                data-detail-row="{{ $item->id }}" id="detail-{{ $item->id }}">
                                <td colspan="5">
                                    <div class="attribute-detail-box">
                                        <div class="attribute-detail-grid">
                                            <div class="attribute-detail-item">
                                                <span>Record ID</span>
                                                <strong>#{{ $item->id }}</strong>
                                            </div>
                                            <div class="attribute-detail-item">
                                                <span>Name</span>
                                                <strong>{{ $item->name }}</strong>
                                            </div>
                                            <div class="attribute-detail-item">
                                                <span>Status</span>
                                                <strong>{{ $item->is_active ? 'Active' : 'Inactive' }}</strong>
                                            </div>
                                            <div class="attribute-detail-item">
                                                <span>Created At</span>
                                                <strong>{{ $item->created_at?->format('M d, Y H:i') ?? 'N/A' }}</strong>
                                            </div>
                                            <div class="attribute-detail-item">
                                                <span>Updated At</span>
                                                <strong>{{ $item->updated_at?->format('M d, Y H:i') ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="attribute-pagination">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="attribute-empty-state">
                <div class="attribute-empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3>No {{ strtolower($module['label']) }} found</h3>
                <p>
                    {{ $search !== '' ? 'Try another search term or clear the filter.' : 'Add the first record to start using this module.' }}
                </p>
                @if ($canCreate)
                    <button type="button" class="attribute-btn attribute-btn-primary" data-hub-action="create"
                        data-hub-module="{{ $module['key'] }}">
                        <i class="bi bi-plus-circle"></i>
                        Add New
                    </button>
                @endif
            </div>
        @endif
    </div>
</section>
