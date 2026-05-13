<?php

namespace App\Http\Resources;

use App\Models\SystemSetting;
use App\Services\SettingsResolverService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * System setting API transformer.
 *
 * WHY RESOURCE LAYER IS IMPORTANT HERE:
 * Settings are a long-lived contract shared by multiple frontends. This
 * resource explicitly controls output fields so internal model evolution does
 * not leak unstable data to clients.
 */
class SystemSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SystemSetting $setting */
        $setting = $this->resource;
        /** @var SettingsResolverService $resolver */
        $resolver = app(SettingsResolverService::class);

        return [
            'id' => $setting->id,
            'key' => $setting->key,
            'label' => $this->resolveLabel($setting),
            'group' => $setting->group,
            'description' => $this->resolveDescription($setting),
            'translation_key' => $this->resolveTranslationKey($setting),
            'type' => $setting->type,
            'value' => $resolver->castValue($setting->value, $setting->type),
            'default_value' => $resolver->castValue($setting->default_value, $setting->type),
            'is_frontend' => $setting->is_frontend,
            'is_backend' => $setting->is_backend,
            'is_public' => $setting->is_public,
            'is_encrypted' => $setting->is_encrypted,
            'priority' => $setting->priority,
            'is_active' => $setting->is_active,
            'is_system' => $setting->is_system,
            'scope' => [
                'type' => $this->scopeType($setting),
                'user_id' => $setting->scope_user_id,
                'role_id' => $setting->scope_role_id,
                'permission_id' => $setting->scope_permission_id,
                'user' => $setting->scopeUser ? [
                    'id' => $setting->scopeUser->id,
                    'name' => $setting->scopeUser->name,
                ] : null,
                'role' => $setting->scopeRole ? [
                    'id' => $setting->scopeRole->id,
                    'name' => $setting->scopeRole->name,
                ] : null,
                'permission' => $setting->scopePermission ? [
                    'id' => $setting->scopePermission->id,
                    'name' => $setting->scopePermission->name,
                ] : null,
            ],
            'created_at' => $setting->created_at?->toISOString(),
            'updated_at' => $setting->updated_at?->toISOString(),
        ];
    }

    protected function resolveLabel(SystemSetting $setting): string
    {
        $translationKey = $this->resolveTranslationKey($setting);
        $translated = dt($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return (string) $setting->label;
    }

    protected function resolveDescription(SystemSetting $setting): ?string
    {
        $descriptionKey = $this->resolveTranslationKey($setting) . '.description';
        $translated = dt($descriptionKey);

        if ($translated !== $descriptionKey) {
            return $translated;
        }

        return $setting->description;
    }

    protected function resolveTranslationKey(SystemSetting $setting): string
    {
        $explicit = data_get($setting->getAttributes(), 'translation_key');
        if (is_string($explicit) && $explicit !== '') {
            return $explicit;
        }

        return 'settings.' . $setting->key;
    }

    protected function scopeType(SystemSetting $setting): string
    {
        if ($setting->scope_user_id !== null) {
            return 'user';
        }
        if ($setting->scope_permission_id !== null) {
            return 'permission';
        }
        if ($setting->scope_role_id !== null) {
            return 'role';
        }
        return 'global';
    }
}
