<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class MetaService
{
    /**
     * Get metadata required by frontend.
     *
     * WHY:
     * One request gives frontend everything needed
     * for role selects and permission-based UI.
     */
    public function getMeta(): array
    {
        /** @var User|null $user */
        $user = auth()->user();
        $roles = Role::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return [
            'roles' => $roles,

            'permissions' => $permissions,

            // WHY:
            // Frontend needs role → permissions mapping to correctly
            // display and auto-apply RBAC logic without duplicating business rules.
            'role_permissions' => $this->getRolePermissionsMap(),

            'current_user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles()
                    ->select('roles.id', 'roles.name')
                    ->get(),
            ] : null,

            'current_user_permissions' => $user
                ? $this->getUserPermissionNames($user)
                : [],
        ];
    }

    /**
     * Get all permissions available to user.
     *
     * WHY:
     * UI buttons should be rendered only when user has access.
     */
    protected function getUserPermissionNames(User $user): array
    {
        $directPermissions = $user->permissions()
            ->pluck('name')
            ->toArray();

        $rolePermissions = Permission::query()
            ->whereHas('roles.users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->pluck('name')
            ->toArray();

        return array_values(array_unique([
            ...$directPermissions,
            ...$rolePermissions,
        ]));
    }

    /**
     * Get role => permissions mapping for frontend RBAC auto-sync.
     *
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
                        ->values()
                        ->all(),
                ];
            })
            ->all();
    }
}
