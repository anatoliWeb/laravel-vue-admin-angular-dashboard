<?php

use App\Services\ActivityService;

/**
 * Log system activity (global helper).
 */
function activity_log(string $action, ?string $description = null, array $meta = []): void
{
    // Safe for CLI / seeders: no authenticated user context is expected there.
    $userId = function_exists('auth') ? (auth()->id() ?? null) : null;

    app(ActivityService::class)->log($userId, $action, $description, $meta);
}
