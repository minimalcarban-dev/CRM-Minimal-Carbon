@extends('layouts.admin')

@section('title', 'Stone Type')

@section('content')
    <h3>Stone Type</h3>

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

    <a href="{{ route('stone_types.index') }}" class="btn btn-secondary">Back</a>
    @if ($currentAdmin && $currentAdmin->hasPermission('stone_types.edit'))
        <a href="{{ route('stone_types.edit', $item->id) }}" class="btn btn-primary">Edit</a>
    @endif
@endsection
