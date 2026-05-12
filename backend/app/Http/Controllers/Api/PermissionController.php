<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StorePermissionRequest;
use App\Http\Requests\Api\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Services\Localization\TranslationUpsertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PermissionController extends BaseController
{
    public function __construct(
        protected TranslationUpsertService $translationUpsert
    ) {
    }

    public function index(): JsonResponse
    {
        $permissions = Permission::query()
            ->withCount('roles')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            PermissionResource::collection($permissions)->resolve(),
            dt('notifications.success')
        );
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $permission = DB::transaction(function () use ($validated): Permission {
            $permission = Permission::query()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            $this->persistPermissionTranslations(
                permissionName: $permission->name,
                translations: $validated['translations'] ?? []
            );

            return $permission->loadCount('roles');
        });

        return $this->successResponse(
            array_merge(
                (new PermissionResource($permission))->resolve(),
                [
                    'module' => explode('.', $permission->name)[0] ?? 'system',
                    'used_by_roles' => $permission->roles()->pluck('roles.name')->values()->all(),
                    'type' => $this->inferType($permission->name),
                    'usage' => $permission->roles_count > 0 ? 'used' : 'unused',
                    'created_at' => $permission->created_at?->toISOString(),
                ]
            ),
            dt('notifications.created'),
            201
        );
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $validated = $request->validated();

        $updated = DB::transaction(function () use ($validated, $permission): Permission {
            // Technical permission key remains immutable for RBAC safety.
            $permission->update([
                'description' => $validated['description'] ?? $permission->description,
            ]);

            $this->persistPermissionTranslations(
                permissionName: $permission->name,
                translations: $validated['translations'] ?? []
            );

            return $permission->loadCount('roles');
        });

        return $this->successResponse(
            array_merge(
                (new PermissionResource($updated))->resolve(),
                [
                    'module' => explode('.', $updated->name)[0] ?? 'system',
                    'used_by_roles' => $updated->roles()->pluck('roles.name')->values()->all(),
                    'type' => $this->inferType($updated->name),
                    'usage' => $updated->roles_count > 0 ? 'used' : 'unused',
                    'created_at' => $updated->created_at?->toISOString(),
                ]
            ),
            dt('notifications.updated')
        );
    }

    /**
     * @param array<string, array{label?: string|null, description?: string|null}> $translations
     */
    protected function persistPermissionTranslations(string $permissionName, array $translations): void
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

        $this->translationUpsert->saveTranslations('permissions', $permissionName, $labels);
        $this->translationUpsert->saveTranslations('permission_descriptions', $permissionName, $descriptions);
    }

    protected function inferType(string $permissionName): string
    {
        $suffix = collect(explode('.', $permissionName))->slice(1)->implode('.');
        if (str_contains($suffix, 'view') || str_contains($suffix, 'list') || str_contains($suffix, 'show')) {
            return 'read';
        }

        if (
            str_contains($suffix, 'create')
            || str_contains($suffix, 'edit')
            || str_contains($suffix, 'update')
            || str_contains($suffix, 'delete')
        ) {
            return 'write';
        }

        return 'manage';
    }
}

