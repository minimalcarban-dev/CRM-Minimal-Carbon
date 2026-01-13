@extends('layouts.admin')
@section('title', 'Draft Preview')
@section('content')

    <div class="draft-preview-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="{{ route('orders.drafts.index') }}" class="breadcrumb-link">
                            <i class="bi bi-arrow-left"></i> Back to Drafts
                        </a>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-file-earmark-text"></i>
                        Draft #D-{{ $draft->id }}
                    </h1>
                    <p class="page-subtitle">
                        Created {{ $draft->created_at->diffForHumans() }} by {{ $draft->admin->name ?? 'Unknown' }}
                    </p>
                </div>
                <div class="header-right">
                    <a href="{{ route('orders.drafts.resume', $draft->id) }}" class="btn-primary-custom">
                        <i class="bi bi-play-circle"></i>
                        Resume Editing
                    </a>
                    <form action="{{ route('orders.drafts.destroy', $draft->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to discard this draft?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger-custom">
                            <i class="bi bi-trash"></i>
                            Discard
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Draft Info -->
        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">Order Type</div>
                <div class="info-value">
                    <span class="order-type-badge badge-{{ $draft->order_type }}">
                        {{ $draft->order_type_label }}
                    </span>
                </div>
            </div>
            <div class="info-card">
                <div class="info-label">Source</div>
                <div class="info-value">
                    <span class="source-badge {{ $draft->source }}">
                        {{ $draft->source_label }}
                    </span>
                </div>
            </div>
            <div class="info-card">
                <div class="info-label">Completion</div>
                <div class="info-value">{{ $draft->completion_percentage }}%</div>
            </div>
            <div class="info-card">
                <div class="info-label">Expires</div>
                <div class="info-value">{{ $draft->expires_at->format('M d, Y') }}</div>
            </div>
        </div>

        @if($draft->error_message)
            <div class="error-alert">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <strong>Error that caused this draft:</strong>
                    <p>{{ $draft->error_message }}</p>
                </div>
            </div>
        @endif

        <!-- Form Data Preview -->
        <div class="data-card">
            <div class="data-header">
                <h3><i class="bi bi-list-ul"></i> Form Data</h3>
            </div>
            <div class="data-body">
                <table class="data-table">
                    <tbody>
                        @foreach($draft->form_data ?? [] as $key => $value)
                            <tr>
                                <td class="data-key">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                <td class="data-value">
                                    @if(is_array($value))
                                        <pre>{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                    @elseif(empty($value))
                                        <span class="text-muted">Not set</span>
                                    @else
                                        {{ Str::limit($value, 200) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .draft-preview-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .breadcrumb-link {
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .breadcrumb-link:hover {
            color: #6366f1;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: #6366f1;
        }

        .page-subtitle {
            color: #64748b;
            margin: 0;
        }

        .header-right {
            display: flex;
            gap: 1rem;
        }

        .btn-primary-custom,
        .btn-danger-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }

        .btn-primary-custom {
            background: #6366f1;
            color: white;
        }

        .btn-danger-custom {
            background: white;
            color: #ef4444;
            border: 2px solid #fecaca;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            border: 2px solid #e2e8f0;
        }

        .info-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
        }

        .order-type-badge,
        .source-badge {
            display: inline-flex;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .badge-ready_to_ship {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .badge-custom_diamond {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .badge-custom_jewellery {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .source-badge.error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .source-badge.auto_save {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .source-badge.manual {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .error-alert {
            background: rgba(239, 68, 68, 0.05);
            border: 2px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            color: #ef4444;
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .error-alert i {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .error-alert p {
            margin: 0.5rem 0 0;
        }

        .data-card {
            background: white;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            overflow: hidden;
        }

        .data-header {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-header h3 {
            margin: 0;
            font-size: 1.125rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .data-body {
            padding: 1.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table tr:last-child {
            border-bottom: none;
        }

        .data-key {
            padding: 0.75rem 1rem 0.75rem 0;
            font-weight: 600;
            color: #64748b;
            width: 200px;
            vertical-align: top;
        }

        .data-value {
            padding: 0.75rem 0;
            color: #1e293b;
        }

        .data-value pre {
            background: #f8fafc;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            margin: 0;
            overflow-x: auto;
        }

        .text-muted {
            color: #94a3b8;
            font-style: italic;
        }
    </style>
@endsection