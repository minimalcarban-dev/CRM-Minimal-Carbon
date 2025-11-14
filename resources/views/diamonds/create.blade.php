@extends('layouts.admin')

@section('title', 'Create New Diamond')

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
                        <span class="breadcrumb-current">Create New</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-gem"></i>
                        Create New Diamond
                    </h1>
                    <p class="page-subtitle">Fill in the details below to add a new diamond to your inventory</p>
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

        <form action="{{ route('diamond.store') }}" method="POST" id="diamondForm" enctype="multipart/form-data">
            @csrf

            <!-- Diamond Form Card -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div>
                            <h5 class="section-title">Diamond Information</h5>
                            <p class="section-description">Enter the basic details and specifications</p>
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
                                   value="{{ old('stockid') }}" required>
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
                                   value="{{ old('sku') }}" required>
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
                                       name="price" value="{{ old('price') }}" required>
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
                                       name="listing_price" value="{{ old('listing_price') }}">
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
                                       value="{{ old('shape') }}">
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
                                       value="{{ old('measurement') }}">
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
                                       name="number_of_pics" value="{{ old('number_of_pics', 0) }}">
                            </div>
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Total available product Piece
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="form-section-card">
                <div class="section-header">
                    <div class="section-info">
                        <div class="section-icon">
                            <i class="bi bi-file-text"></i>
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
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="diamond_type" class="form-label">Diamond Type</label>
                            <input type="text" class="form-control" id="diamond_type" name="diamond_type" 
                                   value="{{ old('diamond_type') }}" placeholder="e.g., Natural, Lab-created">
                        </div>

                        <div class="form-group full-width">
                            <label for="admin_id" class="form-label">Assign To Admin</label>
                            <select class="form-control" id="admin_id" name="admin_id">
                                <option value="">-- Select Admin --</option>
                                @foreach(\App\Models\Admin::orderBy('name')->get() as $admin)
                                    <option value="{{ $admin->id }}" {{ old('admin_id') == $admin->id ? 'selected' : '' }}>
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
                            <label for="note" class="form-label">Note</label>
                            <textarea class="form-control" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="multi_img_upload" class="form-label">Upload Images</label>
                            <input type="file" class="form-control" id="multi_img_upload" name="multi_img_upload[]" 
                                   multiple accept="image/*">
                            <div class="form-hint">
                                <i class="bi bi-info-circle"></i>
                                Upload multiple diamond images (JPEG, PNG, GIF)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Footer -->
            <div class="action-footer">
                <button type="submit" class="btn-primary-custom">
                    <i class="bi bi-check-circle"></i>
                    <span>Create Diamond</span>
                </button>
                <button type="button" class="btn-secondary-custom" onclick="window.history.back()">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancel</span>
                </button>
            </div>
        </form>
    </div>

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
            --shadow-md: rgba(0, 0, 0, 0.1);
            --shadow-lg: rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
        }

        .diamond-management-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .breadcrumb-link {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-link:hover {
            color: var(--primary);
        }

        .breadcrumb-separator {
            font-size: 0.75rem;
        }

        .breadcrumb-current {
            color: var(--dark);
            font-weight: 500;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary);
        }

        .page-subtitle {
            color: var(--gray);
            margin: 0;
            font-size: 1rem;
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            color: white;
        }

        .btn-secondary-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            color: var(--gray);
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: 2px solid var(--border);
            cursor: pointer;
        }

        .btn-secondary-custom:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        /* Alert Card */
        .alert-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            gap: 1.25rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .alert-card.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.05));
            border: 2px solid rgba(239, 68, 68, 0.2);
        }

        .alert-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .alert-card.danger .alert-icon {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 0.75rem 0;
        }

        .error-list {
            margin: 0;
            padding-left: 1.25rem;
            color: var(--dark);
            font-size: 0.9rem;
            line-height: 1.8;
        }

        .error-list li {
            margin-bottom: 0.5rem;
        }

        /* Form Section Card */
        .form-section-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: all 0.3s;
        }

        .form-section-card:hover {
            box-shadow: 0 4px 12px var(--shadow-md);
        }

        .section-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--light-gray), white);
            border-bottom: 2px solid var(--border);
        }

        .section-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .section-description {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0.25rem 0 0;
        }

        .section-body {
            padding: 2rem;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .required {
            color: var(--danger);
            margin-left: 0.25rem;
        }

        .form-control {
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon .form-control {
            padding-left: 2.75rem;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.125rem;
            pointer-events: none;
        }

        .form-hint {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        .form-hint i {
            font-size: 0.875rem;
        }

        /* Action Footer */
        .action-footer {
            background: white;
            border-radius: 16px;
            padding: 1.5rem 2rem;
            box-shadow: 0 1px 3px var(--shadow);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .diamond-management-container {
                padding: 0;
            }

            .page-header {
                border-radius: 12px;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
            }

            .header-right {
                width: 100%;
            }

            .btn-secondary-custom,
            .btn-primary-custom {
                width: 100%;
                justify-content: center;
            }

            .section-body {
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .action-footer {
                flex-direction: column;
                padding: 1.5rem;
            }

            .action-footer .btn-primary-custom,
            .action-footer .btn-secondary-custom {
                width: 100%;
            }
        }
    </style>
@endsection