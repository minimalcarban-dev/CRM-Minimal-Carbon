@extends('layouts.admin')

@section('title', 'Shopify Products')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--dark);">
                <i class="bi bi-box-seam me-2" style="color: var(--primary);"></i>Shopify Products
            </h4>
            <p class="text-muted mb-0">{{ $products->total() }} products synced from Shopify</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('shopify.products.import') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Importing...'; this.form.submit();">
                    <i class="bi bi-cloud-download me-2"></i>Import from Shopify
                </button>
            </form>
            <a href="{{ route('shopify.settings') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear"></i>
            </a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Search by title, SKU, or vendor..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Products Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background: var(--light-gray);">
                    <tr>
                        <th style="width: 60px;" class="ps-4">Image</th>
                        <th>Title</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Synced</th>
                        <th style="width: 140px;" class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr>
                        <td class="ps-4">
                            @if($p->primary_image)
                                <img src="{{ $p->primary_image }}" alt="{{ $p->title }}"
                                     style="width: 45px; height: 45px; object-fit: cover; border-radius: 10px; border: 2px solid var(--border);">
                            @else
                                <div style="width: 45px; height: 45px; border-radius: 10px; background: var(--light-gray); display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('shopify.products.show', $p->id) }}" class="fw-semibold text-decoration-none" style="color: var(--dark);">
                                {{ Str::limit($p->title, 45) }}
                            </a>
                            @if($p->vendor)
                                <br><small class="text-muted">{{ $p->vendor }}</small>
                            @endif
                        </td>
                        <td><code>{{ $p->sku ?? '—' }}</code></td>
                        <td class="fw-semibold">${{ number_format($p->price, 2) }}</td>
                        <td>{{ $p->inventory_quantity }}</td>
                        <td>
                            @php
                                $statusColors = ['active' => 'success', 'draft' => 'warning', 'archived' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$p->status] ?? 'secondary' }}">{{ ucfirst($p->status) }}</span>
                        </td>
                        <td>
                            @if($p->last_synced_at)
                                <small class="text-muted" title="{{ $p->last_synced_at->format('Y-m-d H:i:s') }}">
                                    {{ $p->last_synced_at->diffForHumans() }}
                                </small>
                            @else
                                <small class="text-muted">Never</small>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('shopify.products.show', $p->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('shopify.products.sync', $p->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-info" title="Re-sync">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;"></i>
                                No products imported yet. Click <strong>Import from Shopify</strong> to get started.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="pagination-container">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
