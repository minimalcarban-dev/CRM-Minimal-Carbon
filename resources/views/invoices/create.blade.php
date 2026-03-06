@extends('layouts.admin')

@section('title', 'Create Invoice')

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
                        <span class="breadcrumb-current">Create Invoice</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-file-earmark-plus"></i>
                        Create Invoice
                    </h1>
                    <p class="page-subtitle">Fill in the required details to create a new invoice</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('invoices.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
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

@endsection