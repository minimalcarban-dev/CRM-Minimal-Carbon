<style>
    /* Stock Table Responsive Styles */
    .stock-table-container {
        position: relative;
    }

    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
    }

    /* Base table styles */
    .stock-table-container .table {
        margin-bottom: 0;
    }

    .stock-table-container .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.75rem;
        padding: 1rem 1.5rem;
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }

    .stock-table-container .table tbody td {
        padding: 0.85rem 1.5rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .stock-table-container .table tbody tr {
        transition: background-color 0.15s ease;
    }

    .stock-table-container .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    /* Dark mode styles */
    [data-theme="dark"] .stock-table-container .table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--text-primary, #f1f5f9);
        --bs-table-border-color: rgba(148, 163, 184, 0.22);
        --bs-table-hover-bg: rgba(255, 255, 255, 0.03);
        color: var(--text-primary, #f1f5f9);
    }

    [data-theme="dark"] .stock-table-container .table thead.bg-light th {
        background: rgba(15, 23, 42, 0.5) !important;
        color: var(--text-secondary, #94a3b8);
        border-bottom-color: rgba(148, 163, 184, 0.34);
    }

    [data-theme="dark"] .stock-table-container .table tbody td {
        color: var(--text-primary, #f1f5f9);
        border-bottom-color: rgba(148, 163, 184, 0.22);
    }

    [data-theme="dark"] .stock-table-container .table tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.03);
    }

    [data-theme="dark"] .stock-table-container .table .bg-success-subtle {
        background: rgba(16, 185, 129, 0.18) !important;
        color: #34d399 !important;
    }

    [data-theme="dark"] .stock-table-container .table .bg-danger-subtle {
        background: rgba(239, 68, 68, 0.18) !important;
        color: #f87171 !important;
    }

    [data-theme="dark"] .stock-table-container .table .text-muted {
        color: var(--text-secondary, #94a3b8) !important;
    }

    /* ── MOBILE CARD VIEW ── */
    @media (max-width: 768px) {

        /* Hide the regular table on mobile */
        .stock-table-container .table,
        .stock-table-container .table thead,
        .stock-table-container .table tbody,
        .stock-table-container .table th,
        .stock-table-container .table td,
        .stock-table-container .table tr {
            display: block;
        }

        .stock-table-container .table thead {
            display: none;
        }

        .stock-table-container .table-responsive {
            overflow-x: visible;
            overflow: visible;
        }

        .stock-table-container .table {
            min-width: unset !important;
            font-size: 0.9rem;
        }

        /* Each row becomes a card */
        .stock-table-container .table tbody tr {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 1rem;
            margin-bottom: 0.6rem;
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 12px;
            background: var(--bg-card, #fff);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        }

        .stock-table-container .table tbody tr:hover {
            background-color: transparent;
        }

        [data-theme="dark"] .stock-table-container .table tbody tr {
            background: var(--bg-card, #1e293b);
            border-color: rgba(148, 163, 184, 0.2);
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.2);
        }

        /* Hide all tds by default, show as flex children */
        .stock-table-container .table tbody td {
            padding: 0;
            border: none;
            font-size: 0.85rem;
        }

        /* Shape column — large left label */
        .stock-table-container .table tbody td:nth-child(1) {
            flex: 1 1 auto;
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary, #0f172a);
        }

        [data-theme="dark"] .stock-table-container .table tbody td:nth-child(1) {
            color: var(--text-primary, #f1f5f9) !important;
        }

        /* Color column */
        .stock-table-container .table tbody td:nth-child(2) {
            flex: 0 0 auto;
            font-size: 0.8rem;
            color: var(--text-secondary, #64748b);
            background: rgba(99, 102, 241, 0.07);
            border-radius: 6px;
            padding: 0.15rem 0.5rem;
        }

        /* Size label column */
        .stock-table-container .table tbody td:nth-child(3) {
            flex: 0 0 auto;
            font-size: 0.78rem;
            color: var(--text-secondary, #64748b);
        }

        /* Sieve column — hide on mobile (less important) */
        .stock-table-container .table tbody td:nth-child(4) {
            display: none;
        }

        /* Stock badge column — full new line, left aligned */
        .stock-table-container .table tbody td:nth-child(5) {
            flex: 0 0 auto;
            text-align: left;
        }

        .stock-table-container .table tbody td:nth-child(5) .badge {
            font-size: 0.8rem;
            padding: 0.35em 0.9em;
        }

        /* Price column */
        .stock-table-container .table tbody td:nth-child(6) {
            flex: 1 1 auto;
            text-align: right;
            font-weight: 600;
            color: var(--text-primary, #0f172a);
        }

        [data-theme="dark"] .stock-table-container .table tbody td:nth-child(6) {
            color: var(--text-primary, #f1f5f9) !important;
        }

        /* Actions column — fill full width, right aligned */
        .stock-table-container .table tbody td:last-child {
            flex: 0 0 auto;
            text-align: right;
            white-space: nowrap;
            display: flex;
            gap: 0.4rem;
            align-items: center;
        }

        /* Bigger touch targets for action buttons */
        .stock-table-container .btn-theme-icon {
            width: 40px !important;
            height: 40px !important;
            font-size: 1rem !important;
            border-radius: 10px !important;
        }
    }

    @media (max-width: 480px) {
        .stock-table-container .table tbody tr {
            padding: 0.75rem 0.85rem;
        }

        .stock-table-container .table tbody td:nth-child(1) {
            font-size: 0.9rem;
        }

        .stock-table-container .table tbody td:nth-child(3) {
            display: none;
            /* hide size_label on very small — visible from shape col */
        }

        .stock-table-container .btn-theme-icon {
            width: 38px !important;
            height: 38px !important;
        }
    }
</style>

<div class="stock-table-container">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Shape</th>
                    <th>Color</th>
                    <th>Size Label</th>
                    <th>Sieve</th>
                    <th class="text-center">Stock (Pcs)</th>
                    <th class="text-center">Avg $/Ct</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($diamonds as $diamond)
                    <tr class="stock-row" data-shape="{{ $diamond->shape }}">
                        <td class="ps-4 fw-medium" data-label="Shape">{{ $diamond->shape }}</td>
                        <td data-label="Color">{{ $diamond->color ?? '-' }}</td>
                        <td data-label="Size Label">{{ $diamond->size_label }}</td>
                        <td class="text-muted" data-label="Sieve">{{ $diamond->sieve_size ?? '-' }}</td>
                        <td class="text-center" data-label="Stock">
                            <span
                                class="badge {{ $diamond->available_pieces > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-3 py-2 rounded-pill">
                                {{ $diamond->available_pieces }} pcs
                            </span>
                        </td>
                        <td class="text-center" data-label="Avg $/Ct">
                            ${{ number_format($diamond->purchase_price_per_ct, 2) }}</td>
                        <td class="text-end pe-4" data-label="Actions">
                            <button class="btn btn-sm btn-theme-icon btn-theme-icon-in"
                                onclick="openTransactionModal('in', '{{ $diamond->id }}', '{{ $diamond->shape }} {{ $diamond->size_label }}', '{{ $diamond->category->name }}')"
                                title="Add Stock">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button class="btn btn-sm btn-theme-icon btn-theme-icon-out ms-1"
                                onclick="openTransactionModal('out', '{{ $diamond->id }}', '{{ $diamond->shape }} {{ $diamond->size_label }}', '{{ $diamond->category->name }}')"
                                title="Use Stock">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <p>No diamonds found in this category.</p>
                        </td>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
