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
            ->with(['roles:id,name'])
            ->orderBy('name')
            ->get();

        $payload = $permissions->map(function (Permission $permission): array {
            return $this->transformPermission($permission);
        })->values()->all();

        return $this->successResponse(
            $payload,
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
            $this->transformPermission($permission),
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
            $this->transformPermission($updated),
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

    /**
     * Backend provides localized presentation fields so frontend never has to
     * map technical metadata (`group`, `type`) to human-readable labels.
     *
     * @return array<string, mixed>
     */
    protected function transformPermission(Permission $permission): array
    {
        $type = $this->inferType($permission->name);
        $group = explode('.', $permission->name)[0] ?? 'system';

        return array_merge(
            (new PermissionResource($permission))->resolve(),
            [
                'module' => $group,
                'group_label' => $this->translateWithFallback(
                    'permissions.groups.' . $group,
                    ucfirst(str_replace('_', ' ', $group))
                ),
                'used_by_roles' => $permission->roles->pluck('name')->values()->all(),
                'type' => $type,
                'type_label' => $this->translateWithFallback(
                    'permissions.types.' . $type,
                    ucfirst($type)
                ),
                'usage' => $permission->roles->isNotEmpty() ? 'used' : 'unused',
                'created_at' => $permission->created_at?->toISOString(),
            ]
        );
    }

    protected function translateWithFallback(string $key, string $fallback): string
    {
        $translated = dt($key);
        return $translated === $key ? $fallback : $translated;
    }
}
