<?php

return [
    'attachments' => [
        'disk' => env('CHAT_ATTACHMENTS_DISK', 'local'),
        'max_size_kb' => (int) env('CHAT_ATTACHMENTS_MAX_SIZE_KB', 10240),
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf',
            'text/plain',
            'audio/mpeg',
            'audio/wav',
        ],
        // Placeholder strategy only for this phase (no real scanner integration).
        'virus_scan_enabled' => (bool) env('CHAT_ATTACHMENTS_VIRUS_SCAN_ENABLED', false),
    ],
];

