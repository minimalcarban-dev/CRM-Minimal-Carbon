@extends('layouts.admin')

@section('title', 'Edit Company')

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
                        <span class="breadcrumb-current">Edit Company</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-building-gear"></i>
                        Edit Company
                    </h1>
                    <p class="page-subtitle">Update company information</p>
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
                    <p>Update the company details below</p>
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

                <form action="{{ route('companies.update', $company->id) }}" method="POST" id="companyForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('companies.form', ['company' => $company])

                    <div class="form-actions">
                        <a href="{{ route('companies.index') }}" class="btn-cancel">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancel</span>
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-save"></i>
                            <span>Update Company</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('companies.styles')
@endsection