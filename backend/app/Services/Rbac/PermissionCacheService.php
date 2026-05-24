<?php

namespace App\Services\Rbac;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Collection;
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
                $freshUser = User::query()
                    ->with(['roles.permissions', 'permissions', 'deniedPermissions'])
                    ->find($user->id);

                if (!$freshUser) {
                    return [];
                }

                $rolePermissions = $freshUser->roles->flatMap(fn ($role) => $role->permissions);
                $directPermissions = $freshUser->permissions;
                $denied = $freshUser->deniedPermissions ?? collect();
                $deniedIds = $denied->pluck('id')->map(fn ($id) => (int) $id)->all();

                /** @var Collection<int, Permission> $permissions */
                $permissions = $rolePermissions
                    ->merge($directPermissions)
                    ->filter(fn ($permission) => $permission instanceof Permission);

                return $permissions
                    ->unique('id')
                    ->reject(fn (Permission $permission) => in_array((int) $permission->id, $deniedIds, true))
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
