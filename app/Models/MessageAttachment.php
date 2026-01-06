<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

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

    protected $appends = [
        'url',
        'thumbnail_url',
        'is_image',
        'download_url',
    ];

    public function getDownloadUrlAttribute()
    {
        // Prefer explicit secure URL if present
        if ($this->path && str_starts_with($this->path, 'http')) {
            return $this->path;
        }

        $meta = $this->metadata ?? [];
        if (is_array($meta) && !empty($meta['public_id'])) {
            $cloud = config('cloudinary.cloud_name');
            $publicId = $meta['public_id'];
            $resourceType = $meta['resource_type'] ?? 'raw';
            $format = $meta['format'] ?? pathinfo($this->filename ?? '', PATHINFO_EXTENSION);

            // Build conservative raw URL — if format not available, omit extension
            $ext = $format ? ('.' . $format) : '';
            // Use secure cloudinary domain
            return "https://res.cloudinary.com/{$cloud}/{$resourceType}/upload/{$publicId}{$ext}";
        }

        // Fallback to storage URL for local files
        return $this->path ? Storage::url($this->path) : null;
    }

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
        if (!$this->path) {
            return null;
        }

        if (str_starts_with($this->path, 'http')) {
            return $this->path;
        }

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

        if (str_starts_with($this->thumbnail_path, 'http')) {
            return $this->thumbnail_path;
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
            $meta = $attachment->metadata ?? [];

            // If we have cloudinary metadata, attempt to delete from Cloudinary
            if (is_array($meta) && !empty($meta['public_id'])) {
                try {
                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => config('cloudinary.cloud_name'),
                            'api_key' => config('cloudinary.api_key'),
                            'api_secret' => config('cloudinary.api_secret'),
                        ],
                        'url' => ['secure' => true],
                    ]);

                    $uploadApi = $cloudinary->uploadApi();
                    $resourceType = $meta['resource_type'] ?? 'image';
                    $uploadApi->destroy($meta['public_id'], ['resource_type' => $resourceType]);

                    Log::info('Deleted attachment from Cloudinary', ['public_id' => $meta['public_id'], 'resource_type' => $resourceType]);
                } catch (\Throwable $e) {
                    Log::error('Failed to delete attachment from Cloudinary', ['error' => $e->getMessage(), 'public_id' => $meta['public_id'] ?? null]);
                }

                return;
            }

            // Fallback: delete local files if stored locally
            try {
                Storage::disk('public')->delete($attachment->path);
                if ($attachment->is_image && $attachment->thumbnail_path) {
                    Storage::disk('public')->delete($attachment->thumbnail_path);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to delete local attachment files', ['error' => $e->getMessage(), 'attachment_id' => $attachment->id]);
            }
        });
    }
}