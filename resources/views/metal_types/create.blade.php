@extends('layouts.admin')

@section('title', 'Create Metal Type')

@section('content')
    <h3>Create Metal Type</h3>

    <form method="POST" action="{{ route('metal_types.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active') ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>

        <button class="btn btn-primary">Create</button>
        <a href="{{ route('metal_types.index') }}" class="btn btn-secondary">Back</a>
    </form>

@endsection
