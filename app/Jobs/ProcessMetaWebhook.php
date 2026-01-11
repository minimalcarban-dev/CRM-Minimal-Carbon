<?php

namespace App\Jobs;

use App\Http\Controllers\MetaWebhookController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMetaWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * The webhook payload.
     */
    protected array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->onQueue('webhooks');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('meta')->info('Processing webhook job', [
            'attempt' => $this->attempts(),
        ]);

        try {
            MetaWebhookController::processPayload($this->payload);
        } catch (\Exception $e) {
            Log::channel('meta')->error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('meta')->error('Webhook job failed permanently', [
            'error' => $exception->getMessage(),
            'payload' => $this->payload,
        ]);

        // TODO: Send alert to admin
    }
}
