@extends('layouts.admin')

@section('title', 'Add Company')

@section('content')
    <div class="form-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ url('/admin/dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('companies.index') }}" class="breadcrumb-link">
                            Companies
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Add Company</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-building-add"></i>
                        Add New Company
                    </h1>
                    <p class="page-subtitle">Create a new company profile in the system</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('companies.index') }}" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="form-card-title">
                    <h2>Company Information</h2>
                    <p>Fill in the details below to add a new company</p>
                </div>
            </div>

            <div class="form-card-body">
                @if ($errors->any())
                    <div class="alert-danger-custom">
                        <div class="alert-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="alert-content">
                            <h4 class="alert-title">Validation Errors</h4>
                            <p class="alert-message">Please fix the following issues:</p>
                            <ul class="alert-list">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('companies.store') }}" method="POST" id="companyForm" enctype="multipart/form-data">
                    @csrf
                    @include('companies.form', ['company' => null])

                    <div class="form-actions">
                        <a href="{{ route('companies.index') }}" class="btn-cancel">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancel</span>
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-check-circle"></i>
                            <span>Save Company</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('companies.styles')
@endsection