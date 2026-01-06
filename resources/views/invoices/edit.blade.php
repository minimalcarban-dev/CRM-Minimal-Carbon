@extends('layouts.admin')

@section('title', 'Edit Invoice')

@section('content')
    <div class="edit-invoice-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                <div class="header-text">
                    <h1 class="page-title">Edit Invoice #{{ $invoice->invoice_no }}</h1>
                    <p class="page-subtitle">Update the invoice details and save your changes</p>
                </div>
            </div>
            <a href="{{ route('invoices.index') }}" class="btn-back-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span>Back to Invoices</span>
            </a>
        </div>

        <!-- Error Alert -->
        @if ($errors->any())
            <div class="alert alert-error">
                <div class="alert-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
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

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #3b82f6;
            --success: #059669;
            --danger: #dc2626;
            --danger-light: #fef2f2;
            --danger-border: #fecaca;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --white: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 10px;
            --radius-lg: 16px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        .edit-invoice-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .header-content {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .header-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            border-radius: 12px;
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .header-text {
            flex: 1;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.375rem;
            letter-spacing: -0.025em;
        }

        .page-subtitle {
            font-size: 0.95rem;
            color: var(--gray-600);
            margin: 0;
        }

        .btn-back-header {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--white);
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: var(--radius);
            border: 1.5px solid var(--gray-300);
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-back-header:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            box-shadow: var(--shadow);
            transform: translateY(-1px);
        }

        /* Alert */
        .alert {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid;
        }

        .alert-error {
            background: var(--danger-light);
            border-color: var(--danger-border);
            color: var(--gray-900);
            box-shadow: var(--shadow-sm);
        }

        .alert {
            display: flex;
            gap: 1rem;
        }

        .alert-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border-radius: 8px;
            color: var(--danger);
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--danger);
            margin-bottom: 0.75rem;
        }

        .alert-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .alert-list li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray-700);
            line-height: 1.5;
        }

        .alert-list li:last-child {
            margin-bottom: 0;
        }

        .alert-list li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.6rem;
            width: 6px;
            height: 6px;
            background: var(--danger);
            border-radius: 50%;
        }

        /* Form Container */
        .form-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .invoice-form {
            padding: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .edit-invoice-wrapper {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1.5rem;
            }

            .header-content {
                width: 100%;
            }

            .btn-back-header {
                width: 100%;
                justify-content: center;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .alert {
                flex-direction: column;
            }

            .alert-icon {
                align-self: flex-start;
            }
        }

        @media (max-width: 480px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .header-icon {
                width: 48px;
                height: 48px;
            }

            .header-icon svg {
                width: 24px;
                height: 24px;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .page-subtitle {
                font-size: 0.875rem;
            }
        }
    </style>
@endsection