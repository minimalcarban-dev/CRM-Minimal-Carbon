@extends('layouts.admin')

@section('title', 'Edit Diamond')

@section('content')
    <div class="diamond-management-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="{{ route('diamond.index') }}" class="breadcrumb-link">
                            Diamonds
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Edit Diamond #{{ $diamond->stockid }}</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-pencil-square"></i>
                        Edit Diamond
                    </h1>
                    <p class="page-subtitle">Update the diamond information below (SKU: {{ $diamond->sku }})</p>
                </div>
                <div class="header-right">
                    <a href="{{ route('diamond.index') }}" class="btn-secondary-custom">
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

        <form action="{{ route('diamond.update', $diamond) }}" method="POST" id="diamondForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Diamond Form Card -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Diamond Information</h5>
                            <p class="section-description">Update the diamond details and specifications</p>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="stockid" class="form-label">
                                Stock ID <span class="required">*</span>
                            </label>
                            <input type="number" class="form-control" id="stockid" name="stockid" 
                                   value="{{ old('stockid', $diamond->stockid) }}" required>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Unique integer identifier for the diamond
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="sku" class="form-label">
                                SKU <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="sku" name="sku" 
                                   value="{{ old('sku', $diamond->sku) }}" required>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Unique SKU (will be used in barcode)
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="price" class="form-label">
                                Price <span class="required">*</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-currency-dollar input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="price" 
                                       name="price" value="{{ old('price', $diamond->price) }}" required>
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Base price in your currency
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="listing_price" class="form-label">
                                Listing Price
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-tag input-icon"></i>
                                <input type="number" step="0.01" class="form-control" id="listing_price" 
                                       name="listing_price" value="{{ old('listing_price', $diamond->listing_price) }}">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Leave empty to auto-calculate (25% more than price)
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="shape" class="form-label">
                                Shape
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-diamond input-icon"></i>
                                <input type="text" class="form-control" id="shape" name="shape" 
                                       value="{{ old('shape', $diamond->shape) }}">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                E.g., Round, Princess, Emerald
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="measurement" class="form-label">
                                Measurement
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-rulers input-icon"></i>
                                <input type="text" class="form-control" id="measurement" name="measurement" 
                                       value="{{ old('measurement', $diamond->measurement) }}">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Dimensions in millimeters
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="number_of_pics" class="form-label">
                                Number of Piece
                            </label>
                            <div class="input-with-icon">
                                <i class="bi bi-gem input-icon"></i>
                                <input type="number" min="0" class="form-control" id="number_of_pics" 
                                       name="number_of_pics" value="{{ old('number_of_pics', $diamond->number_of_pics) }}">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Total available product Piece
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <!-- Additional Details Section -->
            <div class="section-body p-0">
                <div class="form-grid">
                    <div class="form-section-card">
                        <div class="section-header">
                            <div class="section-info">
                                <div class="section-icon">
                                    <i class="bi bi-info-square"></i>
                                </div>
                                <div>
                                    <h5 class="section-title">Additional Details</h5>
                                    <p class="section-description">Add notes, description and assignment</p>
                                </div>
                            </div>
                        </div>
                        <div class="section-body">
                            <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="description" class="form-label">
                                    Description
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                        placeholder="Enter diamond description">{{ old('description', $diamond->description) }}</textarea>
                                <div class="form-hint">
                                    <i class="bi bi-info-circle"></i>
                                    Detailed description of the diamond
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="diamond_type" class="form-label">
                                    Diamond Type
                                </label>
                                <div class="input-with-icon">
                                    <i class="bi bi-gem input-icon"></i>
                                    <input type="text" class="form-control" id="diamond_type" name="diamond_type" 
                                        placeholder="e.g., Natural, Lab-created" value="{{ old('diamond_type', $diamond->diamond_type) }}">
                                </div>
                                <div class="form-hint">
                                    <i class="bi bi-info-circle"></i>
                                    Type of diamond (Natural/Lab-created)
                                </div>
                            </div>
                            <div class="form-group full-width">
                                <label for="admin_id" class="form-label">
                                    Assign To Admin
                                </label>
                                <select class="form-control" id="admin_id" name="admin_id">
                                    <option value="">-- Select an Admin --</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}" 
                                            {{ old('admin_id', $diamond->admin_id) == $admin->id ? 'selected' : '' }}>
                                            {{ $admin->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-hint">
                                    <i class="bi bi-info-circle"></i>
                                    Select one admin to assign this diamond
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label for="note" class="form-label">
                                    Internal Note
                                </label>
                                <textarea class="form-control" id="note" name="note" rows="3" 
                                        placeholder="Enter internal notes">{{ old('note', $diamond->note) }}</textarea>
                                <div class="form-hint">
                                    <i class="bi bi-info-circle"></i>
                                    Private notes for internal use
                                </div>
                            </div>

                            

                            <div class="form-group full-width">
                                <label for="multi_img_upload" class="form-label">
                                    Upload Additional Images
                                </label>
                                <div class="file-upload-area">
                                    <input type="file" class="form-control" id="multi_img_upload" name="multi_img_upload[]" 
                                        multiple accept="image/jpeg,image/png,image/jpg,image/gif">
                                    <div class="form-hint">
                                        <i class="bi bi-info-circle"></i>
                                        You can upload multiple images (JPEG, PNG, JPG, GIF). Max 2MB each.
                                    </div>
                                </div>
                                @if($diamond->multi_img_upload && is_array($diamond->multi_img_upload))
                                    <div style="margin-top: 12px;">
                                        <p class="text-semibold" style="margin-bottom: 8px;">Existing Images:</p>
                                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                            @foreach($diamond->multi_img_upload as $img)
                                                <img src="{{ $img }}" alt="Diamond image" style="max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            <!-- Action Footer -->
            <div class="action-footer">
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-circle"></i>
                    <span>Update Diamond</span>
                </button>
                <button type="button" class="btn-secondary-custom" onclick="window.history.back()">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancel</span>
                </button>
            </div>
        </form>
    </div>

    @endsection
