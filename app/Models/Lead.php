<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'platform',
        'platform_user_id',
        'username',
        'profile_pic_url',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'first_contact_at',
        'last_contact_at',
        'sla_deadline',
        'lead_score',
        'tags',
        'notes',
    ];

    protected $casts = [
        'tags' => 'array',
        'first_contact_at' => 'datetime',
        'last_contact_at' => 'datetime',
        'sla_deadline' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(MetaConversation::class);
    }

    public function messages(): HasManyThrough
    {
        return $this->hasManyThrough(
            MetaMessage::class,
            MetaConversation::class,
            'lead_id',
            'meta_conversation_id'
        )->latest();
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, int $adminId)
    {
        return $query->where('assigned_to', $adminId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeOverdueSla($query)
    {
        return $query->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', now())
            ->whereNotIn('status', ['completed', 'lost']);
    }

    public function scopeHighScore($query, int $minScore = 70)
    {
        return $query->where('lead_score', '>=', $minScore);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeInProcess($query)
    {
        return $query->where('status', 'in_process');
    }

    // ─────────────────────────────────────────────────────────────
    // Lead Scoring
    // ─────────────────────────────────────────────────────────────

    public function calculateScore(): int
    {
        return app(\App\Services\LeadScoringService::class)->calculateScore($this);
    }

    public function updateScore(): void
    {
        app(\App\Services\LeadScoringService::class)->updateScore($this);
    }

    // ─────────────────────────────────────────────────────────────
    // SLA Management
    // ─────────────────────────────────────────────────────────────

    public function setSlaDeadline(int $hours = 24): void
    {
        $this->update(['sla_deadline' => now()->addHours($hours)]);
    }

    public function isSlAOverdue(): bool
    {
        return $this->sla_deadline && $this->sla_deadline->isPast();
    }

    public function getSlATimeRemaining(): ?string
    {
        if (!$this->sla_deadline)
            return null;

        if ($this->sla_deadline->isPast()) {
            return 'Overdue by ' . $this->sla_deadline->diffForHumans();
        }

        return $this->sla_deadline->diffForHumans();
    }

    // ─────────────────────────────────────────────────────────────
    // Activity Logging
    // ─────────────────────────────────────────────────────────────

    public function logActivity(string $type, string $description, ?int $adminId = null, array $metadata = []): LeadActivity
    {
        return $this->activities()->create([
            'type' => $type,
            'description' => $description,
            'admin_id' => $adminId ?? auth('admin')->id(),
            'metadata' => $metadata,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────

    public function getPlatformIconAttribute(): string
    {
        return match ($this->platform) {
            'instagram' => 'bi-instagram',
            'facebook' => 'bi-facebook',
            default => 'bi-chat-dots'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'primary',
            'in_process' => 'info',
            'completed' => 'success',
            'lost' => 'secondary',
            default => 'secondary'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'success',
            default => 'secondary'
        };
    }

    public function getUnreadMessagesCountAttribute(): int
    {
        return $this->conversations()
            ->where('is_read', false)
            ->count();
    }

    public function getHeatLevelAttribute(): string
    {
        return app(\App\Services\LeadScoringService::class)->getHeatLevel($this);
    }

    public function getHeatIconAttribute(): string
    {
        return app(\App\Services\LeadScoringService::class)->getHeatIcon($this);
    }
}
