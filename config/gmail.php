<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gmail OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Gmail API integration.
    | credentials should be obtained from the Google Cloud Console.
    |
    */

    'client_id' => env('GMAIL_CLIENT_ID'),
    'client_secret' => env('GMAIL_CLIENT_SECRET'),
    'redirect_uri' => env('GMAIL_REDIRECT_URI'), // Optional, can be dynamic
    'application_name' => env('GMAIL_APP_NAME', 'Minimal Carbon CRM'),

    'scopes' => [
        'https://www.googleapis.com/auth/gmail.readonly',
        'https://www.googleapis.com/auth/gmail.modify',
        'https://www.googleapis.com/auth/gmail.compose',
        'https://www.googleapis.com/auth/gmail.send',
        'https://www.googleapis.com/auth/userinfo.email',
    ],

    'access_type' => 'offline',
    'approval_prompt' => 'force', // Ensures we always get a refresh token
    'prompt' => 'consent',

    'sync' => [
        'per_page' => 50,
        'storage_path' => 'emails/attachments',
        'history_poll_interval' => 300, // Seconds
    ],
];
