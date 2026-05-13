<?php

namespace App\Services\Settings;

use Illuminate\Support\Facades\Cache;

/**
 * Centralized settings cache wrapper.
 *
 * WHY:
 * Settings are consumed from API, jobs, guards and bootstrappers. A single
 * cache contract keeps invalidation deterministic and avoids key drift.
 */
class SettingsCacheService
{
    protected const PREFIX = 'settings';
    protected const TTL_SECONDS = 600;

    public function rememberResolved(int $userId, string $key, ?string $channel, callable $resolver): array
    {
        return Cache::remember(
            $this->resolvedKey($userId, $key, $channel),
            now()->addSeconds(self::TTL_SECONDS),
            $resolver
        );
    }

    public function rememberPreload(int $userId, ?string $channel, callable $resolver): array
    {
        return Cache::remember(
            $this->preloadKey($userId, $channel),
            now()->addSeconds(self::TTL_SECONDS),
            $resolver
        );
    }

    public function forgetResolved(int $userId, string $key = '*', ?string $channel = null): void
    {
        Cache::forget($this->resolvedKey($userId, $key, $channel));
    }

    public function forgetPreload(int $userId, ?string $channel = null): void
    {
        Cache::forget($this->preloadKey($userId, $channel));
    }

    public function flushAll(): void
    {
        Cache::flush();
    }

    protected function resolvedKey(int $userId, string $key, ?string $channel): string
    {
        $channelName = $channel ?? 'all';
        return sprintf('%s:resolved:user:%d:key:%s:channel:%s', self::PREFIX, $userId, $key, $channelName);
    }

    protected function preloadKey(int $userId, ?string $channel): string
    {
        $channelName = $channel ?? 'all';
        return sprintf('%s:preload:user:%d:channel:%s', self::PREFIX, $userId, $channelName);
    }
}

