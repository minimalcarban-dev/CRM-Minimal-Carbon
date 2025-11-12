<?php

namespace App\Jobs;

use App\Models\MessageAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Services\VirusScanner;
use Intervention\Image\Image;

class ProcessChatAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $attachment;

    /**
     * Create a new job instance.
     */
    public function __construct(MessageAttachment $attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Optional: virus scan again (defense in depth)
        $scanner = app(VirusScanner::class);
        $pathAbs = Storage::disk('public')->path($this->attachment->path);
        $scan = $scanner->scan($pathAbs);
        if (empty($scan['clean'])) {
            // Delete infected file and record
            Storage::disk('public')->delete($this->attachment->path);
            $this->attachment->delete();
            return;
        }

        // Check if it's an image
        if (str_starts_with($this->attachment->mime_type, 'image/')) {
            $this->processImage();
        }
        // TODO: Add other file type processing as needed
    }

    /**
     * Process image attachments
     */
    protected function processImage(): void
    {
        $path = Storage::disk('public')->path($this->attachment->path);
        $thumbPath = 'chat-attachments/thumbnails/' . basename($this->attachment->path);

        // Create thumbnail
        $img = Image::make($path);
        $img->fit(300, 300, function ($constraint) {
            $constraint->aspectRatio();
        });

        Storage::disk('public')->put($thumbPath, $img->encode());

        // Update attachment with thumbnail path
        $this->attachment->update([
            'thumbnail_path' => $thumbPath
        ]);
    }
}
