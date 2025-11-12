@extends('layouts.admin')

@section('title', 'Ring Sizes')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Ring Sizes</h3>
        <div class="d-flex">
            <form method="GET" class="me-2">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name"
                        value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary">Search</button>
                </div>
            </form>
            @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.create'))
                <a href="{{ route('ring_sizes.create') }}" class="btn btn-success">Create</a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Active</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $item->created_at?->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.view'))
                                <a href="{{ route('ring_sizes.show', $item->id) }}" class="btn btn-sm btn-info">View</a>
                            @endif
                            @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.edit'))
                                <a href="{{ route('ring_sizes.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @endif
                            @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.delete'))
                                <form action="{{ route('ring_sizes.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $items->links() }}
    </div>


@endsection
