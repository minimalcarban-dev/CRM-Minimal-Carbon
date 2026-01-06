@extends('layouts.admin')

@section('title', 'Create Invoice')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-plus text-primary"></i>
                    Create Invoice
                </h2>
                <p class="text-muted mb-0">Fill in the required details to create a new invoice</p>
            </div>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Invoices
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <h6 class="mb-2"><i class="bi bi-exclamation-triangle"></i> Please fix the following errors:</h6>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('invoices.store') }}">
            @include('invoices._form')
        </form>
    </div>

    <style>
        .container {
            max-width: 1200px;
        }

        .btn-outline-secondary {
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-outline-secondary:hover {
            background: #f1f5f9;
        }
    </style>
@endsection