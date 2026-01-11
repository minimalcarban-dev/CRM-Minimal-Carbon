<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;

class LeadScoringService
{
    // Score weights
    const POINTS_EMAIL = 15;
    const POINTS_PHONE = 20;
    const POINTS_MESSAGE = 5;
    const POINTS_MESSAGE_MAX = 30;
    const POINTS_RECENT_24H = 20;
    const POINTS_RECENT_72H = 10;
    const POINTS_RECENT_7D = 5;
    const POINTS_PRIORITY_HIGH = 15;
    const POINTS_PRIORITY_MEDIUM = 5;
    const MAX_SCORE = 100;

    /**
     * Calculate score for a lead
     */
    public function calculateScore(Lead $lead): int
    {
        $score = 0;

        // Contact information points
        if ($lead->email) {
            $score += self::POINTS_EMAIL;
        }

        if ($lead->phone) {
            $score += self::POINTS_PHONE;
        }

        // Engagement points (message count)
        $messageCount = $lead->messages()->count();
        $messagePoints = min($messageCount * self::POINTS_MESSAGE, self::POINTS_MESSAGE_MAX);
        $score += $messagePoints;

        // Recency points
        if ($lead->last_contact_at) {
            $hoursSinceContact = now()->diffInHours($lead->last_contact_at);

            if ($hoursSinceContact < 24) {
                $score += self::POINTS_RECENT_24H;
            } elseif ($hoursSinceContact < 72) {
                $score += self::POINTS_RECENT_72H;
            } elseif ($hoursSinceContact < 168) { // 7 days
                $score += self::POINTS_RECENT_7D;
            }
        }

        // Priority bonus
        if ($lead->priority === 'high') {
            $score += self::POINTS_PRIORITY_HIGH;
        } elseif ($lead->priority === 'medium') {
            $score += self::POINTS_PRIORITY_MEDIUM;
        }

        return min($score, self::MAX_SCORE);
    }

    /**
     * Update score for a lead and log activity
     */
    public function updateScore(Lead $lead): int
    {
        $oldScore = $lead->lead_score;
        $newScore = $this->calculateScore($lead);

        if ($oldScore !== $newScore) {
            $lead->update(['lead_score' => $newScore]);

            $lead->logActivity(
                LeadActivity::TYPE_SCORE_UPDATED,
                "Lead score updated from {$oldScore} to {$newScore}",
                null,
                ['old_score' => $oldScore, 'new_score' => $newScore]
            );
        }

        return $newScore;
    }

    /**
     * Batch update scores for multiple leads
     */
    public function updateScoresInBatch(array $leadIds): void
    {
        $leads = Lead::whereIn('id', $leadIds)->get();

        foreach ($leads as $lead) {
            $this->updateScore($lead);
        }
    }

    /**
     * Get score breakdown for a lead
     */
    public function getScoreBreakdown(Lead $lead): array
    {
        $breakdown = [];

        // Contact information
        if ($lead->email) {
            $breakdown['email'] = self::POINTS_EMAIL;
        }
        if ($lead->phone) {
            $breakdown['phone'] = self::POINTS_PHONE;
        }

        // Engagement
        $messageCount = $lead->messages()->count();
        $breakdown['messages'] = [
            'count' => $messageCount,
            'points' => min($messageCount * self::POINTS_MESSAGE, self::POINTS_MESSAGE_MAX),
        ];

        // Recency
        if ($lead->last_contact_at) {
            $hoursSinceContact = now()->diffInHours($lead->last_contact_at);
            if ($hoursSinceContact < 24) {
                $breakdown['recency'] = ['period' => '24h', 'points' => self::POINTS_RECENT_24H];
            } elseif ($hoursSinceContact < 72) {
                $breakdown['recency'] = ['period' => '72h', 'points' => self::POINTS_RECENT_72H];
            } elseif ($hoursSinceContact < 168) {
                $breakdown['recency'] = ['period' => '7d', 'points' => self::POINTS_RECENT_7D];
            }
        }

        // Priority
        if ($lead->priority === 'high') {
            $breakdown['priority'] = ['level' => 'high', 'points' => self::POINTS_PRIORITY_HIGH];
        } elseif ($lead->priority === 'medium') {
            $breakdown['priority'] = ['level' => 'medium', 'points' => self::POINTS_PRIORITY_MEDIUM];
        }

        $breakdown['total'] = $this->calculateScore($lead);

        return $breakdown;
    }
}
