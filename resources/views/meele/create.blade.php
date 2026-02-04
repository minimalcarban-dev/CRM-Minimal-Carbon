@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add New Meele Parcel</h5>
                <a href="{{ route('meele-parcels.index') }}" class="btn btn-light btn-sm">Back to List</a>
            </div>
            <div class="card-body">
                <form action="{{ route('meele-parcels.store') }}" method="POST">
                    @csrf

                    <h6 class="mb-3 text-uppercase text-primary">Parcel Identification</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Parcel Code <span class="text-danger">*</span></label>
                            <input type="text" name="parcel_code" class="form-control" required
                                placeholder="e.g. MD-2024-001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sieve Size <span class="text-danger">*</span></label>
                            <select name="sieve_size" class="form-select" required>
                                <option value="">Select Size...</option>
                                <option value="000-00">000 - 00 (0.80-0.90 mm)</option>
                                <option value="00-0">00 - 0 (0.90-1.00 mm)</option>
                                <option value="+0000">+0000 (1.00-1.10 mm)</option>
                                <option value="+2">+2 (1.20-1.30 mm)</option>
                                <option value="+6">+6 (1.80-2.30 mm)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="Stars">Stars</option>
                                <option value="Meele">Meele</option>
                                <option value="Coarse">Coarse</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="mb-3 text-uppercase text-primary">Initial Stock & Valuation</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Initial Pieces</label>
                            <input type="number" name="initial_pieces" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Initial Weight (ct) <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" name="initial_weight" class="form-control" value="0.0000"
                                min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cost per Carat ($)</label>
                            <input type="number" step="0.01" name="avg_cost_per_carat" class="form-control" value="0.00"
                                min="0">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">Create Parcel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection