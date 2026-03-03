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
            max-width: 1500px;
        }

        [data-theme="dark"] .container {
            background: var(--bg-body, #0f172a);
        }

        [data-theme="dark"] h2,
        [data-theme="dark"] h6 {
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] .text-muted,
        [data-theme="dark"] p {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .alert-danger {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.35);
            color: #fecaca;
        }

        .btn-outline-secondary {
            border-radius: 8px;
            font-weight: 500;
        }

        [data-theme="dark"] .btn-outline-secondary {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(148, 163, 184, 0.35);
            color: var(--text-secondary, #94a3b8);
        }

        .btn-outline-secondary:hover {
            background: #f1f5f9;
        }

        [data-theme="dark"] .btn-outline-secondary:hover {
            background: rgba(99, 102, 241, 0.12);
            border-color: rgba(129, 140, 248, 0.55);
            color: #c7d2fe;
        }
    </style>
@endsection
