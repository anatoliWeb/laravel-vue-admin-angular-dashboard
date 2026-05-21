<?php

return [
    'attachments' => [
        'disk' => env('CHAT_ATTACHMENTS_DISK', 'local'),
        'max_size_kb' => (int) env('CHAT_ATTACHMENTS_MAX_SIZE_KB', 10240),
        'allowed_mimes' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
            'image/gif',
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'audio/mpeg',
            'audio/wav',
            'audio/ogg',
            'audio/mp4',
        ],
        // Placeholder strategy only for this phase (no real scanner integration).
        'virus_scan_enabled' => (bool) env('CHAT_ATTACHMENTS_VIRUS_SCAN_ENABLED', false),
    ],
    'typing' => [
        // Throttle start-typing broadcast to reduce event noise.
        'throttle_seconds' => (int) env('CHAT_TYPING_THROTTLE_SECONDS', 2),
    ],
];
