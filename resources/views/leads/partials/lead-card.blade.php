{{-- Lead Card Partial for Kanban Board --}}
<div class="lead-card" draggable="true" data-lead-id="{{ $lead->id }}" data-status="{{ $lead->status }}">

    <div class="lead-card-header">
        <div class="lead-platform-badge {{ $lead->platform }}">
            <i class="bi {{ $lead->platform_icon }}"></i>
        </div>
        <div style="flex: 1; margin-left: 0.75rem;">
            <div class="lead-name">{{ $lead->name }}</div>
            @if($lead->username)
                <div class="lead-username">@{{ $lead->username }}</div>
            @endif
        </div>
        @if($lead->unread_messages_count > 0)
            <span class="lead-unread">{{ $lead->unread_messages_count }}</span>
        @endif
    </div>

    <div class="lead-card-meta">
        <span class="lead-score">
            <i class="bi bi-star-fill"></i>
            {{ $lead->lead_score }}
        </span>
        <span class="lead-priority {{ $lead->priority }}">
            {{ ucfirst($lead->priority) }}
        </span>
        @if($lead->isSlAOverdue())
            <span class="sla-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Overdue
            </span>
        @endif
        <span class="lead-time">
            {{ $lead->last_contact_at?->diffForHumans() ?? $lead->created_at->diffForHumans() }}
        </span>
    </div>

    <div class="lead-card-footer">
        @if($lead->assignedAdmin)
            <div class="lead-assigned">
                <span class="lead-avatar">
                    {{ substr($lead->assignedAdmin->name, 0, 2) }}
                </span>
                {{ $lead->assignedAdmin->name }}
            </div>
        @else
            <div class="lead-assigned">
                <i class="bi bi-person-dash"></i>
                Unassigned
            </div>
        @endif
    </div>
</div>