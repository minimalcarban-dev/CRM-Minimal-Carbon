@extends('layouts.admin')

@section('title', 'Setting Types')

@section('content')
<div class="setting-type-index-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="breadcrumb-nav">
                    <a href="{{ url('/') }}" class="breadcrumb-link">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                    <i class="bi bi-chevron-right breadcrumb-separator"></i>
                    <span class="breadcrumb-current">Setting Types</span>
                </div>
                <h1 class="page-title">
                    <i class="bi bi-sliders"></i>
                    Setting Types
                </h1>
                <p class="page-subtitle">Manage your setting types and configurations</p>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="action-bar-content">
            <div class="search-section">
                <form method="GET" class="search-form">
                    <div class="search-input-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input 
                            type="text" 
                            name="search" 
                            class="search-input" 
                            placeholder="Search setting types..." 
                            value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('setting_types.index') }}" class="search-clear">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="btn-search">
                        <i class="bi bi-search"></i>
                        <span>Search</span>
                    </button>
                </form>
            </div>
            
            <div class="action-buttons">
                @if ($currentAdmin && $currentAdmin->hasPermission('setting_types.create'))
                    <a href="{{ route('setting_types.create') }}" class="btn-create">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create New Type</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        @if($items->count() > 0)
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-id">
                                <div class="th-content">
                                    <i class="bi bi-hash"></i>
                                    <span>ID</span>
                                </div>
                            </th>
                            <th class="th-name">
                                <div class="th-content">
                                    <i class="bi bi-tag"></i>
                                    <span>Name</span>
                                </div>
                            </th>
                            <th class="th-status">
                                <div class="th-content">
                                    <i class="bi bi-toggle2-on"></i>
                                    <span>Status</span>
                                </div>
                            </th>
                            <th class="th-date">
                                <div class="th-content">
                                    <i class="bi bi-calendar3"></i>
                                    <span>Created</span>
                                </div>
                            </th>
                            <th class="th-actions">
                                <div class="th-content">
                                    <i class="bi bi-gear"></i>
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr class="table-row">
                                <td class="td-id">
                                    <span class="id-badge">#{{ $item->id }}</span>
                                </td>
                                <td class="td-name">
                                    <div class="name-cell">
                                        <span class="name-text">{{ $item->name }}</span>
                                    </div>
                                </td>
                                <td class="td-status">
                                    @if($item->is_active)
                                        <span class="status-badge status-active">
                                            <i class="bi bi-check-circle-fill"></i>
                                            <span>Active</span>
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="bi bi-x-circle-fill"></i>
                                            <span>Inactive</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="td-date">
                                    <div class="date-cell">
                                        <i class="bi bi-calendar3"></i>
                                        <span>{{ $item->created_at?->format('M d, Y') ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="td-actions">
                                    <div class="action-buttons-cell">
                                        @if ($currentAdmin && $currentAdmin->hasPermission('setting_types.view'))
                                            <a href="{{ route('setting_types.show', $item->id) }}" 
                                                class="action-btn btn-view"
                                                title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                        @if ($currentAdmin && $currentAdmin->hasPermission('setting_types.edit'))
                                            <a href="{{ route('setting_types.edit', $item->id) }}" 
                                                class="action-btn btn-edit"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if ($currentAdmin && $currentAdmin->hasPermission('setting_types.delete'))
                                            <form action="{{ route('setting_types.destroy', $item->id) }}" 
                                                method="POST" 
                                                class="delete-form"
                                                onsubmit="return confirm('Are you sure you want to delete this setting type?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                    class="action-btn btn-delete"
                                                    title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($items->hasPages())
                <div class="pagination-footer">
                    <div class="pagination-info">
                        <i class="bi bi-info-circle"></i>
                        <span>Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} entries</span>
                    </div>
                    <div class="pagination-links">
                        {{ $items->links() }}
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 class="empty-title">No setting types found</h3>
                <p class="empty-description">
                    @if(request('search'))
                        No results match your search criteria. Try adjusting your search terms.
                    @else
                        Get started by creating your first setting type to organize your settings.
                    @endif
                </p>
                <div class="empty-actions">
                    @if(request('search'))
                        <a href="{{ route('setting_types.index') }}" class="btn-empty-action">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Clear Search</span>
                        </a>
                    @elseif($currentAdmin && $currentAdmin->hasPermission('setting_types.create'))
                        <a href="{{ route('setting_types.create') }}" class="btn-empty-action primary">
                            <i class="bi bi-plus-circle"></i>
                            <span>Create First Setting Type</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-focus search input if there's a search query
        const searchInput = document.querySelector('.search-input');
        if (searchInput && searchInput.value) {
            searchInput.focus();
        }

        // Add animation to table rows on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.table-row').forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            row.style.transition = `all 0.4s ease ${index * 0.05}s`;
            observer.observe(row);
        });
    });
</script>

@endsection
