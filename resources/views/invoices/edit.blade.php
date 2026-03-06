@extends('layouts.admin')

@section('title', 'Edit Invoice')

@section('content')
    <div class="diamond-management-container tracker-page invoice-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('invoices.index') }}" class="breadcrumb-link">Invoices</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Edit Invoice</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-pencil-square"></i>
                        Edit Invoice #{{ $invoice->invoice_no }}
                    </h1>
                    <p class="page-subtitle">Update the invoice details and save your changes</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('invoices.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        @if ($errors->any())
            <div class="alert alert-error">
                <div class="alert-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </div>
                <div class="alert-content">
                    <h6 class="alert-title">Please fix the following errors:</h6>
                    <ul class="alert-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Form Container -->
        <div class="form-container">
            <form method="post" action="{{ route('invoices.update', $invoice->id) }}" class="invoice-form">
                @method('PUT')
                @include('invoices._form')
            </form>
        </div>
    </div>

@endsection