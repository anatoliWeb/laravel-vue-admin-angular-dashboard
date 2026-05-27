<?php

return [
    'rate_limits' => [
        'enabled' => (bool) env('SECURITY_RATE_LIMITS_ENABLED', true),
        'auth_login' => [
            'max_attempts' => (int) env('AUTH_LOGIN_RATE_LIMIT_MAX_ATTEMPTS', 5),
            'decay_seconds' => (int) env('AUTH_LOGIN_RATE_LIMIT_DECAY_SECONDS', 60),
        ],
        'api_docs' => [
            'max_attempts' => (int) env('API_DOCS_RATE_LIMIT_MAX_ATTEMPTS', 60),
            'decay_seconds' => (int) env('API_DOCS_RATE_LIMIT_DECAY_SECONDS', 60),
        ],
        'chat_typing' => [
            'max_attempts' => (int) env('CHAT_TYPING_RATE_LIMIT_MAX_ATTEMPTS', 120),
            'decay_seconds' => (int) env('CHAT_TYPING_RATE_LIMIT_DECAY_SECONDS', 60),
        ],
        'chat_attachments' => [
            'max_attempts' => (int) env('CHAT_ATTACHMENT_RATE_LIMIT_MAX_ATTEMPTS', 20),
            'decay_seconds' => (int) env('CHAT_ATTACHMENT_RATE_LIMIT_DECAY_SECONDS', 60),
        ],
    ],
];
