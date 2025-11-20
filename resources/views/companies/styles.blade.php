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

    .form-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Page Header */
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
        display: flex;
        align-items: center;
        gap: 0.25rem;
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
        transform: translateY(-2px);
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px var(--shadow);
        overflow: hidden;
    }

    .form-card-header {
        background: linear-gradient(135deg, var(--light-gray), white);
        border-bottom: 2px solid var(--border);
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .form-card-icon {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
    }

    .form-card-title h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0 0 0.25rem 0;
    }

    .form-card-title p {
        color: var(--gray);
        margin: 0;
        font-size: 0.95rem;
    }

    .form-card-body {
        padding: 2rem;
    }

    /* Alert */
    .alert-danger-custom {
        background: rgba(239, 68, 68, 0.05);
        border: 2px solid rgba(239, 68, 68, 0.2);
        border-left: 4px solid var(--danger);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
    }

    .alert-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--danger);
        margin: 0 0 0.5rem 0;
    }

    .alert-message {
        color: var(--gray);
        margin: 0 0 0.75rem 0;
        font-size: 0.95rem;
    }

    .alert-list {
        margin: 0;
        padding-left: 1.25rem;
        color: var(--dark);
    }

    .alert-list li {
        margin-bottom: 0.375rem;
        font-size: 0.95rem;
    }

    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 2rem;
    }

    .form-section {
        background: var(--light-gray);
        border-radius: 12px;
        padding: 1.5rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border);
    }

    .section-header i {
        color: var(--primary);
        font-size: 1.25rem;
    }

    .section-header h3 {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    /* Form Group */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }

    .form-label i {
        color: var(--primary);
        font-size: 1rem;
    }

    .required {
        color: var(--danger);
        font-weight: 700;
        font-size: 1rem;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
        background: white;
        color: var(--dark);
        font-family: inherit;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .form-input.error,
    .form-textarea.error {
        border-color: var(--danger);
    }

    .form-input.error:focus,
    .form-textarea.error:focus {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }

    .error-message {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        color: var(--danger);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }

    .error-message i {
        font-size: 0.875rem;
    }

    /* Status Toggle */
    .status-toggle-group {
        display: flex;
        gap: 1rem;
    }

    .status-toggle {
        flex: 1;
        cursor: pointer;
    }

    .status-toggle input {
        display: none;
    }

    .toggle-indicator {
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
        text-align: center;
    }

    .toggle-indicator i {
        font-size: 1.25rem;
    }

    .status-toggle input:checked+.toggle-indicator.active {
        border-color: var(--success);
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }

    .status-toggle input:checked+.toggle-indicator.inactive {
        border-color: var(--gray);
        background: rgba(100, 116, 139, 0.1);
        color: var(--gray);
        box-shadow: 0 0 0 4px rgba(100, 116, 139, 0.1);
    }

    .toggle-indicator:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid var(--border);
    }

    .btn-cancel,
    .btn-submit {
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

    .btn-cancel {
        background: white;
        color: var(--gray);
        border-color: var(--border);
    }

    .btn-cancel:hover {
        border-color: var(--danger);
        color: var(--danger);
        background: rgba(239, 68, 68, 0.05);
        transform: translateY(-2px);
    }

    .btn-submit {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .btn-submit:hover {
        background: var(--primary-dark);
        border-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 1rem;
        }

        .page-header {
            padding: 1.5rem;
        }

        .header-content {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-secondary-custom {
            width: 100%;
            justify-content: center;
        }

        .form-card-header {
            flex-direction: column;
            text-align: center;
        }

        .form-card-body {
            padding: 1.5rem;
        }

        .form-grid {
            gap: 1.5rem;
        }

        .status-toggle-group {
            flex-direction: column;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-cancel,
        .btn-submit {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .page-title {
            font-size: 1.5rem;
        }

        .form-card-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .form-card-title h2 {
            font-size: 1.25rem;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-card {
        animation: fadeIn 0.4s ease forwards;
    }

    .form-section {
        animation: fadeIn 0.4s ease forwards;
    }

    .form-section:nth-child(1) {
        animation-delay: 0.1s;
    }

    .form-section:nth-child(2) {
        animation-delay: 0.2s;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Form validation feedback
        const form = document.getElementById('companyForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                const submitBtn = form.querySelector('.btn-submit');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i><span>Processing...</span>';
                }
            });
        }

        // Status toggle interaction
        const statusToggles = document.querySelectorAll('.status-toggle');
        statusToggles.forEach(toggle => {
            toggle.addEventListener('click', function () {
                statusToggles.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>