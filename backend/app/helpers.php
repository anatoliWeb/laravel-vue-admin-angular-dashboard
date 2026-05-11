<?php

use App\Services\ActivityService;
use App\Services\Translation\TranslationService;

/**
 * Log system activity (global helper).
 */
function activity_log(string $action, ?string $description = null, array $meta = []): void
{
    // Safe for CLI / seeders: no authenticated user context is expected there.
    $userId = function_exists('auth') ? (auth()->id() ?? null) : null;

    app(ActivityService::class)->log($userId, $action, $description, $meta);
}

if (! function_exists('dt')) {

    /**
     * Dynamic translation helper.
     *
     * WHY:
     * Provides unified runtime localization access
     * for database-driven translations.
     *
     * Example:
     *
     * dt('roles.admin')
     * dt('users.created', [':name' => 'John'])
     */
    function dt(
        string $key,
        array $replace = [],
        ?string $locale = null
    ): string {

        return app(TranslationService::class)
            ->get(
                fullKey: $key,
                replace: $replace,
                locale: $locale
            );
    }
}
