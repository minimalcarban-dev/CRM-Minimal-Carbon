@extends('layouts.admin')

@section('title', 'Stone Shape')

@section('content')
    <h3>Stone Shape</h3>

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

    <a href="{{ route('stone_shapes.index') }}" class="btn btn-secondary">Back</a>
    @if ($currentAdmin && $currentAdmin->hasPermission('stone_shapes.edit'))
        <a href="{{ route('stone_shapes.edit', $item->id) }}" class="btn btn-primary">Edit</a>
    @endif
@endsection
