<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lead Management Configuration
    |--------------------------------------------------------------------------
    */

    // SLA
    'default_sla_hours' => env('LEAD_DEFAULT_SLA_HOURS', 24),

    // Lead Scoring
    'score' => [
        'message_points' => env('LEAD_SCORE_MESSAGE_POINTS', 5),
        'contact_points' => env('LEAD_SCORE_CONTACT_POINTS', 20),
    ],

    // Auto-assignment
    'auto_assign' => env('LEAD_AUTO_ASSIGN', true),
    'assignment_strategy' => env('LEAD_ASSIGNMENT_STRATEGY', 'round_robin'), // round_robin, load_balanced, random

];
