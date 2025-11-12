<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'message_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'thumbnail_path',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
    ];

    /**
     * Get the message this attachment belongs to
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the full URL for the attachment
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    /**
     * Check if the attachment is an image
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Get the thumbnail URL if available
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->is_image || !$this->thumbnail_path) {
            return null;
        }
        return Storage::url($this->thumbnail_path);
    }

    /**
     * Delete the file when the model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            Storage::disk('public')->delete($attachment->path);
            if ($attachment->is_image && $attachment->thumbnail_path) {
                Storage::disk('public')->delete($attachment->thumbnail_path);
            }
        });
    }
}