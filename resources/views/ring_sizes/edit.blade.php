@extends('layouts.admin')

@section('title', 'Edit Ring Size')

@section('content')
    <h3>Edit Ring Size</h3>

    <form method="POST" action="{{ route('ring_sizes.update', $item->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}">
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>

        <button class="btn btn-primary">Update</button>
        <a href="{{ route('ring_sizes.index') }}" class="btn btn-secondary">Back</a>
    </form>

@endsection
