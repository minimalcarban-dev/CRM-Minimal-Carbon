@extends('layouts.admin')

@section('title', 'Shopify Collections')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--dark);">
                <i class="bi bi-collection me-2" style="color: var(--success);"></i>Shopify Collections
            </h4>
            <p class="text-muted mb-0">{{ $collections->total() }} collections synced</p>
        </div>
        <form action="{{ route('shopify.collections.import') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Importing...'; this.form.submit();">
                <i class="bi bi-cloud-download me-2"></i>Import Collections
            </button>
        </form>
    </div>

    {{-- Collections Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background: var(--light-gray);">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Handle</th>
                        <th>Shopify ID</th>
                        <th>Synced At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collections as $c)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $c->title }}</td>
                        <td><code>{{ $c->handle ?? '—' }}</code></td>
                        <td><code>{{ $c->shopify_collection_id }}</code></td>
                        <td>
                            <small class="text-muted">{{ $c->updated_at->diffForHumans() }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-collection" style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;"></i>
                                No collections imported yet. Click <strong>Import Collections</strong> to sync.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($collections->hasPages())
    <div class="pagination-container">
        {{ $collections->links() }}
    </div>
    @endif
</div>
@endsection
