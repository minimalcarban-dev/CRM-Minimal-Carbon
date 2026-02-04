@extends('layouts.admin')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="text-white mb-0">Meele Diamonds</h2>
                            <p class="text-gray-400 mb-0">Manage loose diamond parcels and stock.</p>
                        </div>
                        <a href="{{ route('meele-parcels.create') }}"
                            class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="ti ti-plus"></i> Add Parcel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold mb-3">Total Weight</h6>
                    <div class="d-flex align-items-end mb-2">
                        <h3 class="mb-0">{{ number_format($totalWeight, 4) }} <small class="text-muted fs-6">ct</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-semibold mb-3">Total Pieces</h6>
                    <div class="d-flex align-items-end mb-2">
                        <h3 class="mb-0">{{ number_format($totalPieces) }} <small class="text-muted fs-6">pcs</small></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Parcel ID</th>
                            <th>Sieve Size</th>
                            <th>Category</th>
                            <th>Pieces</th>
                            <th>Weight (ct)</th>
                            <th>Avg Cost/Ct</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parcels as $parcel)
                            <tr>
                                <td class="fw-bold">{{ $parcel->parcel_code }}</td>
                                <td>{{ $parcel->sieve_size }}</td>
                                <td><span class="badge bg-secondary">{{ $parcel->category }}</span></td>
                                <td>{{ number_format($parcel->current_pieces) }}</td>
                                <td class="text-primary fw-bold">{{ number_format($parcel->current_weight, 4) }}</td>
                                <td>${{ number_format($parcel->avg_cost_per_carat, 2) }}</td>
                                <td>
                                    @if($parcel->status === 'active')
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @elseif($parcel->status === 'low_stock')
                                        <span class="badge bg-warning-subtle text-warning">Low Stock</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">{{ ucfirst($parcel->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('meele-parcels.show', $parcel->id) }}" class="btn btn-sm btn-light">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No parcels found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $parcels->links() }}
            </div>
        </div>
    </div>
@endsection