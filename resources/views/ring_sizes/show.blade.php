@extends('layouts.admin')

@section('title', 'Ring Size')

@section('content')
    <h3>Ring Size</h3>

    <dl class="row">
        <dt class="col-sm-3">ID</dt>
        <dd class="col-sm-9">{{ $item->id }}</dd>

        <dt class="col-sm-3">Name</dt>
        <dd class="col-sm-9">{{ $item->name }}</dd>

        <dt class="col-sm-3">Active</dt>
        <dd class="col-sm-9">{{ $item->is_active ? 'Yes' : 'No' }}</dd>

        <dt class="col-sm-3">Created</dt>
        <dd class="col-sm-9">{{ $item->created_at?->format('Y-m-d') ?? '—' }}</dd>
    </dl>

    <a href="{{ route('ring_sizes.index') }}" class="btn btn-secondary">Back</a>
    @if ($currentAdmin && $currentAdmin->hasPermission('ring_sizes.edit'))
        <a href="{{ route('ring_sizes.edit', $item->id) }}" class="btn btn-primary">Edit</a>
    @endif
@endsection
