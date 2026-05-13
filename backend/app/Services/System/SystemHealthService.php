<?php

namespace App\Services\System;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemHealthService
{
    /**
     * @return array<string, string>
     */
    public function health(): array
    {
        $db = 'ok';
        $cache = 'ok';

        try {
            DB::select('select 1');
        } catch (\Throwable) {
            $db = 'failed';
        }

        try {
            $key = 'system.health.ping';
            Cache::put($key, 'pong', 10);
            $cache = Cache::get($key) === 'pong' ? 'ok' : 'failed';
        } catch (\Throwable) {
            $cache = 'failed';
        }

        return [
            'database' => $db,
            'cache' => $cache,
            'app_env' => (string) config('app.env'),
            'app_debug' => config('app.debug') ? 'true' : 'false',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function debugInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => (string) config('app.env'),
            'app_locale' => (string) config('app.locale'),
            'fallback_locale' => (string) config('app.fallback_locale'),
            'cache_driver' => (string) config('cache.default'),
            'queue_driver' => (string) config('queue.default'),
            'timezone' => (string) config('app.timezone'),
        ];
    }
}
