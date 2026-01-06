

<?php $__env->startSection('title', 'Meta Integration Settings'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .settings-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px var(--shadow);
            margin-bottom: 1.5rem;
        }

        .settings-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .settings-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .settings-card-title i {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .settings-card-title i.facebook {
            background: rgba(24, 119, 242, 0.1);
            color: #1877F2;
        }

        .settings-card-title i.meta {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.1));
            color: var(--primary);
        }

        /* Setup Steps */
        .setup-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .setup-step {
            padding: 1.25rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            background: var(--light-gray);
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }

        .step-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .step-desc {
            font-size: 0.875rem;
            color: var(--gray);
            line-height: 1.5;
        }

        /* Webhook Info */
        .webhook-info {
            background: var(--light-gray);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .webhook-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .webhook-value {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 2px solid var(--border);
            font-family: monospace;
            font-size: 0.9rem;
            word-break: break-all;
        }

        .webhook-value .copy-btn {
            flex-shrink: 0;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            border: none;
            background: var(--primary);
            color: white;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s;
        }

        .webhook-value .copy-btn:hover {
            background: var(--primary-dark);
        }

        /* Accounts Table */
        .accounts-table {
            width: 100%;
            border-collapse: collapse;
        }

        .accounts-table th,
        .accounts-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .accounts-table th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--gray);
            background: var(--light-gray);
        }

        .accounts-table th:first-child {
            border-radius: 8px 0 0 8px;
        }

        .accounts-table th:last-child {
            border-radius: 0 8px 8px 0;
        }

        .platform-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .platform-badge.facebook {
            background: rgba(24, 119, 242, 0.1);
            color: #1877F2;
        }

        .platform-badge.instagram {
            background: linear-gradient(135deg, rgba(245, 133, 41, 0.1), rgba(221, 42, 123, 0.1));
            color: #E4405F;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-badge.inactive {
            background: rgba(100, 116, 139, 0.1);
            color: var(--gray);
        }

        .token-status {
            font-size: 0.85rem;
        }

        .token-status.valid {
            color: var(--success);
        }

        .token-status.expiring {
            color: var(--warning);
        }

        .token-status.expired {
            color: var(--danger);
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }

        .form-grid .full-width {
            grid-column: 1 / -1;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .form-group label small {
            font-weight: 400;
            color: var(--gray);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .help-text {
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        /* Empty State */
        .empty-accounts {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-accounts i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        /* Alert */
        .config-alert {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .config-alert.warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .config-alert.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .config-alert i {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .config-alert.warning i {
            color: var(--warning);
        }

        .config-alert.success i {
            color: var(--success);
        }

        .config-alert-content h6 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark);
        }

        .config-alert-content p {
            font-size: 0.875rem;
            color: var(--gray);
            margin: 0;
        }

        .config-alert-content code {
            background: rgba(0, 0, 0, 0.05);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .setup-steps {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="settings-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="breadcrumb-nav">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <a href="<?php echo e(route('leads.index')); ?>" class="breadcrumb-link">Leads</a>
                        <i class="bi bi-chevron-right breadcrumb-separator"></i>
                        <span class="breadcrumb-current">Meta Settings</span>
                    </div>
                    <h1 class="page-title">
                        <i class="bi bi-gear"></i>
                        Meta Integration Settings
                    </h1>
                    <p class="page-subtitle">Connect your Facebook & Instagram accounts to receive messages</p>
                </div>
            </div>
        </div>

        <!-- Config Status Alert -->
        <?php if(!$isConfigured): ?>
            <div class="config-alert warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div class="config-alert-content">
                    <h6>Meta API Not Configured</h6>
                    <p>Add your Meta App credentials to your <code>.env</code> file:</p>
                    <p style="margin-top: 0.5rem;">
                        <code>META_APP_ID=your_app_id</code><br>
                        <code>META_APP_SECRET=your_app_secret</code><br>
                        <code>META_WEBHOOK_VERIFY_TOKEN=your_custom_token</code>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="config-alert success">
                <i class="bi bi-check-circle-fill"></i>
                <div class="config-alert-content">
                    <h6>Meta API Configured</h6>
                    <p>Your Meta App credentials are set. You can now connect Facebook & Instagram accounts.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Setup Steps -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h2 class="settings-card-title">
                    <i class="bi bi-list-ol meta"></i>
                    Setup Guide
                </h2>
            </div>

            <div class="setup-steps">
                <div class="setup-step">
                    <div class="step-number">1</div>
                    <div class="step-title">Create Meta App</div>
                    <div class="step-desc">
                        Go to <a href="https://developers.facebook.com" target="_blank">developers.facebook.com</a>,
                        create a Business app, and add Messenger & Instagram Messaging products.
                    </div>
                </div>
                <div class="setup-step">
                    <div class="step-number">2</div>
                    <div class="step-title">Configure Webhook</div>
                    <div class="step-desc">
                        In your Meta App settings, add the webhook URL and verify token shown below.
                        Subscribe to <code>messages</code> events.
                    </div>
                </div>
                <div class="setup-step">
                    <div class="step-number">3</div>
                    <div class="step-title">Get Page Access Token</div>
                    <div class="step-desc">
                        Use the <a href="https://developers.facebook.com/tools/explorer/" target="_blank">Graph API
                            Explorer</a>
                        to generate a Page Access Token with messaging permissions.
                    </div>
                </div>
                <div class="setup-step">
                    <div class="step-number">4</div>
                    <div class="step-title">Connect Account</div>
                    <div class="step-desc">
                        Add your Page ID and Access Token below to start receiving messages from that page.
                    </div>
                </div>
            </div>

            <!-- Webhook Info -->
            <h6 style="font-weight: 600; margin-bottom: 1rem;">Your Webhook Configuration</h6>
            <div class="webhook-info">
                <div style="margin-bottom: 1rem;">
                    <div class="webhook-label">Callback URL</div>
                    <div class="webhook-value">
                        <span id="webhookUrl"><?php echo e($webhookUrl); ?></span>
                        <button type="button" class="copy-btn" onclick="copyToClipboard('webhookUrl')">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>
                <div>
                    <div class="webhook-label">Verify Token</div>
                    <div class="webhook-value">
                        <span
                            id="verifyToken"><?php echo e($verifyToken ?: 'Not configured - add META_WEBHOOK_VERIFY_TOKEN to .env'); ?></span>
                        <?php if($verifyToken): ?>
                            <button type="button" class="copy-btn" onclick="copyToClipboard('verifyToken')">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <button type="button" class="btn-secondary-custom" onclick="testWebhook()">
                <i class="bi bi-wifi"></i> Test Webhook
            </button>
        </div>

        <!-- Connected Accounts -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h2 class="settings-card-title">
                    <i class="bi bi-facebook facebook"></i>
                    Connected Accounts
                </h2>
                <button type="button" class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                    <i class="bi bi-plus-circle"></i> Add Account
                </button>
            </div>

            <?php if($accounts->count() > 0): ?>
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Platform</th>
                            <th>Page ID</th>
                            <th>Token Status</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($account->name); ?></strong>
                                </td>
                                <td>
                                    <span class="platform-badge <?php echo e($account->platform); ?>">
                                        <i class="bi bi-<?php echo e($account->platform); ?>"></i>
                                        <?php echo e(ucfirst($account->platform)); ?>

                                    </span>
                                </td>
                                <td>
                                    <code><?php echo e($account->page_id); ?></code>
                                </td>
                                <td>
                                    <?php if($account->isTokenExpired()): ?>
                                        <span class="token-status expired">
                                            <i class="bi bi-x-circle"></i> Expired
                                        </span>
                                    <?php elseif($account->isTokenExpiringSoon()): ?>
                                        <span class="token-status expiring">
                                            <i class="bi bi-exclamation-triangle"></i> Expires
                                            <?php echo e($account->token_expires_at->diffForHumans()); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="token-status valid">
                                            <i class="bi bi-check-circle"></i> Valid
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo e($account->is_active ? 'active' : 'inactive'); ?>">
                                        <i class="bi bi-<?php echo e($account->is_active ? 'check' : 'pause'); ?>"></i>
                                        <?php echo e($account->is_active ? 'Active' : 'Paused'); ?>

                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <form action="<?php echo e(route('settings.meta.refresh', $account)); ?>" method="POST"
                                            style="display: inline;">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn-secondary-custom" style="padding: 0.4rem 0.6rem;"
                                                title="Refresh Token">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn-secondary-custom" style="padding: 0.4rem 0.6rem;"
                                            onclick="toggleAccount(<?php echo e($account->id); ?>)" title="Toggle Status">
                                            <i class="bi bi-<?php echo e($account->is_active ? 'pause' : 'play'); ?>"></i>
                                        </button>
                                        <form action="<?php echo e(route('settings.meta.destroy', $account)); ?>" method="POST"
                                            style="display: inline;" onsubmit="return confirm('Disconnect this account?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn-secondary-custom"
                                                style="padding: 0.4rem 0.6rem; color: var(--danger);" title="Disconnect">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-accounts">
                    <i class="bi bi-plug"></i>
                    <p>No accounts connected yet</p>
                    <p style="font-size: 0.85rem;">Click "Add Account" to connect your Facebook or Instagram page</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Account Modal -->
    <div class="modal fade" id="addAccountModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border); padding: 1.25rem 1.5rem;">
                    <h5 class="modal-title" style="font-weight: 700;">
                        <i class="bi bi-plus-circle" style="color: var(--primary);"></i>
                        Add Meta Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('settings.meta.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body" style="padding: 1.5rem;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Account Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="e.g., My Jewelry Store" required>
                                <p class="help-text">A friendly name to identify this account</p>
                            </div>

                            <div class="form-group">
                                <label for="platform">Platform</label>
                                <select class="form-control" id="platform" name="platform" required>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="page_id">Page ID</label>
                                <input type="text" class="form-control" id="page_id" name="page_id"
                                    placeholder="e.g., 123456789012345" required>
                                <p class="help-text">Find this in your Facebook Page settings → About → Page ID</p>
                            </div>

                            <div class="form-group">
                                <label for="access_token">
                                    Page Access Token
                                    <small>(this will be encrypted)</small>
                                </label>
                                <input type="password" class="form-control" id="access_token" name="access_token"
                                    placeholder="EAAG..." required>
                                <p class="help-text">Generate using Graph API Explorer with pages_messaging permission</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--border); padding: 1rem 1.5rem;">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-primary-custom">
                            <i class="bi bi-plug"></i> Connect Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(() => {
                // Show feedback
                const btn = event.target.closest('.copy-btn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                }, 2000);
            });
        }

        async function testWebhook() {
            try {
                const response = await fetch('<?php echo e(route("settings.meta.test-webhook")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await response.json();

                if (data.success) {
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ ' + data.message);
                }
            } catch (error) {
                alert('❌ Failed to test webhook: ' + error.message);
            }
        }

        async function toggleAccount(accountId) {
            try {
                const response = await fetch(`/admin/settings/meta/${accountId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                alert('Failed to toggle account status');
            }
        }
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\CRM-Minimal-Carbon\CRM-Minimal-Carbon\resources\views/settings/meta.blade.php ENDPATH**/ ?>