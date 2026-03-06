@extends('layouts.admin')

@section('title', 'Melee Diamond Inventory')

@section('content')

    <style>
        /* Custom Styles meant to match orders/index.blade.php */

        [data-theme="dark"] .inventory-management-container {
            background: var(--bg-body, #0f172a);
        }

        [data-theme="dark"] .page-header,
        [data-theme="dark"] .inventory-card,
        [data-theme="dark"] .sidebar-panel,
        [data-theme="dark"] .main-panel,
        [data-theme="dark"] .shape-group-header,
        [data-theme="dark"] .shape-group-body,
        [data-theme="dark"] .table-header,
        [data-theme="dark"] .add-size-row,
        [data-theme="dark"] .add-shape-bar {
            background: var(--bg-card, #1e293b);
            border-color: rgba(148, 163, 184, 0.28) !important;
        }

        [data-theme="dark"] .shape-group-header:hover,
        [data-theme="dark"] .table-custom tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        [data-theme="dark"] .category-nav-item {
            color: var(--text-secondary, #94a3b8);
        }

        [data-theme="dark"] .category-nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] .category-nav-item:not(.active) .badge {
            background: rgba(255, 255, 255, 0.07);
            color: var(--text-secondary, #94a3b8);
        }

        [data-theme="dark"] .shape-group-header .shape-name,
        [data-theme="dark"] .table-custom tbody td,
        [data-theme="dark"] .inventory-management-container .text-dark {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] .shape-chevron,
        [data-theme="dark"] .inventory-management-container .text-secondary,
        [data-theme="dark"] .inventory-management-container .text-muted {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] .table-custom thead th {
            background: rgba(15, 23, 42, 0.5);
            border-bottom-color: rgba(148, 163, 184, 0.34);
            color: var(--text-secondary, #94a3b8);
        }

        [data-theme="dark"] .table-custom tbody td {
            border-bottom-color: rgba(148, 163, 184, 0.22);
        }

        [data-theme="dark"] .size-count-pill {
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
        }

        [data-theme="dark"] .stock-total-pill {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        /* Stock table partial */
        [data-theme="dark"] .inventory-card .table thead.bg-light th {
            background: rgba(15, 23, 42, 0.5) !important;
            color: var(--text-secondary, #94a3b8);
            border-bottom-color: rgba(148, 163, 184, 0.34);
        }

        [data-theme="dark"] .inventory-card .table tbody td {
            color: var(--text-primary, #f1f5f9);
            border-bottom-color: rgba(148, 163, 184, 0.22);
        }

        [data-theme="dark"] .inventory-card .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-primary, #f1f5f9);
            --bs-table-border-color: rgba(148, 163, 184, 0.22);
            --bs-table-hover-bg: rgba(255, 255, 255, 0.03);
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] .inventory-card .table> :not(caption)>*>* {
            background-color: transparent !important;
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] .inventory-card .table .bg-success-subtle {
            background: rgba(16, 185, 129, 0.18) !important;
            color: #34d399 !important;
        }

        [data-theme="dark"] .inventory-card .table .bg-danger-subtle {
            background: rgba(239, 68, 68, 0.18) !important;
            color: #f87171 !important;
        }

        /* Transaction modal partial */
        [data-theme="dark"] #transactionModal .modal-content {
            background: var(--bg-card, #1e293b);
            border: 1px solid rgba(148, 163, 184, 0.34) !important;
        }

        [data-theme="dark"] #transactionModal .modal-header.bg-light {
            background: rgba(15, 23, 42, 0.55) !important;
            border-color: rgba(148, 163, 184, 0.28) !important;
        }

        [data-theme="dark"] #transactionModal #selection_context {
            background: rgba(15, 23, 42, 0.5) !important;
            border-color: rgba(148, 163, 184, 0.3) !important;
        }

        [data-theme="dark"] #transactionModal .bg-light {
            background: rgba(255, 255, 255, 0.06) !important;
        }

        [data-theme="dark"] #transactionModal .bg-white {
            background: rgba(15, 23, 42, 0.4) !important;
        }

        [data-theme="dark"] #transactionModal .text-dark {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] #transactionModal .text-secondary,
        [data-theme="dark"] #transactionModal .text-muted {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] #transactionModal .form-control,
        [data-theme="dark"] #transactionModal textarea,
        [data-theme="dark"] #transactionModal select {
            background: rgba(15, 23, 42, 0.62);
            border-color: rgba(148, 163, 184, 0.32);
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] #transactionModal .form-control::placeholder,
        [data-theme="dark"] #transactionModal textarea::placeholder {
            color: var(--text-secondary, #94a3b8);
        }

        [data-theme="dark"] #transactionModal .form-text {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] #transactionModal .btn-close {
            filter: invert(1) brightness(1.2);
        }

        [data-theme="dark"] #transactionModal .btn-link {
            color: #a5b4fc !important;
        }

        [data-theme="dark"] #transactionModal .select2-container--bootstrap-5 .select2-selection--single,
        [data-theme="dark"] #transactionModal .select2-container--bootstrap-5 .select2-dropdown,
        [data-theme="dark"] #transactionModal .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            background: rgba(15, 23, 42, 0.62);
            border-color: rgba(148, 163, 184, 0.32);
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] #transactionModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
        [data-theme="dark"] #transactionModal .select2-container--bootstrap-5 .select2-results__option {
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] #transactionModal .select2-container--bootstrap-5 .select2-results__option--selected {
            background: rgba(99, 102, 241, 0.18);
            color: #a5b4fc;
        }

        /* History + Edit Modals Dark Theme */
        [data-theme="dark"] #historyModal .modal-content,
        [data-theme="dark"] #editMeleeModal .modal-content,
        [data-theme="dark"] #editTransactionModal .modal-content,
        [data-theme="dark"] #quickOrderModal .modal-content {
            background: var(--bg-card, #1e293b);
            border: 1px solid rgba(148, 163, 184, 0.34) !important;
        }

        [data-theme="dark"] #historyModal .modal-body,
        [data-theme="dark"] #editMeleeModal .modal-body,
        [data-theme="dark"] #editTransactionModal .modal-body,
        [data-theme="dark"] #quickOrderModal .modal-body {
            background: var(--bg-card, #1e293b);
        }

        [data-theme="dark"] #historyModal #history-diamond-info.bg-light,
        [data-theme="dark"] #editMeleeModal .modal-header.bg-light,
        [data-theme="dark"] #editTransactionModal .modal-header.bg-light,
        [data-theme="dark"] #quickOrderModal .modal-footer.bg-light,
        [data-theme="dark"] #quickOrderModal .bg-light {
            background: rgba(15, 23, 42, 0.52) !important;
            border-color: rgba(148, 163, 184, 0.3) !important;
        }

        [data-theme="dark"] #historyModal .table-custom,
        [data-theme="dark"] #historyModal .table-custom> :not(caption)>*>* {
            color: var(--text-primary, #f1f5f9);
            background: transparent !important;
            border-color: rgba(148, 163, 184, 0.24);
        }

        [data-theme="dark"] #historyModal .table-custom thead th {
            background: rgba(15, 23, 42, 0.52) !important;
            color: var(--text-secondary, #94a3b8);
            border-bottom-color: rgba(148, 163, 184, 0.34);
        }

        [data-theme="dark"] #historyModal .table-custom tbody tr:hover td {
            background: rgba(255, 255, 255, 0.03) !important;
        }

        [data-theme="dark"] #historyModal .text-muted,
        [data-theme="dark"] #editMeleeModal .text-muted,
        [data-theme="dark"] #editTransactionModal .text-muted,
        [data-theme="dark"] #quickOrderModal .text-muted,
        [data-theme="dark"] #editMeleeModal .text-secondary,
        [data-theme="dark"] #editTransactionModal .text-secondary,
        [data-theme="dark"] #quickOrderModal .text-secondary {
            color: var(--text-secondary, #94a3b8) !important;
        }

        [data-theme="dark"] #historyModal .text-dark,
        [data-theme="dark"] #editMeleeModal .text-dark,
        [data-theme="dark"] #editTransactionModal .text-dark,
        [data-theme="dark"] #quickOrderModal .text-dark,
        [data-theme="dark"] #editMeleeModal .modal-title,
        [data-theme="dark"] #editTransactionModal .modal-title,
        [data-theme="dark"] #quickOrderModal .modal-title,
        [data-theme="dark"] #historyModal #history-diamond-name,
        [data-theme="dark"] #quickOrderModal .order-quick-details {
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] #editMeleeModal hr,
        [data-theme="dark"] #editTransactionModal hr,
        [data-theme="dark"] #quickOrderModal hr {
            border-color: rgba(148, 163, 184, 0.22);
        }

        [data-theme="dark"] #editMeleeModal .form-control,
        [data-theme="dark"] #editTransactionModal .form-control,
        [data-theme="dark"] #quickOrderModal .form-control {
            background: rgba(15, 23, 42, 0.62);
            border-color: rgba(148, 163, 184, 0.32);
            color: var(--text-primary, #f1f5f9);
        }

        [data-theme="dark"] #editMeleeModal .form-control::placeholder,
        [data-theme="dark"] #editTransactionModal .form-control::placeholder,
        [data-theme="dark"] #quickOrderModal .form-control::placeholder {
            color: var(--text-secondary, #94a3b8);
        }

        [data-theme="dark"] #editMeleeModal .btn-close,
        [data-theme="dark"] #editTransactionModal .btn-close,
        [data-theme="dark"] #quickOrderModal .btn-close,
        [data-theme="dark"] #historyModal .btn-close {
            filter: invert(1) brightness(1.2);
        }

        /* Theme Buttons */
        .btn-theme-tab {
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-secondary, #64748b);
            border-radius: 10px;
            font-weight: 600;
            padding: 0.45rem 0.9rem;
            transition: all 0.2s ease;
        }

        .btn-theme-tab:hover {
            border-color: rgba(99, 102, 241, 0.45);
            color: var(--text-primary, #1e293b);
            background: rgba(99, 102, 241, 0.08);
        }

        .btn-theme-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-color: transparent;
            color: #fff;
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.28);
        }

        .btn-theme-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            color: #fff;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-theme-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
            color: #fff;
        }

        .btn-theme-danger-outline {
            border: 1px solid rgba(239, 68, 68, 0.55);
            background: rgba(239, 68, 68, 0.04);
            color: #ef4444;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-theme-danger-outline:hover {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
        }

        .btn-theme-outline {
            border: 1px solid rgba(99, 102, 241, 0.6);
            background: rgba(99, 102, 241, 0.04);
            color: var(--primary);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-theme-outline:hover {
            background: rgba(99, 102, 241, 0.12);
            color: var(--primary);
        }

        .btn-theme-icon {
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: rgba(255, 255, 255, 0.02);
            transition: all 0.2s ease;
        }

        .btn-theme-icon-in {
            color: #2563eb;
        }

        .btn-theme-icon-out {
            color: #f59e0b;
        }

        .btn-theme-icon-edit {
            color: #94a3b8;
        }

        .btn-theme-icon-delete {
            color: #ef4444;
        }

        .btn-theme-icon:hover {
            transform: translateY(-1px);
            border-color: rgba(99, 102, 241, 0.45);
            background: rgba(99, 102, 241, 0.12);
        }

        .inventory-management-container {
            --secondary: var(--text-secondary, #64748b);
            padding: 2rem;
            max-width: 1800px;
            margin: 0 auto;
            background: #f8fafc;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inventory-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            min-height: 600px;
        }

        .sidebar-panel {
            width: 300px;
            border-right: 1px solid var(--border);
            padding: 1.5rem;
            background: #fff;
            flex-shrink: 0;
            max-height: 80vh;
            overflow-y: auto;
        }

        .main-panel {
            flex-grow: 1;
            padding: 0;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar Category Items */
        .category-nav-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.85rem 1.25rem;
            border-radius: 12px;
            color: var(--secondary);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .category-nav-item:hover {
            background: #f1f5f9;
            color: var(--dark);
        }

        .category-nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .category-nav-item .badge {
            font-weight: 600;
            font-size: 0.7rem;
            padding: 0.3em 0.6em;
        }

        .category-nav-item.active .badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .category-nav-item:not(.active) .badge {
            background: #f1f5f9;
            color: var(--secondary);
        }

        .category-item-container .category-delete-btn {
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 10;
            background: transparent;
            border: none;
            font-size: 1rem;
        }

        .category-item-container:hover .category-delete-btn {
            opacity: 0.6;
            visibility: visible;
        }

        .category-item-container .category-delete-btn:hover {
            opacity: 1 !important;
        }

        /* Shape Accordion Group inside main panel */
        .shape-group {
            border-bottom: 1px solid var(--border);
        }

        .shape-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            cursor: pointer;
            transition: background 0.2s;
            background: #fff;
            border: none;
            width: 100%;
            text-align: left;
        }

        .shape-group-header:hover {
            background: #f8fafc;
        }

        .shape-group-header .shape-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .shape-group-header .shape-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .shape-chevron {
            transition: transform 0.25s ease;
            color: var(--secondary);
        }

        .shape-group.open .shape-chevron {
            transform: rotate(180deg);
        }

        .shape-group-body {
            display: none;
            padding: 0;
            background: #fafbfc;
        }

        .shape-group.open .shape-group-body {
            display: block;
        }

        /* Table */
        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-custom thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--secondary);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: #f8fafc;
        }

        .table-custom tbody td {
            padding: 0.75rem 1.5rem;
            vertical-align: middle;
            color: var(--dark);
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }

        .hidden {
            display: none !important;
        }

        /* Add size row */
        .add-size-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            background: #f1f5f9;
            border-top: 1px dashed var(--border);
        }

        .add-size-row input {
            max-width: 130px;
        }

        /* Add new shape bar */
        .add-shape-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-top: 2px dashed var(--border);
        }

        .add-shape-bar input,
        .add-shape-bar select {
            max-width: 170px;
        }

        /* ── Select2 Dropdown Styling for Transaction Modal ── */
        #transactionModal .select2-container--bootstrap-5 .select2-selection--single {
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 0.55rem 0.85rem;
            height: auto;
            min-height: 44px;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection--single:focus,
        #transactionModal .select2-container--bootstrap-5.select2-container--focus .select2-selection--single,
        #transactionModal .select2-container--bootstrap-5.select2-container--open .select2-selection--single {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--dark);
            font-weight: 500;
            line-height: 1.5;
            padding: 0;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 10px;
        }

        /* Dropdown panel */
        #transactionModal .select2-container--bootstrap-5 .select2-dropdown {
            border: 2px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 4px;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-search--dropdown {
            padding: 0.75rem;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
            outline: none;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-results__option {
            padding: 0.6rem 0.85rem;
            font-size: 0.9rem;
            color: var(--dark);
            border-radius: 6px;
            margin: 2px 6px;
            transition: background-color 0.15s, color 0.15s;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)) !important;
            color: #fff !important;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-results__option--selected {
            background: rgba(99, 102, 241, 0.08);
            color: var(--primary);
            font-weight: 600;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-results {
            max-height: 220px;
            padding: 4px 0;
        }

        /* Clear button */
        #transactionModal .select2-container--bootstrap-5 .select2-selection__clear {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-right: 4px;
        }

        #transactionModal .select2-container--bootstrap-5 .select2-selection__clear:hover {
            color: var(--danger);
        }

        /* Size count pill */
        .size-count-pill {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2em 0.6em;
            border-radius: 20px;
        }

        .stock-total-pill {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2em 0.6em;
            border-radius: 20px;
        }

        /* Toast notification */
        .melee-toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9999;
            min-width: 300px;
        }

        /* ==========================================
                   RESPONSIVE STYLES - ALL DEVICES
                   ========================================== */

        /* Tablet and below (1024px) */
        @media (max-width: 1024px) {
            .inventory-management-container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .sidebar-panel {
                width: 260px;
            }

            .table-custom thead th,
            .table-custom tbody td {
                padding: 0.65rem 1rem;
                font-size: 0.85rem;
            }
        }

        /* Tablet Portrait (768px) */
        @media (max-width: 768px) {
            .inventory-management-container {
                padding: 0.75rem;
            }

            /* Page header: keep tab pills inline, stack action buttons below */
            .page-header {
                padding: 1rem 1.25rem;
                flex-direction: column;
                gap: 0.85rem;
            }

            /* Title row: keep compact */
            .page-header>div:first-child h2 {
                font-size: 1.3rem;
            }

            .page-header>div:first-child .text-secondary.small {
                font-size: 0.78rem;
            }

            /* Button group: tabs side-by-side, action buttons fill remaining */
            .page-header>div.d-flex {
                width: 100%;
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto auto;
                gap: 0.5rem;
            }

            /* Tab pills go in top row (2 cols) */
            #btn-tab-lab {
                grid-column: 1;
                grid-row: 1;
                font-size: 0.85rem;
                padding: 0.5rem 0.75rem;
                min-height: 40px;
                justify-content: center;
                display: flex;
                align-items: center;
            }

            #btn-tab-natural {
                grid-column: 2;
                grid-row: 1;
                font-size: 0.85rem;
                padding: 0.5rem 0.75rem;
                min-height: 40px;
                justify-content: center;
                display: flex;
                align-items: center;
            }

            /* Hide the vertical rule */
            .page-header .vr {
                display: none;
            }

            /* Add Stock & Use Stock go in bottom row (2 cols) */
            .page-header .btn-theme-primary {
                grid-column: 1;
                grid-row: 2;
                min-height: 42px;
                justify-content: center;
                display: flex;
                align-items: center;
                font-size: 0.88rem;
            }

            .page-header .btn-theme-danger-outline {
                grid-column: 2;
                grid-row: 2;
                min-height: 42px;
                justify-content: center;
                display: flex;
                align-items: center;
                font-size: 0.88rem;
            }

            /* Inventory card: sidebar on top */
            .inventory-card {
                flex-direction: column;
                min-height: auto;
            }

            /* Sidebar: becomes horizontal scrollable chip row */
            .sidebar-panel {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border);
                max-height: none;
                overflow: hidden;
                padding: 0.75rem 1rem;
            }

            .sidebar-panel>h6 {
                font-size: 0.68rem;
                margin-bottom: 0.6rem;
            }

            /* Turn category list into horizontal scroll */
            #sidebar-lab-grown,
            #sidebar-natural {
                display: flex;
                flex-direction: row;
                gap: 0.4rem;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 0.5rem;
                scrollbar-width: none;
            }

            #sidebar-lab-grown::-webkit-scrollbar,
            #sidebar-natural::-webkit-scrollbar {
                display: none;
            }

            /* Each category item becomes a chip */
            .category-item-container {
                flex-shrink: 0;
                margin-bottom: 0;
            }

            .category-nav-item {
                padding: 0.45rem 0.9rem;
                font-size: 0.82rem;
                min-height: 36px;
                border-radius: 20px;
                white-space: nowrap;
                width: auto;
                margin-bottom: 0;
            }

            .category-nav-item span:first-child i {
                display: none;
                /* hide gem icon on chips */
            }

            /* Delete button overlays chip */
            .category-item-container .category-delete-btn {
                opacity: 0.75;
                visibility: visible;
                font-size: 0.65rem;
                padding: 0.15rem 0.3rem;
            }

            /* Add Category button as chip */
            #sidebar-lab-grown>.mt-3,
            #sidebar-natural>.mt-3 {
                flex-shrink: 0;
                margin-top: 0 !important;
                align-self: center;
            }

            #sidebar-lab-grown .btn-theme-outline,
            #sidebar-natural .btn-theme-outline {
                white-space: nowrap;
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
                border-radius: 20px;
            }

            /* Main panel */
            .main-panel {
                min-height: 0;
            }

            /* Table-header toolbar */
            .table-header {
                padding: 0.85rem 1rem;
                flex-direction: column;
                gap: 0.6rem;
                align-items: stretch !important;
            }

            .table-header>div:last-child {
                width: 100%;
                display: flex;
                gap: 0.5rem;
            }

            .table-header input[type="text"] {
                flex: 1;
                width: auto !important;
                min-height: 38px;
            }

            .table-header .btn-theme-outline {
                min-height: 38px;
                white-space: nowrap;
            }

            /* Shape accordion header */
            .shape-group-header {
                padding: 0.9rem 1rem;
                min-height: 48px;
            }

            .shape-group-header .shape-name {
                font-size: 0.9rem;
            }

            /* ── CARD LAYOUT for shape-group table on mobile ── */
            /* Switch table to card list */
            .shape-group-body {
                overflow-x: visible;
                padding: 0.6rem 0.75rem;
                background: transparent;
            }

            .table-custom,
            .table-custom thead,
            .table-custom tbody,
            .table-custom tr,
            .table-custom th,
            .table-custom td {
                display: block !important;
            }

            .table-custom {
                min-width: 0 !important;
                font-size: 0.88rem;
            }

            /* Hide desktop thead */
            .table-custom thead {
                display: none !important;
            }

            /* Each row = one card */
            .table-custom tbody tr.searchable-row {
                display: grid !important;
                grid-template-columns: auto 1fr auto;
                grid-template-rows: auto auto;
                column-gap: 0.6rem;
                row-gap: 0.35rem;
                padding: 0.85rem 0.9rem;
                margin-bottom: 0.55rem;
                border-radius: 12px;
                border: 1px solid var(--border, #e5e7eb);
                background: #fff;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
                overflow: hidden;
                position: relative;
            }

            [data-theme="dark"] .table-custom tbody tr.searchable-row {
                background: var(--bg-card, #1e293b);
                border-color: rgba(148, 163, 184, 0.2);
                box-shadow: 0 1px 6px rgba(0, 0, 0, 0.18);
            }

            /* Column 1: Size (big, bold) — grid col 1, rows 1-2 */
            .table-custom tbody td:nth-child(1) {
                grid-column: 1;
                grid-row: 1;
                font-size: 1.35rem;
                font-weight: 800;
                color: var(--primary, #6366f1);
                line-height: 1;
                align-self: center;
                padding: 0;
                border: none;
            }

            /* Column 2: Size label — grid col 2, row 1 */
            .table-custom tbody td:nth-child(2) {
                grid-column: 2;
                grid-row: 1;
                font-size: 0.78rem;
                color: var(--text-secondary, #64748b);
                padding: 0;
                border: none;
                align-self: end;
            }

            [data-theme="dark"] .table-custom tbody td:nth-child(2) {
                color: var(--text-secondary, #94a3b8) !important;
            }

            /* Column 3: Stock badge — grid col 2, row 2 */
            .table-custom tbody td:nth-child(3) {
                grid-column: 2;
                grid-row: 2;
                padding: 0;
                border: none;
                align-self: start;
            }

            .table-custom tbody td:nth-child(3) .badge {
                font-size: 0.75rem;
                padding: 0.3em 0.75em;
            }

            /* Column 4: Avg $/ct — grid col 2-3, row 1 — right side */
            .table-custom tbody td:nth-child(4) {
                grid-column: 3;
                grid-row: 1;
                font-weight: 600;
                font-size: 0.88rem;
                color: var(--text-primary, #0f172a);
                text-align: right;
                padding: 0;
                border: none;
                align-self: end;
                white-space: nowrap;
            }

            [data-theme="dark"] .table-custom tbody td:nth-child(4) {
                color: var(--text-primary, #f1f5f9) !important;
            }

            /* Column 5: Total Carats — hidden on mobile */
            .table-custom tbody td:nth-child(5) {
                display: none !important;
            }

            /* Column 6: Total Price — hidden on mobile (can show $/ct instead) */
            .table-custom tbody td:nth-child(6) {
                display: none !important;
            }

            /* Column 7: Actions — grid col 3, row 2 — right aligned */
            .table-custom tbody td:nth-child(7) {
                grid-column: 3;
                grid-row: 2;
                text-align: right;
                padding: 0;
                border: none;
                align-self: start;
                white-space: nowrap;
                display: flex;
                gap: 0.35rem;
                justify-content: flex-end;
            }

            /* Touch-friendly action buttons */
            .table-custom .btn-theme-icon {
                width: 36px !important;
                height: 36px !important;
                font-size: 0.9rem !important;
                border-radius: 10px !important;
                margin: 0 !important;
            }

            /* Add size row: stacked */
            .add-size-row {
                padding: 0.65rem 0.75rem;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 0.25rem;
            }

            .add-size-row input {
                flex: 1;
                max-width: none;
                min-height: 38px;
            }

            .add-size-row button {
                min-height: 38px;
            }

            /* Add shape bar */
            .add-shape-bar {
                padding: 0.65rem 0.75rem;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .add-shape-bar input,
            .add-shape-bar select {
                flex: 1;
                max-width: none;
                min-height: 38px;
            }

            .add-shape-bar button {
                min-height: 38px;
            }
        }

        /* Small phones (≤480px): tighten card layout */
        @media (max-width: 480px) {
            .inventory-management-container {
                padding: 0.4rem;
            }

            .page-header {
                padding: 0.85rem;
                border-radius: 12px;
                margin-bottom: 0.85rem;
            }

            .page-header>div.d-flex {
                grid-template-columns: 1fr 1fr;
                gap: 0.4rem;
            }

            #btn-tab-lab,
            #btn-tab-natural,
            .page-header .btn-theme-primary,
            .page-header .btn-theme-danger-outline {
                font-size: 0.8rem;
                padding: 0.45rem 0.5rem;
                min-height: 38px;
            }

            /* Shape header tighter */
            .shape-group-header {
                padding: 0.75rem 0.85rem;
            }

            .shape-group-header .shape-name {
                font-size: 0.82rem;
            }

            /* Card body tighter */
            .shape-group-body {
                padding: 0.5rem 0.6rem;
            }

            .table-custom tbody tr.searchable-row {
                padding: 0.7rem 0.75rem;
                margin-bottom: 0.45rem;
                border-radius: 10px;
            }

            .table-custom tbody td:nth-child(1) {
                font-size: 1.1rem;
            }

            .table-custom .btn-theme-icon {
                width: 34px !important;
                height: 34px !important;
                font-size: 0.82rem !important;
            }

            /* Category chip size */
            .category-nav-item {
                padding: 0.4rem 0.75rem;
                font-size: 0.78rem;
                min-height: 34px;
            }

            /* Toast positioning */
            .melee-toast {
                bottom: 1rem;
                right: 0.75rem;
                left: 0.75rem;
                min-width: auto;
            }

            /* Modal */
            .modal-dialog {
                margin: 0.5rem;
            }

            .modal-content {
                border-radius: 12px;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1rem;
            }

            .modal-title {
                font-size: 1rem;
            }

            .category-item-container .category-delete-btn {
                opacity: 0.7;
                visibility: visible;
            }
        }

        /* History Modal Responsive */
        @media (max-width: 768px) {
            #historyModal .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            #history-diamond-info {
                flex-direction: column;
                gap: 0.75rem;
                align-items: flex-start !important;
            }

            #history-table {
                font-size: 0.75rem;
            }

            #history-table thead th,
            #history-table tbody td {
                padding: 0.5rem 0.4rem;
                font-size: 0.7rem;
            }

            /* Make history table scrollable */
            #historyModal .modal-body {
                overflow-x: auto;
            }

            #history-table {
                min-width: 900px;
            }
        }

        /* Edit Modals Responsive */
        @media (max-width: 640px) {

            #editMeleeModal .modal-dialog,
            #editTransactionModal .modal-dialog {
                margin: 1rem;
            }

            #editMeleeModal .modal-body,
            #editTransactionModal .modal-body {
                padding: 1.5rem 1rem;
            }

            #editMeleeModal .form-label,
            #editTransactionModal .form-label {
                font-size: 0.75rem;
            }

            #editMeleeModal .form-control,
            #editTransactionModal .form-control {
                font-size: 0.88rem;
                padding: 0.55rem 0.75rem;
                min-height: 42px;
            }

            #editMeleeModal button[type="submit"],
            #editTransactionModal button[type="submit"] {
                min-height: 44px;
                font-size: 0.9rem;
            }
        }

        /* Transaction Modal Responsive */
        @media (max-width: 768px) {
            #transactionModal .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            #transactionModal .modal-body {
                padding: 1rem;
            }

            #transactionModal .form-label {
                font-size: 0.82rem;
            }

            #transactionModal .form-control,
            #transactionModal select,
            #transactionModal textarea {
                font-size: 0.88rem;
                padding: 0.6rem 0.8rem;
                min-height: 44px;
            }

            #transactionModal .btn {
                padding: 0.65rem 1rem;
                font-size: 0.88rem;
                min-height: 44px;
            }

            #selection_context {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start !important;
            }
        }

        /* Quick Order Modal Responsive */
        @media (max-width: 640px) {
            #quickOrderModal .modal-dialog {
                margin: 1rem;
            }

            #quickOrderModal .order-quick-details .row {
                flex-direction: column;
            }

            #quickOrderModal .order-quick-details .col-4,
            #quickOrderModal .order-quick-details .col-6,
            #quickOrderModal .order-quick-details .col-8 {
                width: 100%;
                text-align: left !important;
                margin-bottom: 0.75rem;
            }
        }

        /* Landscape orientation optimizations */
        @media (max-height: 500px) and (orientation: landscape) {
            .sidebar-panel {
                max-height: 300px;
                overflow-y: auto;
            }

            .page-header {
                padding: 0.75rem 1rem;
            }

            .modal-dialog-scrollable .modal-body {
                max-height: 250px;
            }
        }

        /* Dark theme adjustments for card mode */
        @media (max-width: 768px) {
            [data-theme="dark"] .shape-group-header {
                border-bottom: 1px solid rgba(148, 163, 184, 0.15);
            }

            [data-theme="dark"] .add-size-row,
            [data-theme="dark"] .add-shape-bar {
                background: rgba(15, 23, 42, 0.4);
            }
        }

        /* Always-on touch target sizes for touch devices */
        @media (hover: none) and (pointer: coarse) {

            button,
            .btn,
            .shape-group-header {
                min-height: 44px;
            }

            .category-nav-item {
                min-height: 36px;
            }

            .btn-theme-icon {
                min-width: 36px;
                min-height: 36px;
            }
        }

        /* Print styles */
        @media print {

            .page-header>div:last-child,
            .sidebar-panel,
            .table-header>div:last-child,
            .btn-theme-icon,
            .add-size-row,
            .add-shape-bar,
            .category-delete-btn {
                display: none !important;
            }

            .inventory-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .main-panel {
                width: 100%;
            }

            .table-custom {
                min-width: auto !important;
            }

            .table-custom,
            .table-custom thead,
            .table-custom tbody,
            .table-custom tr,
            .table-custom th,
            .table-custom td {
                display: table !important;
            }

            .table-custom thead {
                display: table-header-group !important;
            }

            .table-custom tbody {
                display: table-row-group !important;
            }

            .table-custom tr {
                display: table-row !important;
            }

            .table-custom th,
            .table-custom td {
                display: table-cell !important;
            }
        }
    </style>

    <div class="inventory-management-container">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h2 class="mb-1 fw-bold text-dark"><i class="bi bi-gem me-2 text-primary"></i>Melee Inventory</h2>
                <div class="text-secondary small">Manage your melee diamond stock</div>
            </div>

            <div class="d-flex gap-2">
                <!-- Tab Switcher implemented as Buttons -->
                <button class="btn btn-theme-tab active" id="btn-tab-lab" onclick="switchMainTab('lab-grown')">
                    Lab Grown
                </button>
                <button class="btn btn-theme-tab" id="btn-tab-natural" onclick="switchMainTab('natural')">
                    Natural
                </button>

                <div class="vr mx-2"></div>

                <button class="btn btn-theme-primary" onclick="openTransactionModal('in')">
                    <i class="bi bi-plus-lg me-2"></i>Add Stock
                </button>
                <button class="btn btn-theme-danger-outline" onclick="openTransactionModal('out')">
                    <i class="bi bi-dash-lg me-2"></i>Use Stock
                </button>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="inventory-card">

            <!-- LEFT SIDEBAR: Categories -->
            <div class="sidebar-panel">
                <h6 class="text-uppercase text-secondary fs-7 fw-bold mb-3 ps-2">Categories</h6>

                <!-- LAB GROWN LIST -->
                <div id="sidebar-lab-grown">
                    @forelse($labGrownCategories as $category)
                        <div class="position-relative mb-1 category-item-container">
                            <button
                                class="category-nav-item cat-btn-{{ $category->id }} w-100 d-flex justify-content-between align-items-center"
                                style="padding-right: 2.5rem; margin-bottom: 0; min-height: 48px;"
                                onclick="selectCategory('{{ $category->id }}', this)">
                                <span class="text-start" style="white-space: normal; line-height: 1.2;">
                                    <i class="bi bi-gem me-2"></i>{{ $category->name }}
                                </span>
                                <span class="badge ms-2 flex-shrink-0"
                                    style="min-width: 25px;">{{ $category->diamonds->count() }}</span>
                            </button>
                            <button
                                class="btn btn-sm text-danger position-absolute top-50 end-0 translate-middle-y me-1 p-1 category-delete-btn"
                                onclick="event.stopPropagation(); deleteMeleeCategory({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                title="Delete Category">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                            <small>No categories found.<br>Run the seeder to add defaults.</small>
                        </div>
                    @endforelse
                    <div class="mt-3">
                        <button class="btn btn-sm btn-theme-outline w-100" onclick="createMeleeCategory('lab_grown')">
                            <i class="bi bi-plus-lg me-1"></i> Add Category
                        </button>
                    </div>
                </div>

                <!-- NATURAL LIST (Hidden by default) -->
                <div id="sidebar-natural" class="hidden">
                    @forelse($naturalCategories as $category)
                        <div class="position-relative mb-1 category-item-container">
                            <button
                                class="category-nav-item cat-btn-{{ $category->id }} w-100 d-flex justify-content-between align-items-center"
                                style="padding-right: 2.5rem; margin-bottom: 0; min-height: 48px;"
                                onclick="selectCategory('{{ $category->id }}', this)">
                                <span class="text-start" style="white-space: normal; line-height: 1.2;">
                                    <i class="bi bi-diamond-half me-2"></i>{{ $category->name }}
                                </span>
                                <span class="badge ms-2 flex-shrink-0"
                                    style="min-width: 25px;">{{ $category->diamonds->count() }}</span>
                            </button>
                            <button
                                class="btn btn-sm text-danger position-absolute top-50 end-0 translate-middle-y me-1 p-1 category-delete-btn"
                                onclick="event.stopPropagation(); deleteMeleeCategory({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                title="Delete Category">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>
                            <small>No categories found.<br>Run the seeder to add defaults.</small>
                        </div>
                    @endforelse
                    <div class="mt-3">
                        <button class="btn btn-sm btn-theme-outline w-100" onclick="createMeleeCategory('natural')">
                            <i class="bi bi-plus-lg me-1"></i> Add Category
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: Shapes → Sizes -->
            <div class="main-panel">
                <!-- Dynamic Content Areas -->
                @php
                    $allCategories = $labGrownCategories->concat($naturalCategories);
                @endphp

                @foreach ($allCategories as $category)
                    @php
                        // Group diamonds by shape
                        $shapeGroups = $category->diamonds->groupBy('shape');
                    @endphp

                    <div id="cat-view-{{ $category->id }}" class="category-view hidden h-100 flex-column">
                        <!-- Toolbar -->
                        <div class="table-header">
                            <div>
                                <h5 class="fw-bold mb-0">{{ $category->name }}</h5>
                                <small class="text-muted">
                                    {{ $shapeGroups->count() }} shapes
                                    · {{ $category->diamonds->count() }} sizes
                                    · {{ $category->diamonds->sum('available_pieces') }} pcs total stock
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Search..."
                                    aria-label="Search diamonds"
                                    onkeyup="filterCategoryTable('{{ $category->id }}', this.value)"
                                    style="width: 180px;">
                                <button class="btn btn-sm btn-theme-outline" type="button"
                                    onclick="focusAddShape('{{ $category->id }}')">
                                    Add Shape
                                </button>
                            </div>
                        </div>

                        <!-- Shapes Accordion -->
                        <div class="flex-grow-1 overflow-auto" id="shapes-container-{{ $category->id }}">
                            @forelse($shapeGroups as $shapeName => $diamonds)
                                <div class="shape-group" data-shape="{{ strtolower($shapeName) }}">
                                    <button class="shape-group-header" onclick="toggleShapeGroup(this)">
                                        <div class="shape-name">
                                            <i class="bi bi-diamond-fill text-primary" style="font-size:0.8rem;"></i>
                                            {{ $shapeName }}
                                            <span class="size-count-pill">{{ $diamonds->count() }} sizes</span>
                                            @php
                                                $totalPcs = $diamonds->sum('available_pieces');
                                            @endphp
                                            @if ($totalPcs != 0)
                                                <span
                                                    class="stock-total-pill {{ $totalPcs < 0 ? 'bg-danger text-white' : '' }}">{{ $totalPcs }}
                                                    pcs</span>
                                            @endif
                                        </div>
                                        <div class="shape-meta">
                                            <i class="bi bi-chevron-down shape-chevron"></i>
                                        </div>
                                    </button>
                                    <div class="shape-group-body">
                                        <table class="table table-custom mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Size</th>
                                                    <th>Size Label</th>
                                                    <th>Stock Status</th>
                                                    <th>Avg $/Ct</th>
                                                    <th>Total Carats</th>
                                                    <th>Total Price</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($diamonds as $diamond)
                                                    @php
                                                        // Extract size number from size_label (format: "shape-size")
                                                        $sizeParts = explode('-', $diamond->size_label);
                                                        $sizeNum = end($sizeParts);
                                                    @endphp
                                                    <tr class="searchable-row"
                                                        data-search="{{ strtolower($diamond->size_label . ' ' . $diamond->shape) }}">
                                                        <td class="fw-bold">{{ $sizeNum }}</td>
                                                        <td class="text-muted small">
                                                            {{ str_replace('-', ' ', $diamond->size_label) }}
                                                        </td>
                                                        <td>
                                                            @if ($diamond->available_pieces != 0)
                                                                <span
                                                                    class="badge {{ $diamond->available_pieces > 0 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} border px-3 py-2 rounded-pill"
                                                                    style="cursor:pointer"
                                                                    onclick="openHistoryModal({{ $diamond->id }})"
                                                                    title="Click to view history">
                                                                    {{ $diamond->available_pieces }} pcs
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill"
                                                                    style="cursor:pointer"
                                                                    onclick="openHistoryModal({{ $diamond->id }})"
                                                                    title="Click to view history">
                                                                    Out of Stock
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="fw-medium">
                                                            ${{ number_format($diamond->purchase_price_per_ct ?? 0, 2) }}
                                                        </td>
                                                        <td class="fw-medium">
                                                            {{ number_format($diamond->available_carat_weight ?? 0, 3) }}
                                                            ct
                                                        </td>
                                                        <td class="fw-bold">
                                                            ${{ number_format($diamond->total_price ?? 0, 2) }}</td>
                                                        <td class="text-end">
                                                            <button class="btn btn-sm btn-theme-icon btn-theme-icon-in"
                                                                data-action="in" data-diamond-id="{{ $diamond->id }}"
                                                                data-diamond-name="{{ $diamond->shape }} {{ $diamond->size_label }}"
                                                                data-category-name="{{ $category->name }}"
                                                                title="Add Stock"
                                                                onclick="openTransactionModal(this.dataset.action, this.dataset.diamondId, this.dataset.diamondName, this.dataset.categoryName)">
                                                                <i class="bi bi-plus-lg"></i>
                                                            </button>
                                                            <button
                                                                class="btn btn-sm btn-theme-icon btn-theme-icon-out ms-1"
                                                                data-action="out" data-diamond-id="{{ $diamond->id }}"
                                                                data-diamond-name="{{ $diamond->shape }} {{ $diamond->size_label }}"
                                                                data-category-name="{{ $category->name }}"
                                                                title="Use Stock"
                                                                onclick="openTransactionModal(this.dataset.action, this.dataset.diamondId, this.dataset.diamondName, this.dataset.categoryName)">
                                                                <i class="bi bi-dash-lg"></i>
                                                            </button>
                                                            @php
                                                                $lastTx = $diamond->transactions->first();
                                                                $lastTxPieces = $lastTx ? $lastTx->pieces : '';
                                                                $lastTxCarats = $lastTx ? $lastTx->carat_weight : '';
                                                            @endphp
                                                            <button
                                                                class="btn btn-sm btn-theme-icon btn-theme-icon-edit ms-1"
                                                                title="Edit Melee"
                                                                onclick="openEditModal({{ $diamond->id }}, '{{ $diamond->shape }}', '{{ explode('-', $diamond->size_label)[1] ?? str_replace(strtolower($diamond->shape) . '-', '', $diamond->size_label) }}', '{{ $lastTxPieces }}', '{{ $lastTxCarats }}')">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <button
                                                                class="btn btn-sm btn-theme-icon btn-theme-icon-delete ms-1"
                                                                title="Delete Melee"
                                                                onclick="deleteMeleeDiamond({{ $diamond->id }}, '{{ $diamond->shape }} {{ $diamond->size_label }}')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <!-- Add Size Row (inside each shape) -->
                                        <div class="add-size-row">
                                            <input type="text" class="form-control form-control-sm add-size-input"
                                                placeholder="e.g. 1.5 or 4*2" data-category-id="{{ $category->id }}"
                                                data-shape="{{ $shapeName }}">
                                            <button class="btn btn-sm btn-primary" onclick="addSizeToShape(this)">
                                                Add Size
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                {{-- Show available shapes from allowed_shapes even if no diamonds exist --}}
                            @endforelse

                            {{-- Show empty shapes (from allowed_shapes with no diamonds yet) --}}
                            @if ($category->allowed_shapes)
                                @foreach ($category->allowed_shapes as $allowedShape)
                                    @if (!$shapeGroups->has($allowedShape))
                                        <div class="shape-group" data-shape="{{ strtolower($allowedShape) }}">
                                            <button class="shape-group-header" onclick="toggleShapeGroup(this)">
                                                <div class="shape-name">
                                                    <i class="bi bi-diamond text-secondary" style="font-size:0.8rem;"></i>
                                                    {{ $allowedShape }}
                                                    <span class="size-count-pill">0 sizes</span>
                                                </div>
                                                <div class="shape-meta">
                                                    <i class="bi bi-chevron-down shape-chevron"></i>
                                                </div>
                                            </button>
                                            <div class="shape-group-body">
                                                <div class="text-center py-4 text-muted">
                                                    <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                                                    <small>No sizes added yet. Add one below.</small>
                                                </div>

                                                <!-- Add Size Row -->
                                                <div class="add-size-row">
                                                    <input type="text"
                                                        class="form-control form-control-sm add-size-input"
                                                        placeholder="e.g. 1.5 or 4*2"
                                                        data-category-id="{{ $category->id }}"
                                                        data-shape="{{ $allowedShape }}">
                                                    <button class="btn btn-sm btn-primary" onclick="addSizeToShape(this)">
                                                        Add Size
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <!-- Add New Shape Bar at the bottom -->
                            <div class="add-shape-bar" id="add-shape-bar-{{ $category->id }}">
                                <input type="text" class="form-control form-control-sm new-shape-name"
                                    placeholder="New shape name" data-category-id="{{ $category->id }}">
                                <input type="text" class="form-control form-control-sm new-shape-size"
                                    placeholder="Size (e.g. 1.0 or 4*2)" data-category-id="{{ $category->id }}">
                                <button class="btn btn-sm btn-theme-outline"
                                    onclick="addNewShape(this, '{{ $category->id }}')">
                                    Add Shape
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Initial Empty State -->
                <div id="empty-state-placeholder"
                    class="d-flex align-items-center justify-content-center h-100 flex-column text-muted">
                    <i class="bi bi-arrow-left-circle fs-1 mb-3"></i>
                    <h5>Select a category from the sidebar</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="meleeToastContainer" class="melee-toast"></div>

    <!-- Stock History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                <div class="modal-header"
                    style="background:linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; border:0;">
                    <h5 class="modal-title" id="historyModalLabel">
                        <i class="bi bi-clock-history me-2"></i>Stock History
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Diamond Info Header -->
                    <div id="history-diamond-info"
                        class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <strong id="history-diamond-name">Loading...</strong>
                            <div class="text-muted small" id="history-diamond-detail"></div>
                            <div class="text-muted small fw-semibold" id="history-price-summary"></div>
                        </div>
                        <div>
                            <span id="history-stock-badge"
                                class="badge bg-primary-subtle text-primary px-3 py-2 fs-6 rounded-pill"></span>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div id="history-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Loading history...</p>
                    </div>
                    <div id="history-empty" class="text-center py-5 hidden">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted">No transactions recorded yet.</p>
                    </div>
                    <table class="table table-custom mb-0" id="history-table" style="display:none;">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>User</th>
                                <th>Pieces</th>
                                <th>Carat</th>
                                <th>Avg $/Ct</th>
                                <th>Total Price</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="history-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Order View Modal -->
    <div class="modal fade" id="quickOrderModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"
                style="border-radius:12px; overflow:hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title"><i class="bi bi-card-checklist me-2"></i>Order Overview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="quick-order-content">
                    <div class="text-center py-5" id="quick-order-loading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Fetching order details...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <a href="#" id="quick-order-full-link" class="btn btn-primary w-100 py-2 fw-bold">View Full
                        Order
                        Details</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Melee Diamond Modal -->
    <div class="modal fade" id="editMeleeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 bg-light pb-0">
                    <h5 class="modal-title fw-bold">Edit Melee Diamond</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editMeleeForm">
                        @csrf
                        <input type="hidden" id="edit_melee_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Shape</label>
                            <input type="text" id="edit_shape" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Size</label>
                            <input type="text" id="edit_size" class="form-control" required>
                        </div>

                        <hr class="my-4">
                        <div class="mb-3 text-secondary small fw-bold text-uppercase">
                            Latest "IN" Stock Entry <span class="fw-normal text-muted text-lowercase">(Leave blank if
                                none)</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Pieces</label>
                                <input type="number" id="edit_last_pieces" class="form-control" placeholder="0"
                                    min="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Carats</label>
                                <input type="number" step="0.001" id="edit_last_carats" class="form-control"
                                    placeholder="0.000" min="0">
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary w-100 py-2 fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2"
                            id="btnUpdateMelee">
                            <span>Update Details</span>
                            <i class="bi bi-save"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 bg-light pb-0">
                    <h5 class="modal-title fw-bold">Edit Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editTransactionForm">
                        @csrf
                        <input type="hidden" id="edit_tx_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Pieces</label>
                            <input type="number" id="edit_tx_pieces" class="form-control" required min="1">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary text-uppercase fs-8 ls-1">Carats</label>
                            <input type="number" step="0.001" id="edit_tx_carats" class="form-control"
                                min="0">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2" id="btnUpdateTransaction">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('melee.partials.transaction_modal')

    <script>
        let activeCategoryId = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Check localStorage for previous tab, otherwise default to lab-grown
            const savedTab = localStorage.getItem('meleeActiveTab') || 'lab-grown';

            // Ensure buttons have the correct initial classes if switchMainTab needs them
            switchMainTab(savedTab);

            // Initialize Select2 for the melee diamond search dropdown
            if ($ && $.fn.select2) {
                $('#modal_diamond_select').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#transactionModal'),
                    placeholder: 'Search Melee Diamond (Shape, Size, etc.)',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('melee.search') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0
                });

                // When a diamond is selected from dropdown, populate the selection context
                $('#modal_diamond_select').on('select2:select', function(e) {
                    var data = e.params.data;
                    setModalSelection(data.id, data.text.split(' (Stock')[0], data.category_name);
                });
            }
        });

        // ==========================================
        //  EDIT / DELETE STOCK FUNCTIONS
        // ==========================================
        function openEditModal(id, shape, size, lastPieces, lastCarats) {
            document.getElementById('edit_melee_id').value = id;
            document.getElementById('edit_shape').value = shape;
            document.getElementById('edit_size').value = size;

            document.getElementById('edit_last_pieces').value = lastPieces || '';
            document.getElementById('edit_last_carats').value = lastCarats || '';
            document.getElementById('edit_last_pieces').disabled = !lastPieces;
            document.getElementById('edit_last_carats').disabled = !lastPieces;

            new bootstrap.Modal(document.getElementById('editMeleeModal')).show();
        }

        // Handle Edit Form Submission
        document.getElementById('editMeleeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('edit_melee_id').value;
            const shape = document.getElementById('edit_shape').value;
            const size = document.getElementById('edit_size').value;
            const last_pieces = document.getElementById('edit_last_pieces').value;
            const last_carats = document.getElementById('edit_last_carats').value;

            const btn = document.getElementById('btnUpdateMelee');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

            fetch(`{{ url('admin/melee') }}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        shape,
                        size,
                        last_pieces,
                        last_carats
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success'
                            }).then(() => location.reload());
                        } else {
                            alert(data.message);
                            location.reload();
                        }
                    } else {
                        if (window.Swal) Swal.fire('Error', data.message || 'Validation failed.', 'error');
                        else alert('Error: ' + (data.message || 'Validation failed.'));
                    }
                })
                .catch(err => {
                    console.error('Error updating melee diamond:', err);
                    if (window.Swal) Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    else alert('An unexpected error occurred.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });

        function deleteMeleeDiamond(id, name) {
            if (!window.Swal) {
                if (confirm(
                        `Are you sure you want to completely delete ${name}? All transaction history for this size will also be deleted.`
                    )) {
                    executeDelete(id);
                }
                return;
            }

            Swal.fire({
                title: 'Delete Melee Stock?',
                html: `You are about to delete <strong>${name}</strong>.<br><br><span class="text-danger">Warning: This will also permanently delete all transaction history for this size!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeDelete(id);
                }
            });
        }

        function executeDelete(id) {
            fetch(`{{ url('admin/melee') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (window.Swal) {
                            Swal.fire('Deleted!', data.message, 'success').then(() => location.reload());
                        } else {
                            alert(data.message);
                            location.reload();
                        }
                    } else {
                        if (window.Swal) Swal.fire('Error', data.message || 'Could not delete.', 'error');
                        else alert('Error: ' + (data.message || 'Could not delete.'));
                    }
                })
                .catch(err => {
                    console.error('Error deleting melee diamond:', err);
                    if (window.Swal) Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    else alert('An unexpected error occurred.');
                });
        }

        function switchMainTab(type) {
            // Save state to localStorage to persist across reloads
            localStorage.setItem('meleeActiveTab', type);

            // Reset buttons
            document.getElementById('btn-tab-lab').className = 'btn btn-theme-tab';
            document.getElementById('btn-tab-natural').className = 'btn btn-theme-tab';

            if (type === 'lab-grown') {
                document.getElementById('btn-tab-lab').className = 'btn btn-theme-tab active';
                document.getElementById('sidebar-lab-grown').classList.remove('hidden');
                document.getElementById('sidebar-natural').classList.add('hidden');

                const savedCatId = localStorage.getItem('meleeActiveCategory');
                let btnToClick = savedCatId ? document.querySelector(`#sidebar-lab-grown .cat-btn-${savedCatId}`) : null;
                if (!btnToClick) btnToClick = document.querySelector('#sidebar-lab-grown .category-nav-item');
                if (btnToClick) btnToClick.click();
            } else {
                document.getElementById('btn-tab-natural').className = 'btn btn-theme-tab active';
                document.getElementById('sidebar-lab-grown').classList.add('hidden');
                document.getElementById('sidebar-natural').classList.remove('hidden');

                const savedCatId = localStorage.getItem('meleeActiveCategory');
                let btnToClick = savedCatId ? document.querySelector(`#sidebar-natural .cat-btn-${savedCatId}`) : null;
                if (!btnToClick) btnToClick = document.querySelector('#sidebar-natural .category-nav-item');
                if (btnToClick) btnToClick.click();
            }
        }

        function selectCategory(catId, btn) {
            activeCategoryId = catId;
            localStorage.setItem('meleeActiveCategory', catId);

            // 1. Sidebar Active State
            document.querySelectorAll('.category-nav-item').forEach(el => el.classList.remove('active'));
            btn.classList.add('active');

            // 2. Hide all views
            document.querySelectorAll('.category-view').forEach(el => {
                el.classList.remove('d-flex');
                el.classList.add('hidden');
            });

            document.getElementById('empty-state-placeholder').classList.add('hidden');
            document.getElementById('empty-state-placeholder').classList.remove('d-flex');

            // 3. Show target view
            const target = document.getElementById('cat-view-' + catId);
            if (target) {
                target.classList.remove('hidden');
                target.classList.add('d-flex');
            }
        }

        function filterCategoryTable(catId, term) {
            term = term.toLowerCase();
            const container = document.getElementById('shapes-container-' + catId);
            if (!container) return;

            const shapeGroups = container.querySelectorAll('.shape-group');

            shapeGroups.forEach(group => {
                const shapeName = group.getAttribute('data-shape') || '';
                const rows = group.querySelectorAll('.searchable-row');
                let hasVisibleRow = false;

                if (term === '') {
                    // Show all
                    group.classList.remove('hidden');
                    rows.forEach(row => row.classList.remove('hidden'));
                    return;
                }

                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search');
                    if (searchData && searchData.includes(term)) {
                        row.classList.remove('hidden');
                        hasVisibleRow = true;
                    } else {
                        row.classList.add('hidden');
                    }
                });

                // Also match on shape name
                if (shapeName.includes(term)) {
                    group.classList.remove('hidden');
                    rows.forEach(row => row.classList.remove('hidden'));
                } else if (hasVisibleRow) {
                    group.classList.remove('hidden');
                } else {
                    group.classList.add('hidden');
                }
            });
        }

        function toggleShapeGroup(btn) {
            const group = btn.closest('.shape-group');
            group.classList.toggle('open');
        }

        function focusAddShape(categoryId) {
            const bar = document.getElementById(`add-shape-bar-${categoryId}`);
            if (!bar) return;
            bar.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            const nameInput = bar.querySelector('.new-shape-name');
            if (nameInput) nameInput.focus();
        }

        // Add a new size to an existing shape
        function addSizeToShape(btn) {
            const container = btn.closest('.add-size-row');
            const input = container.querySelector('.add-size-input');
            const size = input.value.trim();
            const categoryId = input.dataset.categoryId;
            const shape = input.dataset.shape;

            if (!size) {
                showMeleeToast('Please enter a valid size value.', 'warning');
                input.focus();
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch("{{ route('melee.add-shape') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        category_id: categoryId,
                        shape: shape,
                        size: size
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showMeleeToast(data.message, 'success');
                        location.reload();
                    } else {
                        showMeleeToast(data.message || 'Error adding size.', 'danger');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showMeleeToast('An error occurred.', 'danger');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Add Size';
                });
        }

        // Add a completely new shape with initial size
        function addNewShape(btn, categoryId) {
            const bar = btn.closest('.add-shape-bar');
            const nameInput = bar.querySelector('.new-shape-name');
            const sizeInput = bar.querySelector('.new-shape-size');
            const shape = nameInput.value.trim();
            const size = sizeInput.value.trim();

            if (!shape) {
                showMeleeToast('Please enter a shape name.', 'warning');
                nameInput.focus();
                return;
            }
            if (!size) {
                showMeleeToast('Please enter a valid size value.', 'warning');
                sizeInput.focus();
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch("{{ route('melee.add-shape') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        category_id: categoryId,
                        shape: shape,
                        size: size
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showMeleeToast(data.message, 'success');
                        location.reload();
                    } else {
                        showMeleeToast(data.message || 'Error adding shape.', 'danger');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showMeleeToast('An error occurred.', 'danger');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Add Shape';
                });
        }

        // Simple toast helper
        function showMeleeToast(msg, type = 'info') {
            const container = document.getElementById('meleeToastContainer');
            const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : type === 'warning' ?
                'bg-warning text-dark' : 'bg-primary';
            const iconClass = type === 'success' ? 'bi-check-circle' : type === 'danger' ? 'bi-exclamation-triangle' :
                type === 'warning' ? 'bi-exclamation-circle' : 'bi-info-circle';

            const toastEl = document.createElement('div');
            toastEl.className = `toast show align-items-center text-white ${bgClass} border-0 mb-2`;
            toastEl.setAttribute('role', 'alert');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body"><i class="bi ${iconClass} me-2"></i>${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
                </div>
            `;
            container.appendChild(toastEl);
            setTimeout(() => toastEl.remove(), 4000);
        }

        // Transaction Modal logic
        function openTransactionModal(type, diamondId, diamondName, categoryName) {
            document.getElementById('transactionForm').reset();
            $('#modal_diamond_select').val(null).trigger('change'); // Reset Select2

            // Set type & update theme
            if (type === 'in') {
                if (document.getElementById('type_in')) document.getElementById('type_in').checked = true;
                updateModalTheme('in');
            } else {
                if (document.getElementById('type_out')) document.getElementById('type_out').checked = true;
                updateModalTheme('out');
            }

            if (diamondId) {
                setModalSelection(diamondId, diamondName, categoryName);
            } else {
                resetModalSelection();
            }

            var modalEl = document.getElementById('transactionModal');
            if (modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        function setModalSelection(id, name, cat) {
            if (document.getElementById('modal_diamond_id')) document.getElementById('modal_diamond_id').value = id;
            if (document.getElementById('modal_item_name')) document.getElementById('modal_item_name').textContent = name ||
                'Unknown Item';
            if (document.getElementById('modal_item_cat')) document.getElementById('modal_item_cat').textContent = cat ||
                'Category';

            if (document.getElementById('selection_context')) document.getElementById('selection_context').style.display =
                'flex';
            if (document.getElementById('diamond_selector_container')) document.getElementById('diamond_selector_container')
                .style.display = 'none';
        }

        function resetModalSelection() {
            if (document.getElementById('modal_diamond_id')) document.getElementById('modal_diamond_id').value = '';
            if (document.getElementById('selection_context')) document.getElementById('selection_context').style.display =
                'none';
            if (document.getElementById('diamond_selector_container')) document.getElementById('diamond_selector_container')
                .style.display = 'block';
        }

        // ── Stock History Modal ──
        function openHistoryModal(diamondId) {
            // Show modal & loading state
            document.getElementById('history-loading').classList.remove('hidden');
            document.getElementById('history-empty').classList.add('hidden');
            document.getElementById('history-table').style.display = 'none';
            document.getElementById('history-diamond-name').textContent = 'Loading...';
            document.getElementById('history-diamond-detail').textContent = '';
            document.getElementById('history-price-summary').textContent = '';
            document.getElementById('history-stock-badge').textContent = '';

            var historyModalEl = document.getElementById('historyModal');
            var historyModal = new bootstrap.Modal(historyModalEl);
            historyModal.show();

            activeHistoryDiamondId = diamondId;

            // Fetch history
            fetch(`/admin/melee/history/${diamondId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('history-loading').classList.add('hidden');

                    // Populate diamond info header
                    const d = data.diamond;
                    document.getElementById('history-diamond-name').textContent = `${d.category_name} — ${d.shape}`;
                    document.getElementById('history-diamond-detail').textContent =
                        `Size: ${d.size_label.replace('-', ' ')}`;
                    document.getElementById('history-stock-badge').textContent = `${d.available_pieces} pcs available`;

                    // Populate transactions
                    const txns = data.transactions;
                    const tbody = document.getElementById('history-tbody');
                    tbody.innerHTML = '';

                    if (!txns || txns.length === 0) {
                        document.getElementById('history-empty').classList.remove('hidden');
                        return;
                    }

                    document.getElementById('history-table').style.display = '';

                    txns.forEach(t => {
                        const typeBadge = t.type === 'in' ?
                            '<span class="badge bg-success-subtle text-success rounded-pill px-3 py-1"><i class="bi bi-arrow-down-circle me-1"></i>Stock IN</span>' :
                            t.type === 'out' ?
                            '<span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-arrow-up-circle me-1"></i>Stock OUT</span>' :
                            '<span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1"><i class="bi bi-arrow-repeat me-1"></i>Adjust</span>';

                        const refText = t.reference_type === 'order' && t.reference_id ?
                            `<a href="javascript:void(0)" onclick="viewOrderQuick(${t.reference_id})" class="text-primary text-decoration-none fw-bold"><i class="bi bi-link-45deg"></i>Order #${t.reference_id}</a>` :
                            (t.reference_type || 'Manual');

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${typeBadge}</td>
                            <td class="fw-medium">${t.user_name}</td>
                            <td class="fw-bold">${Math.abs(t.pieces)}</td>
                            <td>${t.carat_weight || '-'}</td>
                            <td>$${parseFloat(t.cost_per_ct).toFixed(2)}</td>
                            <td class="fw-bold text-success">$${parseFloat(t.total_price).toFixed(2)}</td>
                            <td>${refText}</td>
                            <td class="text-muted small">${t.notes || '-'}</td>
                            <td>
                                <span class="small">${t.created_at}</span>
                                <br><span class="text-muted small">${t.time_ago}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-light text-secondary border me-1" 
                                    onclick="openEditTransactionModal(${t.id}, ${Math.abs(t.pieces)}, '${t.carat_weight || 0}', '${t.type}')" 
                                    title="Edit Transaction">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger border" 
                                    onclick="deleteTransaction(${t.id}, '${t.type}')" 
                                    title="Delete Transaction">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('history-loading').classList.add('hidden');
                    document.getElementById('history-empty').classList.remove('hidden');
                    document.getElementById('history-empty').querySelector('p').textContent = 'Error loading history.';
                });
        }

        // ── Quick Order View ──
        function viewOrderQuick(orderId) {
            const content = document.getElementById('quick-order-content');
            const loading = document.getElementById('quick-order-loading');
            const link = document.getElementById('quick-order-full-link');

            // Show modal first
            const modal = new bootstrap.Modal(document.getElementById('quickOrderModal'));
            modal.show();

            // Clear old content & show loading
            loading.style.display = 'block';
            const oldDetails = content.querySelector('.order-quick-details');
            if (oldDetails) oldDetails.remove();
            link.classList.add('disabled');

            fetch(`/admin/orders/${orderId}/quick-view`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    loading.style.display = 'none';
                    link.classList.remove('disabled');
                    link.href = data.url;

                    const detailsHtml = `
                    <div class="order-quick-details">
                        <div class="p-3 bg-light border-bottom">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h6 class="mb-0 fw-bold">${data.client_name}</h6>
                                    <small class="text-muted">${data.company} • ${data.created_at}</small>
                                </div>
                                <div class="col-4 text-end">
                                    <span class="badge bg-primary rounded-pill px-3">${data.status.replace('_', ' ').toUpperCase()}</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase fw-bold d-block mb-1">Product Details</label>
                                <p class="mb-1 fw-medium">${data.jewellery_details || 'No jewellery details'}</p>
                                <small class="text-muted">${data.diamond_details || ''}</small>
                            </div>

                            ${data.diamond_sku ? `
                                    <div class="mb-3">
                                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Diamond SKU</label>
                                        <code class="fs-6 text-primary fw-bold">${data.diamond_sku}</code>
                                    </div>` : ''}

                            ${data.melee_details ? `
                                    <div class="p-3 border rounded bg-light mb-3">
                                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Melee Component</label>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>${data.melee_details.name}</span>
                                            <span class="fw-bold text-dark">${data.melee_details.pieces} pcs / ${data.melee_details.carat} ct</span>
                                        </div>
                                    </div>` : ''}

                            <div class="row pt-3 border-top">
                                <div class="col-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Total Value</label>
                                    <h5 class="mb-0 fw-bold text-success">$ ${data.gross_sell}</h5>
                                </div>
                                <div class="col-6 text-end">
                                    <label class="text-muted small text-uppercase fw-bold d-block mb-1">Submitted By</label>
                                    <span class="fw-medium">${data.submitted_by}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    content.insertAdjacentHTML('beforeend', detailsHtml);
                })
                .catch(err => {
                    console.error(err);
                    loading.innerHTML = '<div class="alert alert-danger m-3">Failed to load order details.</div>';
                });
        }

        // ── Transaction Edit / Delete ──
        let activeHistoryDiamondId = null;

        function openEditTransactionModal(id, pieces, carats, type) {
            document.getElementById('edit_tx_id').value = id;
            document.getElementById('edit_tx_pieces').value = pieces;
            document.getElementById('edit_tx_carats').value = carats;
            new bootstrap.Modal(document.getElementById('editTransactionModal')).show();
        }

        document.getElementById('editTransactionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('edit_tx_id').value;
            const pieces = document.getElementById('edit_tx_pieces').value;
            const carat_weight = document.getElementById('edit_tx_carats').value;
            const btn = document.getElementById('btnUpdateTransaction');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            fetch(`{{ url('admin/melee/transaction') }}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        pieces,
                        carat_weight
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editTransactionModal')).hide();

                        if (window.Swal) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                if (activeHistoryDiamondId) openHistoryModal(activeHistoryDiamondId);
                                else location.reload();
                            });
                        } else {
                            alert('Success: ' + data.message);
                            if (activeHistoryDiamondId) openHistoryModal(activeHistoryDiamondId);
                            else location.reload();
                        }
                    } else {
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Error updating transaction',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        } else {
                            alert('Error: ' + (data.message || 'Error updating transaction'));
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    showMeleeToast('An unexpected error occurred.', 'danger');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });

        function deleteTransaction(id, type) {
            const confirmMsg =
                `Are you sure you want to completely delete this ${type.toUpperCase()} transaction? This will reverse its effect on the total stock balance.`;

            if (window.Swal) {
                Swal.fire({
                    title: 'Delete Transaction?',
                    html: `You are about to delete this <strong>${type.toUpperCase()}</strong> transaction.<br><br><span class="text-danger">Warning: This action will alter your total stock permanently.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) executeTransactionDelete(id);
                });
            } else {
                if (confirm(confirmMsg)) executeTransactionDelete(id);
            }
        }

        function executeTransactionDelete(id) {
            fetch(`{{ url('admin/melee/transaction') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                if (activeHistoryDiamondId) openHistoryModal(activeHistoryDiamondId);
                                else location.reload();
                            });
                        } else {
                            alert('Deleted: ' + data.message);
                            if (activeHistoryDiamondId) openHistoryModal(activeHistoryDiamondId);
                            else location.reload();
                        }
                    } else {
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Error deleting transaction',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        } else {
                            alert('Error: ' + (data.message || 'Error deleting transaction'));
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    showMeleeToast('An unexpected error occurred.', 'danger');
                });
        }

        // ── Category Management ──
        function createMeleeCategory(type) {
            if (!window.Swal) {
                const name = prompt("Enter category name:");
                if (name) submitNewCategory(name, type);
                return;
            }

            Swal.fire({
                title: 'Add New Category',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'e.g. Round Brilliant'
                },
                showCancelButton: true,
                confirmButtonText: 'Create',
                showLoaderOnConfirm: true,
                preConfirm: (name) => {
                    if (!name) Swal.showValidationMessage("Name cannot be empty");
                    return name;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitNewCategory(result.value, type);
                }
            });
        }

        function submitNewCategory(name, type) {
            fetch(`{{ route('melee.category.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name,
                        type
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showMeleeToast(data.message, 'success');
                        location.reload();
                    } else {
                        if (window.Swal) Swal.fire('Error', data.message, 'error');
                        else alert(data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showMeleeToast('An unexpected error occurred.', 'danger');
                });
        }

        function deleteMeleeCategory(id, name) {
            if (window.Swal) {
                Swal.fire({
                    title: 'Delete Category?',
                    html: `You are about to delete <strong>${name}</strong>.<br><br><span class="text-danger fw-bold">Warning: This will permanently delete ALL diamonds and their transaction history inside this category!</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) executeCategoryDelete(id);
                });
            } else {
                if (confirm(
                        `Are you sure you want to completely delete ${name}? ALL diamonds inside this category will be deleted!`
                    )) {
                    executeCategoryDelete(id);
                }
            }
        }

        function executeCategoryDelete(id) {
            fetch(`{{ url('admin/melee/category') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (window.Swal) {
                            Swal.fire('Deleted!', data.message, 'success').then(() => location.reload());
                        } else {
                            alert(data.message);
                            location.reload();
                        }
                    } else {
                        if (window.Swal) Swal.fire('Error', data.message || 'Could not delete.', 'error');
                        else alert('Error: ' + (data.message || 'Could not delete.'));
                    }
                })
                .catch(err => {
                    console.error('Error deleting category:', err);
                    showMeleeToast('An unexpected error occurred.', 'danger');
                });
        }
    </script>
@endsection
