<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreSystemSettingRequest;
use App\Http\Requests\Api\UpdateSystemSettingRequest;
use App\Http\Resources\SystemSettingResource;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SettingsResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Settings API controller.
 *
 * Centralizes dynamic configuration CRUD and effective-value resolution so
 * frontend clients can debug inheritance and precedence in one place.
 */
class SettingsController extends BaseController
{
    public function __construct(
        protected SettingsResolverService $resolver
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $search = (string) $request->query('search', '');
        $group = (string) $request->query('group', '');
        $channel = $this->normalizeChannel($request->query('channel'));
        $forUserId = $request->integer('for_user_id') ?: auth()->id();

        $query = SystemSetting::query()
            ->with(['scopeUser:id,name', 'scopeRole:id,name', 'scopePermission:id,name'])
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($nested) use ($search): void {
                    $nested->where('key', 'like', "%{$search}%")
                        ->orWhere('label', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($group !== '', fn ($builder) => $builder->where('group', $group))
            ->orderBy('group')
            ->orderBy('key')
            ->orderByDesc('priority');

        $settings = $query->get();
        $effective = [];

        if ($forUserId) {
            $user = User::find($forUserId);
            if ($user) {
                $effective = $this->resolver->resolveAllForUser($user, $channel);
            }
        }

        return $this->successResponse([
            'settings' => SystemSettingResource::collection($settings)->resolve(),
            'effective' => $effective,
            'groups' => $settings->pluck('group')->unique()->values()->all(),
            'types' => ['string', 'integer', 'number', 'boolean', 'json', 'array', 'enum', 'color', 'select', 'textarea', 'toggle'],
        ], dt('notifications.success'));
    }

    public function store(StoreSystemSettingRequest $request): JsonResponse
    {
        $validated = $this->preparePayload($request->validated());
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $setting = SystemSetting::create($validated);
        $this->resolver->invalidateCaches();

        return $this->successResponse(
            (new SystemSettingResource($setting->load(['scopeUser:id,name', 'scopeRole:id,name', 'scopePermission:id,name'])))->resolve(),
            dt('notifications.created'),
            201
        );
    }

    public function update(UpdateSystemSettingRequest $request, SystemSetting $setting): JsonResponse
    {
        $validated = $this->preparePayload($request->validated());
        $validated['updated_by'] = auth()->id();

        $setting->update($validated);
        $this->resolver->invalidateCaches();

        return $this->successResponse(
            (new SystemSettingResource($setting->fresh(['scopeUser:id,name', 'scopeRole:id,name', 'scopePermission:id,name'])))->resolve(),
            dt('notifications.updated')
        );
    }

    public function destroy(SystemSetting $setting): JsonResponse
    {
        $setting->delete();
        $this->resolver->invalidateCaches();

        return $this->successResponse([
            'deleted' => true,
        ], dt('notifications.deleted'));
    }

    public function effective(Request $request): JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string', 'max:160'],
            'for_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'channel' => ['nullable', 'string', 'in:frontend,backend'],
        ]);

        $userId = $request->integer('for_user_id') ?: auth()->id();
        $channel = $this->normalizeChannel($request->query('channel'));
        $key = (string) $request->query('key');

        $result = null;
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $result = $this->resolver->getForUser($user, $key, $channel);
            }
        }

        if ($result === null) {
            $result = $this->resolver->get($key, $channel);
        }

        return $this->successResponse($result, dt('notifications.success'));
    }

    protected function preparePayload(array $payload): array
    {
        foreach (['value', 'default_value'] as $field) {
            if (array_key_exists($field, $payload) && is_array($payload[$field])) {
                $payload[$field] = json_encode($payload[$field], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            if (array_key_exists($field, $payload) && is_bool($payload[$field])) {
                $payload[$field] = $payload[$field] ? 'true' : 'false';
            }
            if (array_key_exists($field, $payload) && is_int($payload[$field])) {
                $payload[$field] = (string) $payload[$field];
            }
            if (array_key_exists($field, $payload) && is_float($payload[$field])) {
                $payload[$field] = (string) $payload[$field];
            }
        }

        return $payload;
    }

    protected function normalizeChannel(mixed $channel): ?string
    {
        return in_array($channel, ['frontend', 'backend'], true) ? $channel : null;
    }
}
