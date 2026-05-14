<div class="workspace-details-container animate__animated animate__fadeIn">
    <!-- Workspace Header -->
    <div class="workspace-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="order-branding">
                <div class="brand-badge">ORD</div>
                <div>
                    <h2 class="workspace-title">Order #{{ $investigation->order->id }}</h2>
                    <div class="workspace-meta">
                        <span class="meta-item"><i class="bi bi-person"></i>
                            {{ $investigation->order->client_name }}</span>
                        <span class="meta-separator"></span>
                        <span class="meta-item"><i class="bi bi-hash"></i>
                            {{ $investigation->order->tracking_number }}</span>
                    </div>
                </div>
            </div>
            <div class="action-zone">
                <div class="status-switcher">
                    <button
                        class="status-current-btn tag-{{ str_replace(' ', '-', strtolower($investigation->investigation_status)) }}"
                        type="button" data-bs-toggle="dropdown">
                        {{ $investigation->investigation_status }} <i class="bi bi-chevron-down ms-2"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end premium-dropdown">
                        <li class="dropdown-header">Update Status</li>
                        @foreach(['Pending', 'In Progress', 'Carrier Contacted', 'Resolved', 'Delivered'] as $status)
                            <li>
                                <form action="{{ route('investigations.update-status', $investigation->id) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <button type="submit"
                                        class="dropdown-item d-flex align-items-center gap-2 {{ $investigation->investigation_status == $status ? 'active' : '' }}">
                                        <span
                                            class="status-dot-sm dot-{{ str_replace(' ', '-', strtolower($status)) }}"></span>
                                        {{ $status }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="started-info">Started by {{ $investigation->creator->name ?? 'System' }}</div>
            </div>
        </div>
    </div>

    <div class="workspace-grid mt-4">
        <!-- Left: Information Bento -->
        <div class="grid-left">
            <div class="bento-card info-card">
                <div class="bento-card-header">
                    <h6><i class="bi bi-info-circle"></i> Order Details</h6>
                </div>
                <div class="bento-card-body">
                    <div class="info-row">
                        <div class="info-label">Dispatch Date</div>
                        <div class="info-value">
                            {{ $investigation->order->dispatch_date ? \Carbon\Carbon::parse($investigation->order->dispatch_date)->format('d M, Y') : 'N/A' }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Courier Status</div>
                        <div class="info-value">
                            <span
                                class="badge-soft-primary">{{ $investigation->order->tracking_status ?? 'Unknown' }}</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Total Weight</div>
                        <div class="info-value">{{ $investigation->order->gold_weight ?? '0.00' }}g</div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('orders.show', $investigation->order->id) }}" target="_blank"
                            class="btn-workspace-action">
                            View Order <i class="bi bi-box-arrow-up-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="bento-card history-card mt-3">
                <div class="bento-card-header">
                    <h6><i class="bi bi-truck"></i> Tracking History</h6>
                </div>
                <div class="bento-card-body p-0 d-flex flex-column">
                    <div class="mini-history-list scroll-y-custom">
                        @if($investigation->order->tracking_history && count($investigation->order->tracking_history) > 0)
                            @foreach(array_reverse($investigation->order->tracking_history) as $event)
                                <div class="mini-history-item">
                                    <div class="history-date">{{ \Carbon\Carbon::parse($event['date'])->format('d M') }}</div>
                                    <div class="history-content">
                                        <div class="history-status">{{ $event['status'] }}</div>
                                        <div class="history-location">{{ $event['location'] ?? '' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="p-4 text-center text-muted small italic">No tracking events.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Log & Composer -->
        <div class="grid-right">
            <div class="bento-card timeline-card">
                <div class="bento-card-header d-flex justify-content-between align-items-center">
                    <h6><i class="bi bi-chat-left-dots"></i> Admin Timeline</h6>
                </div>
                <div class="bento-card-body d-flex flex-column">
                    <div class="timeline-container scroll-y-custom">
                        @if($investigation->investigation_notes && count($investigation->investigation_notes) > 0)
                            <div class="modern-timeline">
                                @foreach(array_reverse($investigation->investigation_notes) as $note)
                                    <div class="timeline-entry">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="note-header">
                                                <span class="note-author">{{ $note['admin'] }}</span>
                                                <span
                                                    class="note-time">{{ \Carbon\Carbon::parse($note['time'])->diffForHumans() }}</span>
                                            </div>
                                            <div class="note-body">
                                                {{ $note['text'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-timeline-state">
                                <i class="bi bi-chat-quote"></i>
                                <p>No log entries yet.</p>
                            </div>
                        @endif
                    </div>

                    <div class="timeline-composer mt-auto border-top pt-3">
                        <form action="{{ route('investigations.add-note', $investigation->id) }}" method="POST">
                            @csrf
                            <div class="composer-box">
                                <textarea name="note" class="composer-input" rows="2"
                                    placeholder="Add an internal note or update..." required
                                    onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); this.closest('form').submit(); }"></textarea>
                                <div class="composer-actions">
                                    <button type="submit" class="composer-submit">
                                        Update <i class="bi bi-send ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .workspace-details-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 1.5rem 2rem;
        background: white;
        overflow: hidden;
        min-height: 0;
    }

    /* Header Styling */
    .brand-badge {
        width: 38px;
        height: 38px;
        background: var(--inv-primary);
        color: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 0.7rem;
        flex-shrink: 0;
    }

    .order-branding {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .workspace-title {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0;
        color: var(--inv-text-main);
        letter-spacing: -0.5px;
    }

    .workspace-meta {
        font-size: 0.8rem;
        color: var(--inv-text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .meta-item i {
        margin-right: 4px;
        color: var(--inv-primary);
    }

    .meta-separator {
        width: 3px;
        height: 3px;
        background: var(--inv-border);
        border-radius: 50%;
    }

    /* Action Zone */
    .action-zone {
        text-align: right;
    }

    .started-info {
        font-size: 0.7rem;
        color: var(--inv-text-muted);
        margin-top: 4px;
        font-weight: 500;
    }

    /* Status Switcher */
    .status-current-btn {
        border: 1px solid transparent;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s;
    }

    .premium-dropdown {
        border: 1px solid var(--inv-border);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 0.5rem;
        min-width: 200px;
    }

    .premium-dropdown .dropdown-header {
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 1px;
        color: var(--inv-text-muted);
        padding: 0.5rem 0.75rem;
    }

    .premium-dropdown .dropdown-item {
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .status-dot-sm {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .dot-pending {
        background: var(--inv-tag-pending-text);
    }

    .dot-in-progress {
        background: var(--inv-tag-active-text);
    }

    .dot-carrier-contacted {
        background: var(--inv-tag-active-text);
    }

    .dot-resolved {
        background: var(--inv-tag-resolved-text);
    }

    /* Bento Grid */
    .workspace-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 1rem;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    @media (max-width: 992px) {
        .workspace-grid {
            grid-template-columns: 1fr;
            overflow-y: auto;
            gap: 1.5rem;
        }

        .grid-left,
        .grid-right {
            min-height: auto;
        }

        .mini-history-list {
            height: auto;
            max-height: 300px;
        }
    }

    .grid-left,
    .grid-right {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .bento-card {
        background: white;
        border: 1px solid var(--inv-border);
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: border-color 0.2s;
        min-height: 0;
    }

    .timeline-card,
    .history-card {
        flex: 1;
    }

    .bento-card:hover {
        border-color: #cbd5e1;
    }

    .bento-card-header {
        padding: 1rem 1.25rem 0.5rem;
    }

    .bento-card-header h6 {
        font-weight: 800;
        font-size: 0.85rem;
        color: var(--inv-text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .bento-card-body {
        padding: 0.5rem 1.25rem 1.25rem;
        flex: 1;
        min-height: 0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f8fafc;
    }

    .info-label {
        font-size: 0.8rem;
        color: var(--inv-text-muted);
        font-weight: 500;
    }

    .info-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--inv-text-main);
    }

    .btn-workspace-action {
        display: flex;
        width: 100%;
        padding: 0.65rem;
        background: var(--inv-primary);
        border: none;
        border-radius: 10px;
        color: #fff;
        font-weight: 800;
        font-size: 0.8rem;
        justify-content: center;
        align-items: center;
        transition: all 0.2s;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-workspace-action:hover {
        background: #4f46e5;
        box-shadow: 0 4px 10px rgba(99, 102, 241, 0.2);
        color: #fff;
    }

    /* History Mini List */
    .mini-history-list {
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        flex: 1;
    }

    .mini-history-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #f8fafc;
    }

    .history-date {
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--inv-text-muted);
        text-transform: uppercase;
        width: 35px;
        flex-shrink: 0;
    }

    .history-status {
        font-size: 0.8rem;
        font-weight: 800;
        color: var(--inv-text-main);
        line-height: 1.2;
    }

    .history-location {
        font-size: 0.7rem;
        color: var(--inv-text-muted);
        font-weight: 500;
    }

    /* Modern Timeline */
    .timeline-container {
        flex: 1;
        overflow-y: auto;
        padding: 0.5rem 1.25rem;
    }

    .modern-timeline {
        padding: 0.5rem 0;
    }

    .timeline-entry {
        position: relative;
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .timeline-marker {
        position: absolute;
        left: 0;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid var(--inv-primary);
        z-index: 2;
    }

    .timeline-entry::before {
        content: '';
        position: absolute;
        left: 4px;
        top: 20px;
        bottom: -30px;
        width: 2px;
        background: #f1f5f9;
        z-index: 1;
    }

    .timeline-entry:last-child::before {
        display: none;
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.4rem;
    }

    .note-author {
        font-weight: 800;
        font-size: 0.85rem;
        color: var(--inv-text-main);
    }

    .note-time {
        font-size: 0.7rem;
        color: var(--inv-text-muted);
        font-weight: 500;
    }

    .note-body {
        background: #fdfdfe;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        font-size: 0.9rem;
        color: #334155;
        line-height: 1.6;
        border: 1px solid #f1f5f9;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    /* Composer */
    .composer-box {
        background: #f8fafc;
        border: 1px solid var(--inv-border);
        border-radius: 12px;
        padding: 0.4rem;
        transition: all 0.2s;
    }

    .composer-box:focus-within {
        border-color: var(--inv-primary);
        background: #fff;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.08);
    }

    .composer-input {
        width: 100%;
        border: none;
        background: transparent;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        resize: none;
        outline: none;
        font-weight: 500;
        color: var(--inv-text-main);
    }

    .composer-actions {
        display: flex;
        justify-content: flex-end;
        padding: 0.25rem 0.5rem;
    }

    .composer-submit {
        background: var(--inv-text-main);
        color: #fff;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 800;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .composer-submit:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }

    .badge-soft-primary {
        background: var(--inv-primary-light);
        color: var(--inv-primary);
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .empty-timeline-state {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        padding: 2rem 0;
    }

    .empty-timeline-state i {
        font-size: 2rem;
        opacity: 0.2;
        margin-bottom: 0.75rem;
    }
</style>