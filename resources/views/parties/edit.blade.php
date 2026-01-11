@extends('layouts.admin')

@section('title', 'Edit Party')

@section('content')
    <div class="container py-4">
        <h3>Edit Party</h3>
        <form action="{{ route('parties.update', $party->id) }}" method="POST">
            @method('PUT')
            @include('parties._form')
        </form>
    </div>
@endsection