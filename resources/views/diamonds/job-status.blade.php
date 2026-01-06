@extends('layouts.admin')

@section('title', 'Job Status - ' . ucfirst($jobTrack->type))

@section('content')
    <div class="job-status-container">
        <!-- Header -->
        <div class="job-header">
            <div class="breadcrumb-nav">
                <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
                <i class="bi bi-chevron-right"></i>
                <a href="{{ route('diamond.index') }}" class="breadcrumb-link">Diamonds</a>
                <i class="bi bi-chevron-right"></i>
                <span class="breadcrumb-current">Job #{{ $jobTrack->id }}</span>
            </div>
            <h1 class="job-title">
                @if($jobTrack->type === 'import')
                    <i class="bi bi-cloud-upload"></i> Import Job #{{ $jobTrack->id }}
                @else
                    <i class="bi bi-cloud-download"></i> Export Job #{{ $jobTrack->id }}
                @endif
            </h1>
        </div>

        <!-- Status Badge -->
        <div class="status-badge-container">
            <span class="status-badge status-{{ $jobTrack->status }}" id="status-badge">
                @if($jobTrack->status === 'queued')
                    <i class="bi bi-hourglass-split"></i> Queued
                @elseif($jobTrack->status === 'processing')
                    <i class="bi bi-arrow-repeat spinning"></i> Processing
                @elseif($jobTrack->status === 'completed')
                    <i class="bi bi-check-circle"></i> Completed
                @elseif($jobTrack->status === 'failed')
                    <i class="bi bi-x-circle"></i> Failed
                @else
                    <i class="bi bi-dash-circle"></i> {{ ucfirst($jobTrack->status) }}
                @endif
            </span>
        </div>

        <!-- Job Info Card -->
        <div class="job-info-card">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">File Name</div>
                    <div class="info-value" style="word-break: break-all;">
                        {{ $jobTrack->file_name ?? 'Export - ' . now()->format('Y-m-d') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Started By</div>
                    <div class="info-value">{{ $jobTrack->admin->name ?? 'Admin' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Created At</div>
                    <div class="info-value">{{ $jobTrack->created_at->format('M d, Y') }}<br><small>{{ $jobTrack->created_at->format('h:i:s A') }}</small></div>
                </div>
                @if($jobTrack->started_at)
                    <div class="info-item">
                        <div class="info-label">Started At</div>
                        <div class="info-value">{{ $jobTrack->started_at->format('M d, Y') }}<br><small>{{ $jobTrack->started_at->format('h:i:s A') }}</small></div>
                    </div>
                @endif
                @if($jobTrack->completed_at)
                    <div class="info-item">
                        <div class="info-label">Completed At</div>
                        <div class="info-value">{{ $jobTrack->completed_at->format('M d, Y') }}<br><small>{{ $jobTrack->completed_at->format('h:i:s A') }}</small></div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Progress Section -->
        @if($jobTrack->status === 'processing' || $jobTrack->status === 'completed')
            <div class="progress-card">
                <h3>Progress</h3>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progress-bar" data-width="{{ $jobTrack->progress_percentage }}"></div>
                    <span class="progress-text" id="progress-text">{{ $jobTrack->progress_percentage }}%</span>
                </div>

                <div class="progress-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="processed-rows">{{ number_format($jobTrack->processed_rows) }}</div>
                        <div class="stat-label">Processed</div>
                    </div>
                    <div class="stat-item success">
                        <div class="stat-value" id="successful-rows">{{ number_format($jobTrack->successful_rows) }}</div>
                        <div class="stat-label">Success</div>
                    </div>
                    @if($jobTrack->type === 'import')
                        <div class="stat-item error">
                            <div class="stat-value" id="failed-rows">{{ number_format($jobTrack->failed_rows) }}</div>
                            <div class="stat-label">Failed</div>
                        </div>
                    @endif
                    <div class="stat-item">
                        <div class="stat-value" id="total-rows">{{ number_format($jobTrack->total_rows) }}</div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>

                @if($jobTrack->status === 'processing')
                    <div class="live-stats">
                        <div class="live-stat">
                            <i class="bi bi-speedometer2"></i>
                            <span id="processing-speed">{{ $jobTrack->getProcessingSpeed() }}</span> rows/sec
                        </div>
                        <div class="live-stat">
                            <i class="bi bi-clock"></i>
                            ETA: <span id="time-remaining">{{ $jobTrack->getFormattedTimeRemaining() }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Results Section -->
        @if($jobTrack->status === 'completed')
            <div class="results-card success">
                <div class="results-header">
                    <i class="bi bi-check-circle-fill"></i>
                    <h3>{{ ucfirst($jobTrack->type) }} Completed Successfully!</h3>
                </div>
                <div class="results-body">
                    @if($jobTrack->type === 'import')
                        <p><strong>{{ number_format($jobTrack->successful_rows) }}</strong> diamonds imported successfully.</p>

                        @if($jobTrack->failed_rows > 0)
                            <div class="error-alert">
                                <i class="bi bi-exclamation-triangle"></i>
                                <div>
                                    <strong>{{ number_format($jobTrack->failed_rows) }} rows failed</strong>
                                    <p>Download the error report to fix and re-import failed rows</p>
                                </div>
                            </div>
                            <a href="{{ route('diamond.download-errors', basename($jobTrack->error_file_path)) }}"
                                class="btn-download-errors">
                                <i class="bi bi-download"></i> Download Error Report
                            </a>
                        @endif
                    @else
                        <p><strong>{{ number_format($jobTrack->total_rows) }}</strong> diamonds exported successfully.</p>
                        <a href="{{ route('diamond.job.download', $jobTrack->id) }}" class="btn-download-export">
                            <i class="bi bi-download"></i> Download Export File
                        </a>
                        <p class="expiry-notice">File expires in 24 hours</p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Error Section -->
        @if($jobTrack->status === 'failed')
            <div class="results-card error">
                <div class="results-header">
                    <i class="bi bi-x-circle-fill"></i>
                    <h3>Job Failed</h3>
                </div>
                <div class="results-body">
                    <p>{{ $jobTrack->error_message ?? 'Unknown error occurred' }}</p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('diamond.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Diamonds
            </a>
            <a href="{{ route('diamond.job.history') }}" class="btn-secondary">
                <i class="bi bi-clock-history"></i> Job History
            </a>
        </div>
    </div>

    @if($jobTrack->isRunning())
        <script>
            // Initialize progress bar width on page load
            document.getElementById('progress-bar').style.width = document.getElementById('progress-bar').getAttribute('data-width') + '%';

            // Auto-refresh job status every 2 seconds
            setInterval(function () {
                fetch('{{ route(`diamond.job.status.json `, $jobTrack->id) }}')
                    .then(response => response.json())
                    .then(data => {
                        // Update progress bar
                        document.getElementById('progress-bar').style.width = data.progress_percentage + '%';
                        document.getElementById('progress-text').textContent = data.progress_percentage + '%';

                        // Update stats
                        document.getElementById('processed-rows').textContent = Number(data.processed_rows).toLocaleString();
                        document.getElementById('successful-rows').textContent = Number(data.successful_rows).toLocaleString();
                        const failedRowsElement = document.getElementById('failed-rows');
                        if (failedRowsElement) {
                            failedRowsElement.textContent = Number(data.failed_rows).toLocaleString();
                        }
                        document.getElementById('total-rows').textContent = Number(data.total_rows).toLocaleString();

                        // Update live stats
                        if (data.processing_speed) {
                            document.getElementById('processing-speed').textContent = data.processing_speed;
                        }
                        if (data.estimated_time_remaining) {
                            document.getElementById('time-remaining').textContent = data.estimated_time_remaining;
                        }

                        // Reload page if job is complete
                        if (data.is_complete) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }, 2000);
        </script>
    @endif

    <style>
        .job-status-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .job-header {
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

        .breadcrumb-current {
            color: #6b7280;
        }

        .job-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .status-badge-container {
            margin-bottom: 2rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.125rem;
        }

        .status-queued {
            background: #fef3c7;
            color: #92400e;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .spinning {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .job-info-card,
        .progress-card,
        .results-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
        }

        .info-value small {
            font-size: 0.85rem;
            font-weight: 500;
            color: #6b7280;
        }

        .progress-card h3 {
            margin: 0 0 1.5rem 0;
            color: #1e293b;
        }

        .progress-bar-container {
            position: relative;
            width: 100%;
            height: 40px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            transition: width 0.5s ease;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 700;
            color: #1e293b;
            font-size: 1.125rem;
        }

        .progress-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            background: #f8fafc;
        }

        .stat-item.success {
            background: #d1fae5;
        }

        .stat-item.error {
            background: #fee2e2;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .live-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            padding-top: 1rem;
            border-top: 2px dashed #e2e8f0;
        }

        .live-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            color: #475569;
        }

        .live-stat i {
            color: #6366f1;
            font-size: 1.25rem;
        }

        .results-card {
            border-left: 4px solid;
        }

        .results-card.success {
            border-left-color: #10b981;
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        }

        .results-card.error {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
        }

        .results-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .results-header i {
            font-size: 2rem;
        }

        .results-card.success .results-header i {
            color: #10b981;
        }

        .results-card.error .results-header i {
            color: #ef4444;
        }

        .results-header h3 {
            margin: 0;
            color: #1e293b;
        }

        .error-alert {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background: #fef3c7;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin: 1rem 0;
        }

        .error-alert i {
            color: #f59e0b;
            font-size: 1.5rem;
        }

        .btn-download-errors,
        .btn-download-export {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-download-errors {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-download-export {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
        }

        .btn-download-errors:hover,
        .btn-download-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .expiry-notice {
            margin-top: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-secondary {
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            background: white;
            color: #6366f1;
            border: 2px solid #6366f1;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #f8f9ff;
        }
    </style>
@endsection