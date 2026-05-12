<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreRoleRequest;
use App\Http\Requests\Api\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\Localization\TranslationUpsertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RoleController extends BaseController
{
    public function __construct(
        protected TranslationUpsertService $translationUpsert
    ) {
    }

    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            RoleResource::collection($roles)->resolve(),
            dt('notifications.success')
        );
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $role = DB::transaction(function () use ($validated): Role {
            $role = Role::query()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            if (isset($validated['permissions']) && is_array($validated['permissions'])) {
                $permissionIds = \App\Models\Permission::query()
                    ->whereIn('name', $validated['permissions'])
                    ->pluck('id')
                    ->all();
                $role->permissions()->sync($permissionIds);
            }

            $this->persistRoleTranslations(
                roleName: $role->name,
                translations: $validated['translations'] ?? []
            );

            return $role->loadCount(['permissions', 'users']);
        });

        return $this->successResponse(
            array_merge(
                (new RoleResource($role))->resolve(),
                [
                    'permissions' => $role->permissions()->pluck('permissions.name')->values()->all(),
                    'permissions_count' => $role->permissions_count,
                    'users_count' => $role->users_count,
                    'status' => 'active',
                    'type' => in_array(strtolower($role->name), ['admin', 'manager', 'user'], true) ? 'system' : 'custom',
                    'created_at' => $role->created_at?->toISOString(),
                ]
            ),
            dt('notifications.created'),
            201
        );
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $validated = $request->validated();

        $updated = DB::transaction(function () use ($validated, $role): Role {
            // Technical role identifier remains immutable for stable RBAC contracts.
            $role->update([
                'description' => $validated['description'] ?? $role->description,
            ]);

            if (isset($validated['permissions']) && is_array($validated['permissions'])) {
                $permissionIds = \App\Models\Permission::query()
                    ->whereIn('name', $validated['permissions'])
                    ->pluck('id')
                    ->all();
                $role->permissions()->sync($permissionIds);
            }

            $this->persistRoleTranslations(
                roleName: $role->name,
                translations: $validated['translations'] ?? []
            );

            return $role->loadCount(['permissions', 'users']);
        });

        return $this->successResponse(
            array_merge(
                (new RoleResource($updated))->resolve(),
                [
                    'permissions' => $updated->permissions()->pluck('permissions.name')->values()->all(),
                    'permissions_count' => $updated->permissions_count,
                    'users_count' => $updated->users_count,
                    'status' => 'active',
                    'type' => in_array(strtolower($updated->name), ['admin', 'manager', 'user'], true) ? 'system' : 'custom',
                    'created_at' => $updated->created_at?->toISOString(),
                ]
            ),
            dt('notifications.updated')
        );
    }

    /**
     * @param array<string, array{label?: string|null, description?: string|null}> $translations
     */
    protected function persistRoleTranslations(string $roleName, array $translations): void
    {
        $labels = [];
        $descriptions = [];

        foreach ($translations as $locale => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $labels[$locale] = isset($entry['label']) ? (string) $entry['label'] : null;
            $descriptions[$locale] = isset($entry['description']) ? (string) $entry['description'] : null;
        }

        $this->translationUpsert->saveTranslations('roles', $roleName, $labels);
        $this->translationUpsert->saveTranslations('role_descriptions', $roleName, $descriptions);
    }
}
