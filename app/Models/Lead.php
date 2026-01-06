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
        $score = 0;

        // Base points for having contact information
        if ($this->email)
            $score += 15;
        if ($this->phone)
            $score += 20;

        // Points for engagement (message count)
        $messageCount = $this->messages()->count();
        $score += min($messageCount * 5, 30); // Max 30 points for messages

        // Points for recent activity
        if ($this->last_contact_at) {
            $hoursSinceContact = now()->diffInHours($this->last_contact_at);
            if ($hoursSinceContact < 24)
                $score += 20;
            elseif ($hoursSinceContact < 72)
                $score += 10;
            elseif ($hoursSinceContact < 168)
                $score += 5;
        }

        // Bonus for high priority
        if ($this->priority === 'high')
            $score += 15;
        elseif ($this->priority === 'medium')
            $score += 5;

        return min($score, 100); // Cap at 100
    }

    public function updateScore(): void
    {
        $this->update(['lead_score' => $this->calculateScore()]);
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
}
