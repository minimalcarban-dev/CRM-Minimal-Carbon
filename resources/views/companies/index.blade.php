@extends('layouts.admin')

@section('title', 'Companies Management')

@php
    // Calculate stats
    $totalCompanies = $companies->total();
    $activeCount = 0;
    $inactiveCount = 0;

    foreach ($companies as $company) {
        if ($company->status === 'active') {
            $activeCount++;
        } else {
            $inactiveCount++;
        }
    }
@endphp

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title mb-0">Company List</h1>
            <p class="page-subtitle">Manage all registered companies in the system.</p>
        </div>
        <div>
            <a href="{{ route('companies.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Company
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <form method="GET" action="{{ route('companies.index') }}" class="mb-3 d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Search company name or email..."
            value="{{ request('search') }}">
        <select name="status" class="form-select" style="max-width: 180px;">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->has('search') || request()->has('status'))
            <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">Reset</a>
        @endif
    </form>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Companies Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Company Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $company->name }}</td>
                            <td>{{ $company->email ?? '—' }}</td>
                            <td>{{ $company->phone ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $company->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($company->status) }}
                                </span>
                            </td>
                            <td>{{ $company->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('companies.edit', $company->id) }}"
                                    class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('companies.destroy', $company->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this company?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">No companies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $companies->links() }}
            </div>
        </div>
    </div>
@endsection