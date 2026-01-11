<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'admin/*', 'broadcasting/*'],
    // Add common local dev origins (vite dev server and artisan serve)
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
        'http://192.168.1.4:5173',
        'http://192.168.1.4:8000',
        // 'https://minimalcarbon.in',
    ],
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
