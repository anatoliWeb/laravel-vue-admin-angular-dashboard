<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Rbac\PermissionCacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MetaService
{
    public function __construct(
        protected PermissionCacheService $permissionCacheService,
        protected MetaCacheService $metaCacheService,
    ) {
    }

    /**
     * Get metadata required by frontend.
     */
    public function getMeta(): array
    {
        return array_merge(
            $this->getRbacMeta(),
            $this->getBootstrapMeta(),
        );
    }

    /**
     * Lightweight bootstrap payload for admin runtime.
     *
     * @return array<string, mixed>
     */
    public function getBootstrapMeta(): array
    {
        $authUser = auth()->user();
        $user = $authUser instanceof User ? $authUser : null;

        if (!$user) {
            return [
                'current_user' => null,
                'current_user_permissions' => [],
            ];
        }

        $rbacVersion = $this->metaCacheService->rbacVersion();
        $userVersion = $this->metaCacheService->userBootstrapVersion((int) $user->id);
        $cacheKey = sprintf('meta:bootstrap:user:%d:v%d:%d', $user->id, $rbacVersion, $userVersion);

        /** @var array<string, mixed> $payload */
        $payload = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($user): array {
            return [
                'current_user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles()
                        ->select('roles.id', 'roles.name')
                        ->orderBy('roles.name')
                        ->get(),
                ],
                'current_user_permissions' => $this->getUserPermissionNames($user),
            ];
        });

        return $payload;
    }

    /**
     * RBAC metadata payload.
     *
     * @return array<string, mixed>
     */
    public function getRbacMeta(): array
    {
        $rbacVersion = $this->metaCacheService->rbacVersion();

        return [
            'roles' => $this->getSafeCachedModelList(
                cacheKey: sprintf('meta:rbac:roles:v%d', $rbacVersion),
                query: fn () => Role::query()->select('id', 'name', 'description')->orderBy('name')->get(),
                modelClass: Role::class,
            ),
            'permissions' => $this->getSafeCachedModelList(
                cacheKey: sprintf('meta:rbac:permissions:v%d', $rbacVersion),
                query: fn () => Permission::query()->select('id', 'name', 'description')->orderBy('name')->get(),
                modelClass: Permission::class,
            ),
            'role_permissions' => $this->getSafeCachedRolePermissionsMap(
                cacheKey: sprintf('meta:rbac:role_permissions:v%d', $rbacVersion),
            ),
        ];
    }

    /**
     * Ensure cached RBAC lists stay flat and model-typed.
     *
     * @param callable(): Collection<int, Model> $query
     * @return Collection<int, Model>
     */
    protected function getSafeCachedModelList(string $cacheKey, callable $query, string $modelClass): Collection
    {
        $cached = Cache::get($cacheKey);

        if ($cached instanceof Collection && $this->isFlatModelCollection($cached, $modelClass)) {
            return $cached->values();
        }

        $fresh = $query();
        Cache::put($cacheKey, $fresh, now()->addMinutes(10));

        return $fresh->values();
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function getSafeCachedRolePermissionsMap(string $cacheKey): array
    {
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && $this->isValidRolePermissionMap($cached)) {
            return $cached;
        }

        $fresh = $this->getRolePermissionsMap();
        Cache::put($cacheKey, $fresh, now()->addMinutes(10));

        return $fresh;
    }

    /**
     * @param Collection<int, mixed> $items
     */
    protected function isFlatModelCollection(Collection $items, string $modelClass): bool
    {
        if ($items->isEmpty()) {
            return true;
        }

        return $items->every(function (mixed $item) use ($modelClass): bool {
            return $item instanceof $modelClass
                && !empty((string) data_get($item, 'name'))
                && data_get($item, 'id') !== null;
        });
    }

    /**
     * @param array<mixed> $map
     */
    protected function isValidRolePermissionMap(array $map): bool
    {
        foreach ($map as $roleName => $permissionNames) {
            if (!is_string($roleName) || $roleName === '') {
                return false;
            }

            if (!is_array($permissionNames)) {
                return false;
            }

            foreach ($permissionNames as $permissionName) {
                if (!is_string($permissionName) || $permissionName === '') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    protected function getUserPermissionNames(User $user): array
    {
        return $this->permissionCacheService->getEffectivePermissionsForUser($user);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function getRolePermissionsMap(): array
    {
        return Role::with('permissions:id,name')
            ->get()
            ->mapWithKeys(function (Role $role) {
                return [
                    $role->name => $role->permissions
                        ->pluck('name')
                        ->filter(fn ($name) => is_string($name) && $name !== '')
                        ->values()
                        ->all(),
                ];
            })
            ->all();
    }
}
