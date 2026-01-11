@extends('layouts.admin')

@section('title', 'Import Result')

@section('content')
<div class="import-result-container">
    <!-- Header -->
    <div class="result-header">
        <div class="breadcrumb-nav">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <i class="bi bi-chevron-right breadcrumb-separator"></i>
            <a href="{{ route('diamond.index') }}" class="breadcrumb-link">Diamonds</a>
            <i class="bi bi-chevron-right breadcrumb-separator"></i>
            <span class="breadcrumb-current">Import Result</span>
        </div>
        <h1 class="result-title">
            <i class="bi bi-file-earmark-check"></i>
            Import Complete
        </h1>
    </div>

    <!-- Result Cards -->
    <div class="result-cards">
        <!-- Success Card -->
        <div class="result-card success-card">
            <div class="card-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="card-content">
                <div class="card-label">Successfully Imported</div>
                <div class="card-value">{{ session('import_success', 0) }}</div>
                <div class="card-subtitle">Diamonds added to inventory</div>
            </div>
        </div>

        <!-- Failed Card -->
        @if(session('import_failed', 0) > 0)
        <div class="result-card error-card">
            <div class="card-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="card-content">
                <div class="card-label">Failed Rows</div>
                <div class="card-value">{{ session('import_failed', 0) }}</div>
                <div class="card-subtitle">Rows with validation errors</div>
            </div>
        </div>
        @endif

        <!-- Total Card -->
        <div class="result-card total-card">
            <div class="card-icon">
                <i class="bi bi-file-earmark-spreadsheet"></i>
            </div>
            <div class="card-content">
                <div class="card-label">Total Processed</div>
                <div class="card-value">{{ session('import_total', 0) }}</div>
                <div class="card-subtitle">Rows in uploaded file</div>
            </div>
        </div>
    </div>

    @if(session('import_failed', 0) > 0)
    <!-- Error Report Section -->
    <div class="error-report-section">
        <div class="error-report-card">
            <div class="error-header">
                <div class="error-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h3>Action Required: Fix & Re-import Failed Rows</h3>
                    <p>Download the error report to see what went wrong and fix the issues</p>
                </div>
            </div>

            <div class="error-body">
                <div class="step-guide">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-text">
                            <strong>Download Error Report</strong>
                            <span>Excel file with failed rows and error descriptions</span>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-text">
                            <strong>Fix Errors in Excel</strong>
                            <span>Check "Error Description" column and fix the issues</span>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-text">
                            <strong>Delete Error Columns</strong>
                            <span>Remove "Original Row" and "Error Description" columns</span>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-text">
                            <strong>Re-import Fixed File</strong>
                            <span>Upload the corrected file through the import button</span>
                        </div>
                    </div>
                </div>

                <div class="download-section">
                    <a href="{{ route('diamond.download-errors', session('error_report_file')) }}" 
                       class="btn-download-error">
                        <i class="bi bi-download"></i>
                        <span>Download Error Report</span>
                        <small>({{ session('import_failed', 0) }} rows)</small>
                    </a>
                </div>

                <div class="info-box">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <strong>Note:</strong> Successfully imported diamonds are already in your inventory. 
                        You only need to re-import the failed rows after fixing them.
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="result-actions">
        <a href="{{ route('diamond.index') }}" class="btn-primary">
            <i class="bi bi-arrow-left"></i>
            Back to Diamonds
        </a>
        
        @if(session('import_failed', 0) == 0)
        <a href="{{ route('diamond.create') }}" class="btn-secondary">
            <i class="bi bi-plus-circle"></i>
            Add Another Diamond
        </a>
        @endif
    </div>
</div>

<style>
    .import-result-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .result-header {
        margin-bottom: 2rem;
    }

    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
    }

    .breadcrumb-link {
        color: #6366f1;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .breadcrumb-link:hover {
        text-decoration: underline;
    }

    .breadcrumb-separator {
        color: #9ca3af;
    }

    .breadcrumb-current {
        color: #6b7280;
    }

    .result-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
    }

    .result-title i {
        color: #6366f1;
    }

    .result-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .result-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: flex;
        gap: 1.5rem;
        align-items: center;
        transition: transform 0.2s;
    }

    .result-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .card-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
    }

    .success-card .card-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .error-card .card-icon {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .total-card .card-icon {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .card-content {
        flex: 1;
    }

    .card-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .card-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .card-subtitle {
        font-size: 0.875rem;
        color: #9ca3af;
    }

    .error-report-section {
        margin-bottom: 2rem;
    }

    .error-report-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .error-header {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        padding: 2rem;
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        border-left: 4px solid #f59e0b;
    }

    .error-icon {
        width: 48px;
        height: 48px;
        background: #f59e0b;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .error-header h3 {
        margin: 0 0 0.5rem 0;
        color: #92400e;
        font-size: 1.25rem;
    }

    .error-header p {
        margin: 0;
        color: #78350f;
    }

    .error-body {
        padding: 2rem;
    }

    .step-guide {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .step {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .step-number {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .step-text {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .step-text strong {
        color: #1e293b;
        font-size: 0.9375rem;
    }

    .step-text span {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .download-section {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .btn-download-error {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1.5rem 3rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.125rem;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-download-error:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
    }

    .btn-download-error i {
        font-size: 1.5rem;
    }

    .btn-download-error small {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .info-box {
        display: flex;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: #f0f9ff;
        border-radius: 8px;
        border-left: 4px solid #3b82f6;
    }

    .info-box i {
        color: #3b82f6;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .info-box strong {
        color: #1e40af;
    }

    .info-box div {
        color: #1e3a8a;
        font-size: 0.9375rem;
        line-height: 1.6;
    }

    .result-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        padding-top: 2rem;
    }

    .btn-primary,
    .btn-secondary {
        padding: 0.875rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #6366f1;
        border: 2px solid #6366f1;
    }

    .btn-secondary:hover {
        background: #f8f9ff;
    }

    @media (max-width: 768px) {
        .import-result-container {
            padding: 1rem;
        }

        .result-cards {
            grid-template-columns: 1fr;
        }

        .step-guide {
            grid-template-columns: 1fr;
        }

        .result-actions {
            flex-direction: column;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection
