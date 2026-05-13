<?php

namespace App\Services\Settings;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SettingsResolverService;

/**
 * Settings facade service for runtime consumers.
 *
 * WHY:
 * Application layers should use one API (`settings()->get()`, helper `settings`)
 * rather than coupling directly to resolver internals. This also centralizes
 * preload and future hierarchical-source metadata expansion.
 */
class SettingsService
{
    public function __construct(
        protected SettingsResolverService $resolver,
        protected SettingsCacheService $cache
    ) {
    }

    public function get(string $key, mixed $default = null, ?string $channel = null, ?User $user = null): mixed
    {
        $resolved = $user
            ? $this->cache->rememberResolved(
                $user->id,
                $key,
                $channel,
                fn (): array => $this->resolver->getForUser($user, $key, $channel)
            )
            : $this->resolver->get($key, $channel);

        return $resolved['value'] ?? $default;
    }

    public function set(string $key, mixed $value, array $attributes = []): SystemSetting
    {
        $type = (string) ($attributes['type'] ?? 'string');
        $serialized = $this->serializeValue($value, $type);

        $scope = [
            'key' => $key,
            'scope_user_id' => $attributes['scope_user_id'] ?? null,
            'scope_role_id' => $attributes['scope_role_id'] ?? null,
            'scope_permission_id' => $attributes['scope_permission_id'] ?? null,
        ];

        $payload = [
            'label' => (string) ($attributes['label'] ?? $key),
            'group' => (string) ($attributes['group'] ?? 'general'),
            'description' => $attributes['description'] ?? null,
            'type' => $type,
            'value' => $serialized,
            'default_value' => array_key_exists('default_value', $attributes)
                ? $this->serializeValue($attributes['default_value'], $type)
                : null,
            'is_frontend' => (bool) ($attributes['is_frontend'] ?? true),
            'is_backend' => (bool) ($attributes['is_backend'] ?? true),
            'is_public' => (bool) ($attributes['is_public'] ?? false),
            'is_encrypted' => (bool) ($attributes['is_encrypted'] ?? false),
            'priority' => (int) ($attributes['priority'] ?? 100),
            'is_active' => (bool) ($attributes['is_active'] ?? true),
            'is_system' => (bool) ($attributes['is_system'] ?? false),
            'updated_by' => auth()->id(),
        ];

        $setting = SystemSetting::updateOrCreate($scope, $payload);
        $this->invalidateCaches();

        return $setting;
    }

    /**
     * @return array<string, mixed>
     */
    public function preloadFrontend(User $user): array
    {
        $resolved = $this->cache->rememberPreload($user->id, 'frontend', function () use ($user): array {
            return $this->resolver->resolveAllForUser($user, 'frontend');
        });

        return [
            'channel' => 'frontend',
            'settings' => $this->flattenResolvedMap($resolved),
        ];
    }

    public function invalidateCaches(?int $userId = null): void
    {
        $this->resolver->invalidateCaches($userId);
        if ($userId !== null) {
            $this->cache->forgetResolved($userId, '*', null);
            $this->cache->forgetResolved($userId, '*', 'frontend');
            $this->cache->forgetResolved($userId, '*', 'backend');
            $this->cache->forgetPreload($userId, 'frontend');
            return;
        }

        $this->cache->flushAll();
    }

    protected function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'array', 'json' => is_string($value)
                ? $value
                : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL) ? 'true' : 'false',
            'integer' => (string) ((int) $value),
            'float' => (string) ((float) $value),
            default => (string) $value,
        };
    }

    /**
     * @param array<string, array<string, mixed>> $resolved
     * @return array<string, mixed>
     */
    protected function flattenResolvedMap(array $resolved): array
    {
        $flattened = [];
        foreach ($resolved as $key => $item) {
            $flattened[$key] = $item['value'] ?? null;
        }

        return $flattened;
    }
}

