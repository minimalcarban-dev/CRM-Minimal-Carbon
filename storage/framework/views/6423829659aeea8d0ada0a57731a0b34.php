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
    }

    .attr-list-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Header */
    .attr-list-header {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px var(--shadow);
    }

    .attr-header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .attr-breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--gray);
        margin-bottom: 1rem;
    }

    .attr-breadcrumb-link {
        color: var(--gray);
        text-decoration: none;
        transition: color 0.2s;
    }

    .attr-breadcrumb-link:hover {
        color: var(--primary);
    }

    .attr-breadcrumb-separator {
        font-size: 0.75rem;
    }

    .attr-breadcrumb-current {
        color: var(--dark);
        font-weight: 500;
    }

    .attr-list-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .attr-list-title i {
        color: var(--primary);
    }

    .attr-list-subtitle {
        color: var(--gray);
        margin: 0;
        font-size: 0.95rem;
    }

    .attr-btn-create {
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
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .attr-btn-create:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
        color: white;
    }

    /* Stats */
    .attr-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .attr-stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px var(--shadow);
        border: 2px solid transparent;
        transition: all 0.3s;
    }

    .attr-stat-card:hover {
        transform: translateY(-4px);
    }

    .attr-stat-primary {
        border-color: rgba(99, 102, 241, 0.1);
    }

    .attr-stat-primary:hover {
        border-color: var(--primary);
    }

    .attr-stat-success {
        border-color: rgba(16, 185, 129, 0.1);
    }

    .attr-stat-success:hover {
        border-color: var(--success);
    }

    .attr-stat-warning {
        border-color: rgba(245, 158, 11, 0.1);
    }

    .attr-stat-warning:hover {
        border-color: var(--warning);
    }

    .attr-stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .attr-stat-primary .attr-stat-icon {
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
    }

    .attr-stat-success .attr-stat-icon {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .attr-stat-warning .attr-stat-icon {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }

    .attr-stat-label {
        font-size: 0.8rem;
        color: var(--gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .attr-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
    }

    /* Filter */
    .attr-filter-section {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px var(--shadow);
    }

    .attr-filter-form {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .attr-search-box {
        position: relative;
        flex: 1;
        min-width: 250px;
    }

    .attr-search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    .attr-search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: var(--light-gray);
    }

    .attr-search-input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .attr-btn-filter,
    .attr-btn-reset {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        background: white;
        color: var(--gray);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .attr-btn-filter:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .attr-btn-reset:hover {
        border-color: var(--danger);
        color: var(--danger);
    }

    /* Table */
    .attr-table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px var(--shadow);
        overflow: hidden;
    }

    .attr-table {
        width: 100%;
        border-collapse: collapse;
    }

    .attr-table thead {
        background: linear-gradient(135deg, var(--light-gray), white);
        border-bottom: 2px solid var(--border);
    }

    .attr-table th {
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .attr-th {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .attr-th i {
        color: var(--primary);
    }

    .attr-th-actions {
        text-align: center;
    }

    .attr-row {
        border-bottom: 1px solid var(--border);
        transition: all 0.2s;
    }

    .attr-row:hover {
        background: var(--light-gray);
    }

    .attr-table td {
        padding: 1rem 1.5rem;
        color: var(--dark);
        font-size: 0.95rem;
        vertical-align: middle;
    }

    .attr-id-badge {
        display: inline-block;
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
        padding: 0.25rem 0.75rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .attr-name {
        font-weight: 600;
    }

    .attr-status {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.875rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .attr-status.active {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .attr-status.inactive {
        background: rgba(100, 116, 139, 0.1);
        color: var(--gray);
    }

    .attr-date {
        color: var(--gray);
        font-size: 0.9rem;
    }

    .attr-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .attr-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .attr-action-btn.view {
        background: rgba(59, 130, 246, 0.1);
        color: var(--info);
    }

    .attr-action-btn.view:hover {
        background: var(--info);
        color: white;
    }

    .attr-action-btn.edit {
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
    }

    .attr-action-btn.edit:hover {
        background: var(--primary);
        color: white;
    }

    .attr-action-btn.delete {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .attr-action-btn.delete:hover {
        background: var(--danger);
        color: white;
    }

    /* Empty State */
    .attr-empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .attr-empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        background: var(--light-gray);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--gray);
    }

    .attr-empty-state h3 {
        color: var(--dark);
        margin: 0 0 0.5rem;
    }

    .attr-empty-state p {
        color: var(--gray);
        margin: 0 0 1.5rem;
    }

    /* Pagination - Matching Diamond Page Style */
    .attr-pagination {
        padding: 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: center;
        align-items: center;
        background: white;
    }

    /* Bootstrap 5 Pagination Override */
    .attr-pagination nav {
        display: flex !important;
        justify-content: center;
        align-items: center;
        width: 100%;
    }

    .attr-pagination nav>div {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .attr-pagination nav>div.hidden {
        display: flex !important;
    }

    /* Pagination wrapper */
    .attr-pagination .pagination {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    /* Page items */
    .attr-pagination .page-item {
        list-style: none;
    }

    /* Page links (all buttons) */
    .attr-pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0.5rem 0.75rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--dark);
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .attr-pagination .page-link:hover {
        background: var(--light-gray);
        border-color: var(--primary);
        color: var(--primary);
    }

    /* Active page */
    .attr-pagination .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        font-weight: 600;
    }

    .attr-pagination .page-item.active .page-link:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
        color: white;
    }

    /* Disabled state (Previous on first page, Next on last page) */
    .attr-pagination .page-item.disabled .page-link {
        background: var(--light-gray);
        border-color: var(--border);
        color: var(--gray);
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Previous/Next buttons with text */
    .attr-pagination .page-item:first-child .page-link,
    .attr-pagination .page-item:last-child .page-link {
        padding: 0.5rem 1rem;
        min-width: auto;
    }

    /* Tailwind fallback pagination styles */
    .attr-pagination nav span[aria-current="page"] span,
    .attr-pagination nav a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0.5rem 0.75rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--dark);
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .attr-pagination nav a:hover {
        background: var(--light-gray);
        border-color: var(--primary);
        color: var(--primary);
    }

    .attr-pagination nav span[aria-current="page"] span {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        font-weight: 600;
    }

    .attr-pagination nav span[aria-disabled="true"] span {
        background: var(--light-gray);
        border-color: var(--border);
        color: var(--gray);
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Results text (Showing X to Y of Z results) */
    .attr-pagination p,
    .attr-pagination nav p {
        margin: 0;
        color: var(--gray);
        font-size: 0.875rem;
    }

    .attr-pagination p span,
    .attr-pagination nav p span {
        font-weight: 600;
        color: var(--primary);
    }

    /* SVG icons in pagination */
    .attr-pagination svg {
        width: 14px;
        height: 14px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .attr-list-container {
            padding: 1rem;
        }

        .attr-header-content {
            flex-direction: column;
        }

        .attr-stats-grid {
            grid-template-columns: 1fr;
        }

        .attr-filter-form {
            flex-direction: column;
        }

        .attr-search-box {
            min-width: 100%;
        }

        .attr-table {
            display: block;
            overflow-x: auto;
        }
    }
</style><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/partials/attribute-index-styles.blade.php ENDPATH**/ ?>