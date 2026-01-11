<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\LeadScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLeadScore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $leadId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $leadId)
    {
        $this->leadId = $leadId;
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(LeadScoringService $scoringService): void
    {
        $lead = Lead::find($this->leadId);

        if ($lead) {
            $scoringService->updateScore($lead);
        }
    }
}
