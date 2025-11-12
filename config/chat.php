<?php

return [
    // Max upload size in megabytes per file
    'max_upload_mb' => (int) env('CHAT_MAX_UPLOAD_MB', 10),

    // Strict whitelist of allowed MIME types for attachments
    // Comma-separated ENV can override fully
    'allowed_mime_types' => array_values(array_filter(array_map('trim', explode(',', (string) env('CHAT_ALLOWED_MIMES',
        implode(',', [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'text/plain',
        ])
    ))))),
];
