@extends('layouts.admin')

@section('title', 'Stone Colors')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Stone Colors</h3>
        <div class="d-flex">
            <form method="GET" class="me-2">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name"
                        value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary">Search</button>
                </div>
            </form>
            @if ($currentAdmin && $currentAdmin->hasPermission('stone_colors.create'))
                <a href="{{ route('stone_colors.create') }}" class="btn btn-success">Create</a>
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
                            @if ($currentAdmin && $currentAdmin->hasPermission('stone_colors.view'))
                                <a href="{{ route('stone_colors.show', $item->id) }}" class="btn btn-sm btn-info">View</a>
                            @endif
                            @if ($currentAdmin && $currentAdmin->hasPermission('stone_colors.edit'))
                                <a href="{{ route('stone_colors.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @endif
                            @if ($currentAdmin && $currentAdmin->hasPermission('stone_colors.delete'))
                                <form action="{{ route('stone_colors.destroy', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this item?')">
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
