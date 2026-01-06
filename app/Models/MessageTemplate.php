<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'content',
        'variables',
        'created_by',
        'usage_count',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    // Category constants
    const CATEGORY_GREETING = 'greeting';
    const CATEGORY_FOLLOW_UP = 'follow_up';
    const CATEGORY_CLOSING = 'closing';
    const CATEGORY_CATALOG = 'catalog';
    const CATEGORY_PRICING = 'pricing';
    const CATEGORY_GENERAL = 'general';

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    // ─────────────────────────────────────────────────────────────
    // Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Render template with variable substitution
     * 
     * @param array $data Variables to substitute: ['name' => 'John', 'date' => '2024-01-01']
     * @return string
     */
    public function render(array $data = []): string
    {
        $content = $this->content;

        foreach ($data as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }

        return $content;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get available variable names from the template
     * 
     * @return array
     */
    public function getVariableNames(): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $this->content, $matches);
        return array_unique($matches[1] ?? []);
    }

    // ─────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────

    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_GREETING => 'bi-hand-wave',
            self::CATEGORY_FOLLOW_UP => 'bi-arrow-repeat',
            self::CATEGORY_CLOSING => 'bi-check-circle',
            self::CATEGORY_CATALOG => 'bi-collection',
            self::CATEGORY_PRICING => 'bi-currency-dollar',
            default => 'bi-chat-text'
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_GREETING => 'success',
            self::CATEGORY_FOLLOW_UP => 'info',
            self::CATEGORY_CLOSING => 'primary',
            self::CATEGORY_CATALOG => 'warning',
            self::CATEGORY_PRICING => 'danger',
            default => 'secondary'
        };
    }
}
