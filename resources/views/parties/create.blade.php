@extends('layouts.admin')

@section('title', 'Create Party')

@section('content')
    <div class="container py-4">
        <h3>Create Party</h3>
        <form action="{{ route('parties.store') }}" method="POST">
            @include('parties._form')
        </form>
    </div>
@endsection
