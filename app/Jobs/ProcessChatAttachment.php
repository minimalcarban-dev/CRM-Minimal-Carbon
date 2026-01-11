<?php

namespace App\Jobs;

use App\Models\MessageAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

class ProcessChatAttachment
{
    protected MessageAttachment $attachment;

    public function __construct(MessageAttachment $attachment)
    {
        $this->attachment = $attachment;
    }

    public function handle(): void
    {
        try {
            $disk = Storage::disk('public');
            $localPath = $disk->path($this->attachment->path);
            // Save local storage path BEFORE overwriting with Cloudinary URL
            $localStoragePath = $this->attachment->path;

            if (!file_exists($localPath)) {
                return;
            }

            // Initialize Cloudinary using env/config (same approach as other controllers)
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key' => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ],
                'url' => [
                    'secure' => true,
                ],
            ]);

            $uploadApi = $cloudinary->uploadApi();

            $isImage = str_starts_with($this->attachment->mime_type, 'image/');
            $isPdf = $this->attachment->mime_type === 'application/pdf';
            $timestamp = time();
            $uniqueId = uniqid();

            // Keep public_id free of folder prefix to avoid duplicated folder segments
            $publicId = "{$timestamp}_{$uniqueId}";

            $uploadOptions = [
                'public_id' => $publicId,
                'folder' => 'chat-attachments',
            ];

            if ($isImage) {
                $uploadOptions['transformation'] = [
                    'quality' => 'auto:good',
                    'fetch_format' => 'auto',
                ];
                $result = $uploadApi->upload($localPath, $uploadOptions);
            } elseif ($isPdf) {
                // PDF upload - match OrderController approach (just resource_type = raw)
                $uploadOptions['resource_type'] = 'raw';
                $result = $uploadApi->upload($localPath, $uploadOptions);
                $resourceType = 'raw';

            } else {
                // Other files (raw upload)
                $uploadOptions['resource_type'] = 'raw';
                $result = $uploadApi->upload($localPath, $uploadOptions);
            }

            $secureUrl = $result['secure_url'] ?? null;
            // $secureUrl = null;

            // if (($result['resource_type'] ?? null) === 'raw') {
            //     $cloudName = config('cloudinary.cloud_name');
            //     // $secureUrl = "https://res.cloudinary.com/{$cloudName}/raw/upload/{$result['public_id']}";
            //     $secureUrl = "https://res.cloudinary.com/{$cloudName}/raw/upload/{$result['public_id']}.pdf";
            // } else {
            //     $secureUrl = $result['secure_url'] ?? null;
            // }
            $resultPublicId = $result['public_id'] ?? $publicId;
            $resourceType = $result['resource_type'] ?? ($isImage ? 'image' : 'raw');
            $resultFormat = $result['format'] ?? pathinfo($this->attachment->filename, PATHINFO_EXTENSION) ?? null;

            $thumbnailUrl = null;
            if ($isImage && $resultPublicId) {
                $cloudName = config('cloudinary.cloud_name');
                $format = $result['format'] ?? 'jpg';
                $thumbnailUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/c_fit,w_200,h_200/{$resultPublicId}.{$format}";
            }

            // Update attachment to point to Cloudinary
            $this->attachment->update([
                'path' => $secureUrl,
                'thumbnail_path' => $isImage ? $thumbnailUrl : null,
                'metadata' => [
                    'public_id' => $result['public_id'],
                    'resource_type' => $resourceType,
                    'format' => $result['format'] ?? null,
                    'type' => $isPdf ? 'PDF' : ($isImage ? 'Image' : 'File'),
                    'original_filename' => $this->attachment->filename,
                    'local_path' => $isPdf ? $localStoragePath : null, // Keep local path for PDFs
                ],
            ]);

            // Only delete local copy for non-PDF files (keep PDFs locally for proxy access)
            if (!$isPdf) {
                try {
                    $disk->delete($localStoragePath);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete local attachment after Cloudinary upload', [
                        'attachment_id' => $this->attachment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Attachment handle failed', [
                'attachment_id' => $this->attachment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
