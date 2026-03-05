@extends('layouts.admin')

@section('title', 'Shopify Sync Logs')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--dark);">
                <i class="bi bi-journal-text me-2" style="color: var(--warning);"></i>Sync Logs
            </h4>
            <p class="text-muted mb-0">API activity and synchronization history</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <select class="form-select" name="action">
                        <option value="">All Actions</option>
                        <option value="Import" {{ request('action') == 'Import' ? 'selected' : '' }}>Import</option>
                        <option value="Export" {{ request('action') == 'Export' ? 'selected' : '' }}>Export</option>
                        <option value="Sync" {{ request('action') == 'Sync' ? 'selected' : '' }}>Sync</option>
                        <option value="Webhook" {{ request('action') == 'Webhook' ? 'selected' : '' }}>Webhook</option>
                        <option value="TestConnection" {{ request('action') == 'TestConnection' ? 'selected' : '' }}>Test Connection</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="Success" {{ request('status') == 'Success' ? 'selected' : '' }}>Success</option>
                        <option value="Failed" {{ request('status') == 'Failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background: var(--light-gray);">
                    <tr>
                        <th class="ps-4" style="width: 140px;">Action</th>
                        <th style="width: 100px;">Entity</th>
                        <th style="width: 120px;">Entity ID</th>
                        <th style="width: 90px;">Status</th>
                        <th>Message</th>
                        <th style="width: 160px;">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-4">
                            @php
                                $actionIcons = [
                                    'Import' => 'bi-cloud-download text-primary',
                                    'Export' => 'bi-cloud-upload text-success',
                                    'Sync'   => 'bi-arrow-repeat text-info',
                                    'Webhook'=> 'bi-broadcast text-warning',
                                    'TestConnection' => 'bi-lightning text-primary',
                                ];
                            @endphp
                            <i class="bi {{ $actionIcons[$log->action] ?? 'bi-circle' }} me-1"></i>
                            {{ $log->action }}
                        </td>
                        <td>{{ $log->entity_type }}</td>
                        <td><code>{{ $log->entity_id ?? '—' }}</code></td>
                        <td>
                            @if($log->status === 'Success')
                                <span class="badge bg-success">Success</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        <td>
                            <small style="color: var(--gray);">{{ Str::limit($log->response_message, 100) }}</small>
                        </td>
                        <td>
                            <small class="text-muted" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                                {{ $log->created_at->diffForHumans() }}
                            </small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem;"></i>
                                No sync logs yet. Activity will appear here after imports, exports, or webhooks.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
    <div class="pagination-container">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
