@extends('layouts.admin')

@section('title', 'Add Company')

@section('content')
    <div class="companies-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('companies.index') }}" class="breadcrumb-link">
                            Companies
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Add New Company</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-plus-circle"></i>
                        Add New Company
                    </h1>
                    <p class="page-subtitle">Fill in the details below to register a new company</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('companies.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        @if ($errors->any())
            <div class="alert-card danger">
                <div class="alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="alert-content">
                    <h5 class="alert-title">Please Correct the Following Errors</h5>
                    <ul class="error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('companies.store') }}" method="POST" id="companyForm">
            @csrf

            <!-- Company Form Card -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Company Information</h5>
                            <p class="section-description">Enter the company details and contact information</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    @include('companies.form')
                </div>
            </div>

            <!-- Action Footer -->
            <div class="action-footer">
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-circle"></i>
                    <span>Save Company</span>
                </button>
                <a href="{{ route('companies.index') }}" class="btn-secondary-custom">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>

    @endsection
