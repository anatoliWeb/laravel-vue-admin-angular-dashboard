<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreSystemSettingRequest;
use App\Http\Requests\Api\UpdateSystemSettingRequest;
use App\Http\Resources\SystemSettingResource;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Localization\TranslationUpsertService;
use App\Services\Settings\SettingsService;
use App\Services\SettingsResolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Runtime settings API controller.
 *
 * WHY THIS CONTROLLER EXISTS:
 * Platform settings are dynamic runtime configuration records and must support:
 * - CRUD management
 * - inheritance debugging
 * - effective value inspection
 * - frontend preload hydration
 * - feature flags
 * - future tenant-aware resolution
 *
 * IMPORTANT:
 * This controller should orchestrate only:
 * - validation
 * - response formatting
 * - service calls
 *
 * Business logic must remain inside:
 * - SettingsService
 * - SettingsResolverService
 * - SettingsCacheService
 */
class SettingsController extends BaseController
{
    public function __construct(
        protected SettingsResolverService $resolver,
        protected SettingsService $settings,
        protected TranslationUpsertService $translationUpsert
    ) {
    }

    /**
     * List runtime settings and effective resolved values.
     *
     * WHY:
     * Admin UI must inspect:
     * - raw setting records
     * - inheritance layers
     * - effective runtime values
     * - grouped configuration structure
     */
    public function index(Request $request): JsonResponse
    {
        $search = (string) $request->query('search', '');
        $group = (string) $request->query('group', '');
        $channel = $this->normalizeChannel(
            $request->query('channel')
        );
        $isActive = $this->normalizeBooleanFilter($request->query('is_active'));
        $isPublic = $this->normalizeBooleanFilter($request->query('is_public'));
        $isEncrypted = $this->normalizeBooleanFilter($request->query('is_encrypted'));
        $type = (string) $request->query('type', '');
        $perPage = min(max($request->integer('per_page', 15), 5), 100);

        $forUserId = $request->integer('for_user_id')
            ?: auth()->id();

        $query = SystemSetting::query()

            ->with([
                'scopeUser:id,name',
                'scopeRole:id,name',
                'scopePermission:id,name',
            ])

            ->when(
                $search !== '',
                function ($builder) use ($search): void {

                    $builder->where(function ($nested) use ($search): void {

                        $nested
                            ->where('key', 'like', "%{$search}%")
                            ->orWhere('label', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }
            )

            ->when(
                $group !== '',
                fn ($builder) => $builder->where('group', $group)
            )
            ->when(
                $type !== '',
                fn ($builder) => $builder->where('type', $type)
            )
            ->when(
                $isActive !== null,
                fn ($builder) => $builder->where('is_active', $isActive)
            )
            ->when(
                $isPublic !== null,
                fn ($builder) => $builder->where('is_public', $isPublic)
            )
            ->when(
                $isEncrypted !== null,
                fn ($builder) => $builder->where('is_encrypted', $isEncrypted)
            )
            ->when(
                $channel === SystemSetting::CHANNEL_FRONTEND,
                fn ($builder) => $builder->where('is_frontend', true)
            )
            ->when(
                $channel === SystemSetting::CHANNEL_BACKEND,
                fn ($builder) => $builder->where('is_backend', true)
            )

            ->orderBy('group')
            ->orderBy('key')
            ->orderByDesc('priority');

        $availableGroups = (clone $query)
            ->reorder()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->values()
            ->all();

        $settingsPaginator = $query->paginate($perPage)->withQueryString();
        /** @var Collection<int, SystemSetting> $settings */
        $settings = collect($settingsPaginator->items());
        /*
        |--------------------------------------------------------------------------
        | Effective Runtime Resolution
        |--------------------------------------------------------------------------
        |
        | Admin UI can inspect which setting finally won inheritance resolution.
        */

        $effective = [];

        if ($forUserId) {

            $user = User::find($forUserId);

            if ($user) {
                $keys = $settings->pluck('key')->values()->all();
                $effective = $this->resolver->resolveManyForUser($user, $keys, $channel);
            }
        }

        return $this->successResponse([
            'settings' => SystemSettingResource::collection($settings)
                ->resolve(),

            'effective' => $effective,

            'groups' => $availableGroups,

            /*
            |--------------------------------------------------------------------------
            | Supported Runtime Types
            |--------------------------------------------------------------------------
            */

            'types' => [
                SystemSetting::TYPE_STRING,
                SystemSetting::TYPE_INTEGER,
                SystemSetting::TYPE_FLOAT,
                SystemSetting::TYPE_BOOLEAN,
                SystemSetting::TYPE_JSON,
                SystemSetting::TYPE_ARRAY,

                /*
                |--------------------------------------------------------------------------
                | Future UI-Specific Types
                |--------------------------------------------------------------------------
                */

                'enum',
                'color',
                'select',
                'textarea',
                'toggle',
            ],
            'meta' => [
                'current_page' => $settingsPaginator->currentPage(),
                'last_page' => $settingsPaginator->lastPage(),
                'per_page' => $settingsPaginator->perPage(),
                'total' => $settingsPaginator->total(),
            ],

        ], dt('notifications.success'));
    }

    /**
     * Create runtime setting.
     *
     * IMPORTANT:
     * Actual serialization/casting/inheritance logic is delegated
     * to SettingsService.
     */
    public function store(
        StoreSystemSettingRequest $request
    ): JsonResponse {

        $validated = $request->validated();

        $setting = $this->settings->set(
            key: $validated['key'],
            value: $validated['value'] ?? null,

            attributes: [
                ...$validated,

                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );
        $this->persistSettingTranslations($setting->key, $validated['translations'] ?? []);

        return $this->successResponse(
            (new SystemSettingResource(
                $setting->fresh([
                    'scopeUser:id,name',
                    'scopeRole:id,name',
                    'scopePermission:id,name',
                ])
            ))->resolve(),

            dt('notifications.created'),

            201
        );
    }

    /**
     * Update runtime setting.
     *
     * IMPORTANT:
     * Effective runtime caches are invalidated automatically
     * by SettingsService.
     */
    public function update(
        UpdateSystemSettingRequest $request,
        SystemSetting $setting
    ): JsonResponse {

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Preserve Existing Scope Ownership
        |--------------------------------------------------------------------------
        */

        $attributes = [
            ...$validated,

            'scope_user_id' => $validated['scope_user_id']
                ?? $setting->scope_user_id,

            'scope_role_id' => $validated['scope_role_id']
                ?? $setting->scope_role_id,

            'scope_permission_id' => $validated['scope_permission_id']
                ?? $setting->scope_permission_id,

            'updated_by' => auth()->id(),
        ];

        $updated = $this->settings->set(
            key: $setting->key,

            value: $validated['value']
            ?? $setting->value,

            attributes: $attributes
        );
        $this->persistSettingTranslations($updated->key, $validated['translations'] ?? []);

        return $this->successResponse(
            (new SystemSettingResource(
                $updated->fresh([
                    'scopeUser:id,name',
                    'scopeRole:id,name',
                    'scopePermission:id,name',
                ])
            ))->resolve(),

            dt('notifications.updated')
        );
    }

    /**
     * Delete runtime setting.
     */
    public function destroy(
        SystemSetting $setting
    ): JsonResponse {

        $setting->delete();

        $this->settings->invalidateCaches();

        return $this->successResponse([
            'deleted' => true,
        ], dt('notifications.deleted'));
    }

    /**
     * Resolve effective runtime value with inheritance metadata.
     *
     * WHY:
     * Admin/debug tools must understand:
     * - which value won
     * - which inheritance scope resolved it
     * - where the value originated from
     */
    public function effective(
        Request $request
    ): JsonResponse {

        $request->validate([
            'key' => ['required', 'string', 'max:160'],

            'for_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'channel' => [
                'nullable',
                'string',
                'in:frontend,backend',
            ],
        ]);

        $userId = $request->integer('for_user_id')
            ?: auth()->id();

        $channel = $this->normalizeChannel(
            $request->query('channel')
        );

        $key = (string) $request->query('key');

        $user = $userId
            ? User::find($userId)
            : null;

        $result = $this->settings->getDetailed(
            key: $key,
            channel: $channel,
            user: $user
        );

        return $this->successResponse(
            $result,
            dt('notifications.success')
        );
    }

    /**
     * Frontend runtime preload endpoint.
     *
     * WHY:
     * SPA applications preload runtime settings during bootstrap
     * to avoid:
     * - duplicated API calls
     * - hydration flickering
     * - runtime configuration waterfalls
     *
     * IMPORTANT:
     * Only frontend-safe settings are exposed here.
     */
    public function preload(
        Request $request
    ): JsonResponse {

        $userId = auth()->id();

        if (! $userId) {
            return $this->errorResponse(
                dt('notifications.error'),
                401
            );
        }

        $user = User::find($userId);

        if (! $user) {
            return $this->errorResponse(
                dt('notifications.error'),
                404
            );
        }

        $payload = $this->settings->preloadFrontend($user);

        return $this->successResponse(
            $payload,
            dt('notifications.success')
        );
    }

    /**
     * Normalize runtime channel identifier.
     *
     * Supported:
     * - frontend
     * - backend
     */
    protected function normalizeChannel(
        mixed $channel
    ): ?string {

        return in_array(
            $channel,
            [
                SystemSetting::CHANNEL_FRONTEND,
                SystemSetting::CHANNEL_BACKEND,
            ],
            true
        )
            ? $channel
            : null;
    }

    protected function normalizeBooleanFilter(mixed $value): ?bool
    {
        if ($value === null || $value === '' || $value === 'all') {
            return null;
        }

        if ($value === true || $value === false) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = strtolower($value);
            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return null;
    }

    /**
     * @param array<string, array{label?: string|null, description?: string|null}> $translations
     */
    protected function persistSettingTranslations(string $settingKey, array $translations): void
    {
        if ($translations === []) {
            return;
        }

        $labels = [];
        $descriptions = [];

        foreach ($translations as $locale => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $labels[$locale] = isset($entry['label']) ? (string) $entry['label'] : null;
            $descriptions[$locale] = isset($entry['description']) ? (string) $entry['description'] : null;
        }

        $this->translationUpsert->saveTranslations('settings', $settingKey, $labels, true, true);
        $this->translationUpsert->saveTranslations('settings', $settingKey . '.description', $descriptions, true, true);
    }
}
