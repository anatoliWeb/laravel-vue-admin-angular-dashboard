<?php

namespace App\Services\Localization;

use App\Models\Permission;
use App\Models\Role;
use App\Services\Translation\TranslationService;

/**
 * RBAC localization facade for presentation-layer labels/descriptions.
 *
 * WHY THIS SERVICE EXISTS:
 * - keeps technical identifiers immutable (`roles.name`, `permissions.name`)
 * - centralizes translation key conventions and fallback behavior
 * - prevents duplicated translation logic across resources/controllers
 * - keeps API contracts scalable for multilingual frontends
 */
class RbacLocalizationService
{
    public function __construct(
        protected TranslationService $translations
    ) {
    }

    public function getRoleLabel(Role $role, ?string $locale = null): string
    {
        return $this->resolve(
            key: 'roles.' . $role->name,
            fallback: $role->name,
            locale: $locale
        );
    }

    public function getRoleDescription(Role $role, ?string $locale = null): ?string
    {
        return $this->resolveNullable(
            key: 'role_descriptions.' . $role->name,
            fallback: $role->description,
            locale: $locale
        );
    }

    /**
     * @return array<string, array{label: string, description: string|null}>
     */
    public function getRoleTranslations(Role $role): array
    {
        $translations = [];

        foreach ($this->supportedLocales() as $locale) {
            $translations[$locale] = [
                'label' => $this->getRoleLabel($role, $locale),
                'description' => $this->getRoleDescription($role, $locale),
            ];
        }

        return $translations;
    }

    public function getPermissionLabel(Permission $permission, ?string $locale = null): string
    {
        return $this->resolve(
            key: 'permissions.' . $permission->name,
            fallback: $permission->name,
            locale: $locale
        );
    }

    public function getPermissionDescription(Permission $permission, ?string $locale = null): ?string
    {
        return $this->resolveNullable(
            key: 'permission_descriptions.' . $permission->name,
            fallback: $permission->description,
            locale: $locale
        );
    }

    /**
     * @return array<string, array{label: string, description: string|null}>
     */
    public function getPermissionTranslations(Permission $permission): array
    {
        $translations = [];

        foreach ($this->supportedLocales() as $locale) {
            $translations[$locale] = [
                'label' => $this->getPermissionLabel($permission, $locale),
                'description' => $this->getPermissionDescription($permission, $locale),
            ];
        }

        return $translations;
    }

    protected function resolve(string $key, string $fallback, ?string $locale = null): string
    {
        $translated = $this->translations->get(
            fullKey: $key,
            locale: $locale
        );

        return $translated === $key ? $fallback : $translated;
    }

    protected function resolveNullable(string $key, ?string $fallback, ?string $locale = null): ?string
    {
        $translated = $this->translations->get(
            fullKey: $key,
            locale: $locale
        );

        if ($translated === $key) {
            return $fallback;
        }

        return $translated;
    }

    /**
     * @return array<int, string>
     */
    protected function supportedLocales(): array
    {
        /** @var mixed $locales */
        $locales = config('app.supported_locales', ['en', 'uk', 'de']);

        if (!is_array($locales) || $locales === []) {
            return ['en'];
        }

        return array_values(array_filter($locales, fn ($locale) => is_string($locale) && $locale !== ''));
    }
}
