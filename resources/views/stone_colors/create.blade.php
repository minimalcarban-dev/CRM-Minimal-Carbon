@extends('layouts.admin')

@section('title', 'Create Stone Color')

@section('content')
    <h3>Create Stone Color</h3>

    <form method="POST" action="{{ route('stone_colors.store') }}">
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
        <a href="{{ route('stone_colors.index') }}" class="btn btn-secondary">Back</a>
    </form>

@endsection
