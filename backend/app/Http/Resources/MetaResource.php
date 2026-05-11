<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Metadata API resource.
 *
 * WHY THIS RESOURCE EXISTS:
 * Metadata payload is consumed by multiple frontends to build forms, role
 * selectors, and permission-aware UI behaviors.
 *
 * WHY NOT RETURN RAW ELOQUENT COLLECTIONS:
 * Direct model serialization can change unexpectedly when model attributes or
 * relations evolve, breaking clients without endpoint-level changes.
 *
 * WHAT THIS RESOURCE CONTROLS:
 * It freezes a clear metadata contract by returning explicit role/permission
 * structures and a normalized current-user block.
 */
class MetaResource extends JsonResource
{
    /**
     * Transform metadata payload into stable API structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'roles' => collect(data_get($this->resource, 'roles', []))
                ->map(fn ($role) => (new RoleResource($role))->resolve())
                ->values()
                ->all(),
            'permissions' => collect(data_get($this->resource, 'permissions', []))
                ->map(fn ($permission) => (new PermissionResource($permission))->resolve())
                ->values()
                ->all(),
            'role_permissions' => data_get($this->resource, 'role_permissions', []),
            'current_user' => $this->transformCurrentUser(data_get($this->resource, 'current_user')),
            'current_user_permissions' => array_values(data_get($this->resource, 'current_user_permissions', [])),
        ];
    }

    /**
     * Normalize current user block for metadata response.
     *
     * @param mixed $currentUser
     * @return array<string, mixed>|null
     */
    protected function transformCurrentUser(mixed $currentUser): ?array
    {
        if ($currentUser === null) {
            return null;
        }

        return [
            'id' => data_get($currentUser, 'id'),
            'name' => data_get($currentUser, 'name'),
            'email' => data_get($currentUser, 'email'),
            'roles' => collect(data_get($currentUser, 'roles', []))
                ->map(fn ($role) => (new RoleResource($role))->resolve())
                ->values()
                ->all(),
        ];
    }
}
