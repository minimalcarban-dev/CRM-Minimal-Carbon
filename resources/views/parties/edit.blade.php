@extends('layouts.admin')

@section('title', 'Edit Party')

@section('content')
    <div class="container py-4 party-editor-page">
        <h3 class="party-editor-title">Edit Party</h3>
        <form action="{{ route('parties.update', $party->id) }}" method="POST">
            @method('PUT')
            @include('parties._form')
        </form>
    </div>

    <style>
        [data-theme="dark"] .party-editor-page {
            color: #cbd5e1;
        }

        [data-theme="dark"] .party-editor-title {
            color: #e2e8f0;
        }
    </style>
@endsection
