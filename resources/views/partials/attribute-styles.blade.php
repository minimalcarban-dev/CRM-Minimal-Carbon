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
    }

    .attr-form-container {
        padding: 2rem;
        max-width: 900px;
        margin: 0 auto;
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Page Header */
    .attr-page-header {
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
        display: flex;
        align-items: center;
        gap: 0.25rem;
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

    .attr-page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .attr-page-title i {
        color: var(--primary);
    }

    .attr-page-subtitle {
        color: var(--gray);
        margin: 0;
        font-size: 0.95rem;
    }

    .attr-btn-back {
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
    }

    .attr-btn-back:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
        transform: translateY(-2px);
    }

    /* Form Card */
    .attr-form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px var(--shadow);
        overflow: hidden;
        animation: attrFadeIn 0.4s ease forwards;
    }

    .attr-form-card-header {
        background: linear-gradient(135deg, var(--light-gray), white);
        border-bottom: 2px solid var(--border);
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .attr-form-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .attr-form-card-title h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.25rem 0;
    }

    .attr-form-card-title p {
        color: var(--gray);
        margin: 0;
        font-size: 0.9rem;
    }

    .attr-form-card-body {
        padding: 2rem;
    }

    /* Form Elements */
    .attr-form-group {
        margin-bottom: 1.5rem;
    }

    .attr-form-group:last-child {
        margin-bottom: 0;
    }

    .attr-form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }

    .attr-form-label i {
        color: var(--primary);
        font-size: 1rem;
    }

    .attr-required {
        color: var(--danger);
        font-weight: 700;
    }

    .attr-form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: white;
        color: var(--dark);
    }

    .attr-form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .attr-form-input.is-invalid {
        border-color: var(--danger);
    }

    .attr-error-message {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        color: var(--danger);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }

    /* Status Toggle */
    .attr-status-toggle-group {
        display: flex;
        gap: 1rem;
    }

    .attr-status-toggle {
        flex: 1;
        cursor: pointer;
    }

    .attr-status-toggle input {
        display: none;
    }

    .attr-toggle-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        background: white;
        color: var(--gray);
        font-weight: 600;
        transition: all 0.2s;
    }

    .attr-toggle-indicator i {
        font-size: 1.25rem;
    }

    .attr-status-toggle input:checked+.attr-toggle-indicator.active {
        border-color: var(--success);
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    .attr-status-toggle input:checked+.attr-toggle-indicator.inactive {
        border-color: var(--gray);
        background: rgba(100, 116, 139, 0.1);
        color: var(--gray);
        box-shadow: 0 0 0 4px rgba(100, 116, 139, 0.1);
    }

    .attr-toggle-indicator:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    /* Form Actions */
    .attr-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid var(--border);
    }

    .attr-btn-cancel,
    .attr-btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.75rem;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.2s;
        border: 2px solid;
        cursor: pointer;
    }

    .attr-btn-cancel {
        background: white;
        color: var(--gray);
        border-color: var(--border);
    }

    .attr-btn-cancel:hover {
        border-color: var(--danger);
        color: var(--danger);
        background: rgba(239, 68, 68, 0.05);
        transform: translateY(-2px);
    }

    .attr-btn-submit {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .attr-btn-submit:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
    }

    /* Show Page Styles */
    .attr-detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px var(--shadow);
        overflow: hidden;
        animation: attrFadeIn 0.4s ease forwards;
    }

    .attr-detail-header {
        background: linear-gradient(135deg, var(--light-gray), white);
        border-bottom: 2px solid var(--border);
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .attr-detail-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .attr-detail-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .attr-detail-title h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.25rem 0;
    }

    .attr-detail-title p {
        color: var(--gray);
        margin: 0;
        font-size: 0.9rem;
    }

    .attr-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .attr-status-badge.active {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .attr-status-badge.inactive {
        background: rgba(100, 116, 139, 0.1);
        color: var(--gray);
    }

    .attr-detail-body {
        padding: 2rem;
    }

    .attr-detail-row {
        display: flex;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border);
    }

    .attr-detail-row:last-child {
        border-bottom: none;
    }

    .attr-detail-label {
        width: 140px;
        font-weight: 600;
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .attr-detail-value {
        flex: 1;
        color: var(--dark);
        font-weight: 500;
    }

    .attr-detail-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid var(--border);
    }

    .attr-btn-edit {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: var(--primary);
        color: white;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .attr-btn-edit:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        color: white;
    }

    .attr-btn-delete {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: var(--danger);
        border: 2px solid var(--danger);
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .attr-btn-delete:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
    }

    /* Animation */
    @keyframes attrFadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .attr-form-container {
            padding: 1rem;
        }

        .attr-page-header {
            padding: 1.5rem;
        }

        .attr-header-content {
            flex-direction: column;
        }

        .attr-btn-back {
            width: 100%;
            justify-content: center;
        }

        .attr-form-card-header {
            flex-direction: column;
            text-align: center;
        }

        .attr-form-card-body {
            padding: 1.5rem;
        }

        .attr-status-toggle-group {
            flex-direction: column;
        }

        .attr-form-actions {
            flex-direction: column-reverse;
        }

        .attr-btn-cancel,
        .attr-btn-submit {
            width: 100%;
            justify-content: center;
        }

        .attr-detail-header {
            flex-direction: column;
            text-align: center;
        }

        .attr-detail-row {
            flex-direction: column;
            gap: 0.5rem;
        }

        .attr-detail-label {
            width: 100%;
        }

        .attr-detail-actions {
            flex-direction: column;
        }

        .attr-btn-edit,
        .attr-btn-delete {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Status toggle interaction
        const statusToggles = document.querySelectorAll('.attr-status-toggle');
        statusToggles.forEach(toggle => {
            toggle.addEventListener('click', function () {
                const input = this.querySelector('input');
                if (input.type === 'radio') {
                    statusToggles.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });

        // Form submit loading state
        const form = document.querySelector('.attr-form-card form');
        if (form) {
            form.addEventListener('submit', function () {
                const submitBtn = form.querySelector('.attr-btn-submit');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Processing...</span>';
                }
            });
        }
    });
</script>