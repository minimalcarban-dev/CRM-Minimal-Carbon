@extends('layouts.admin')

@section('title', 'Closure Types')

@section('content')
    <div class="attr-list-container">
        <div class="attr-list-header">
            <div class="attr-header-content">
                <div class="attr-header-left">
                    <div class="attr-breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="attr-breadcrumb-link"><i
                                class="bi bi-house-door"></i> Dashboard</a>
                        <i class="bi bi-chevron-right attr-breadcrumb-separator"></i>
                        <span class="attr-breadcrumb-current">Closure Types</span>
                    </div>
                    <h1 class="attr-list-title"><i class="bi bi-lock"></i> Closure Types</h1>
                    <p class="attr-list-subtitle">Manage all closure types in your inventory</p>
                </div>
                @if ($currentAdmin && $currentAdmin->hasPermission('closure_types.create'))
                    <div class="attr-header-right">
                        <a href="{{ route('closure_types.create') }}" class="attr-btn-create"><i class="bi bi-plus-circle"></i>
                            Add Closure Type</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="attr-filter-section">
            <form method="GET" class="attr-filter-form">
                <div class="attr-search-box"><i class="bi bi-search attr-search-icon"></i><input type="text" name="search"
                        class="attr-search-input" placeholder="Search..." value="{{ request('search') }}"></div>
                <button type="submit" class="attr-btn-filter"><i class="bi bi-funnel"></i> Filter</button>
                @if(request('search'))<a href="{{ route('closure_types.index') }}" class="attr-btn-reset"><i
                class="bi bi-arrow-counterclockwise"></i> Reset</a>@endif
            </form>
        </div>

        <div class="attr-table-card">
            @if($items->count() > 0)
                <table class="attr-table">
                    <thead>
                        <tr>
                            <th>
                                <div class="attr-th"><i class="bi bi-hash"></i> ID</div>
                            </th>
                            <th>
                                <div class="attr-th"><i class="bi bi-tag"></i> Name</div>
                            </th>
                            <th>
                                <div class="attr-th"><i class="bi bi-toggle-on"></i> Status</div>
                            </th>
                            <th>
                                <div class="attr-th"><i class="bi bi-calendar"></i> Created</div>
                            </th>
                            <th class="attr-th-actions">
                                <div class="attr-th"><i class="bi bi-gear"></i> Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr class="attr-row">
                                <td><span class="attr-id-badge">#{{ $item->id }}</span></td>
                                <td><span class="attr-name">{{ $item->name }}</span></td>
                                <td><span class="attr-status {{ $item->is_active ? 'active' : 'inactive' }}"><i
                                            class="bi bi-{{ $item->is_active ? 'check-circle' : 'x-circle' }}"></i>
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td><span class="attr-date">{{ $item->created_at?->format('M d, Y') ?? '—' }}</span></td>
                                <td class="attr-actions">
                                    @if ($currentAdmin && $currentAdmin->hasPermission('closure_types.view'))<a
                                        href="{{ route('closure_types.show', $item->id) }}" class="attr-action-btn view"><i
                                    class="bi bi-eye"></i></a>@endif
                                    @if ($currentAdmin && $currentAdmin->hasPermission('closure_types.edit'))<a
                                        href="{{ route('closure_types.edit', $item->id) }}" class="attr-action-btn edit"><i
                                    class="bi bi-pencil"></i></a>@endif
                                    @if ($currentAdmin && $currentAdmin->hasPermission('closure_types.delete'))
                                        <form action="{{ route('closure_types.destroy', $item->id) }}" method="POST"
                                            class="d-inline delete-form">@csrf @method('DELETE')<button type="submit"
                                    class="attr-action-btn delete"><i class="bi bi-trash"></i></button></form>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="attr-empty-state">
                    <div class="attr-empty-icon"><i class="bi bi-inbox"></i></div>
                    <h3>No closure types found</h3>
                    <p>Get started by adding your first closure type.</p><a href="{{ route('closure_types.create') }}"
                        class="attr-btn-create"><i class="bi bi-plus-circle"></i> Add Closure Type</a>
                </div>
            @endif
            @if($items->hasPages())
                <div class="attr-pagination">{{ $items->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
    @include('partials.attribute-index-styles')
    <script>document.querySelectorAll('.delete-form').forEach(form => { form.addEventListener('submit', function (e) { e.preventDefault(); if (confirm('Delete this item?')) this.submit(); }); });</script>
@endsection