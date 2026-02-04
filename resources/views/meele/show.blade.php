@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0">{{ $parcel->parcel_code }}</h3>
                <span class="text-muted">{{ $parcel->sieve_size }} • {{ $parcel->category }}</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                    <i class="ti ti-arrows-exchange me-1"></i> Stock Adjustment
                </button>
                <a href="{{ route('meele-parcels.index') }}" class="btn btn-light">Back</a>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <label class="text-muted text-uppercase fs-2">Available Stock</label>
                        <div class="d-flex align-items-baseline mt-2">
                            <h2 class="mb-0 me-2">{{ number_format($parcel->current_weight, 4) }}</h2>
                            <span class="text-muted">ct</span>
                        </div>
                        <div class="text-muted small mt-1">{{ number_format($parcel->current_pieces) }} pieces</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <label class="text-muted text-uppercase fs-2">Valuation</label>
                        <div class="d-flex align-items-baseline mt-2">
                            <h3 class="mb-0">${{ number_format($parcel->avg_cost_per_carat, 2) }}</h3>
                            <span class="text-muted ms-1">/ ct</span>
                        </div>
                        <div class="text-primary small mt-1">
                            Total Value: ${{ number_format($parcel->current_weight * $parcel->avg_cost_per_carat, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <label class="text-muted text-uppercase fs-2">Status</label>
                        <div class="mt-2">
                            @if($parcel->status == 'active')
                                <span class="badge bg-success fs-3 px-3 py-2">Active</span>
                            @else
                                <span class="badge bg-secondary fs-3 px-3 py-2">{{ ucfirst($parcel->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transaction History</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Pieces</th>
                            <th>Weight</th>
                            <th>User</th>
                            <th>Reference</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parcel->transactions->sortByDesc('created_at') as $trans)
                            <tr>
                                <td>{{ $trans->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if(in_array($trans->type, ['purchase', 'adjustment_add', 'initial', 'return']))
                                        <span class="badge bg-success-subtle text-success">{{ ucfirst($trans->type) }}</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">{{ ucfirst($trans->type) }}</span>
                                    @endif
                                </td>
                                <td class="{{ $trans->pieces > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $trans->pieces > 0 ? '+' : '' }}{{ $trans->pieces }}
                                </td>
                                <td class="{{ $trans->weight > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ $trans->weight > 0 ? '+' : '' }}{{ number_format($trans->weight, 4) }}
                                </td>
                                <td>{{ $trans->user->name ?? 'System' }}</td>
                                <td>
                                    @if($trans->reference_id)
                                        <span class="badge bg-light text-dark border">Order #{{ $trans->reference_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{Str::limit($trans->description, 30)}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div class="modal fade" id="adjustmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('meele-parcels.adjustment', $parcel->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Stock Adjustment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Action Type</label>
                            <select name="type" class="form-select" required>
                                <option value="add">Add Stock (+)</option>
                                <option value="subtract">Deduct Stock (-)</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Weight (ct)</label>
                                <input type="number" step="0.0001" name="weight" class="form-control" required min="0.0001">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Pieces</label>
                                <input type="number" name="pieces" class="form-control" required min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason / Remarks</label>
                            <textarea name="reason" class="form-control" rows="2" required
                                placeholder="e.g. Audit correction, broken stone..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Adjustment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection