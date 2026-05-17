<?php

namespace App\Services\Rbac;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionCacheService
{
    /**
     * Resolve and cache effective permission names for a user.
     *
     * WHY:
     * Auth payload and permission-aware UI hit this path frequently.
     * Caching reduces repeated relation resolution for unchanged RBAC state.
     *
     * @return array<int, string>
     */
    public function getEffectivePermissionsForUser(User $user): array
    {
        $cacheKey = $this->keyForUserId((int) $user->id);

        return Cache::remember(
            $cacheKey,
            now()->addSeconds($this->ttlSeconds()),
            function () use ($user): array {
                $user->loadMissing(['roles.permissions', 'permissions', 'deniedPermissions']);

                $rolePermissions = $user->roles->flatMap(fn ($role) => $role->permissions);
                $directPermissions = $user->permissions;
                $denied = $user->deniedPermissions ?? collect();

                return $rolePermissions
                    ->merge($directPermissions)
                    ->unique('id')
                    ->reject(fn ($permission) => $denied->contains('id', $permission->id))
                    ->pluck('name')
                    ->values()
                    ->all();
            }
        );
    }

    public function forgetForUser(User $user): void
    {
        $this->forgetForUserId((int) $user->id);
    }

    public function forgetForUserId(int $userId): void
    {
        Cache::forget($this->keyForUserId($userId));
    }

    public function forgetAll(): void
    {
        Cache::flush();
    }

    protected function keyForUserId(int $userId): string
    {
        return sprintf('rbac:user:%d:effective_permissions', $userId);
    }

    protected function ttlSeconds(): int
    {
        return (int) config('cache.rbac_permissions_ttl', 600);
    }
}

