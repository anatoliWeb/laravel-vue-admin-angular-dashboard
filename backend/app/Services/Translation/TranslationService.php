<?php

namespace App\Services\Translation;

use App\Models\SystemTranslation;

/**
 * Dynamic translation resolver service.
 *
 * WHY:
 * Provides unified runtime localization access
 * for database-driven translations.
 *
 * Responsibilities:
 * - translation resolution
 * - locale fallback
 * - auto-generation of missing keys
 * - Laravel fallback integration
 * - placeholder replacements
 * - centralized translation access
 *
 * IMPORTANT:
 * Static UI translations should still remain
 * inside Laravel/Vue language files.
 */
class TranslationService
{
    public function __construct(
        protected TranslationCacheService $cache
    ) {
    }

    /**
     * Resolve translated value.
     */
    public function get(
        string $fullKey,
        array $replace = [],
        ?string $locale = null
    ): string {

        $locale ??= app()->getLocale();

        /*
        |--------------------------------------------------------------------------
        | Parse translation namespace
        |--------------------------------------------------------------------------
        */

        [$group, $key] = $this->parseKey($fullKey);

        /*
        |--------------------------------------------------------------------------
        | Resolve current locale translation
        |--------------------------------------------------------------------------
        */

        $translation = $this->cache->get(
            locale: $locale,
            group: $group,
            key: $key
        );

        /*
        |--------------------------------------------------------------------------
        | Locale fallback
        |--------------------------------------------------------------------------
        */

        if (! $translation && $locale !== 'en') {

            $translation = $this->cache->get(
                locale: 'en',
                group: $group,
                key: $key
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Laravel static translation fallback
        |--------------------------------------------------------------------------
        */

        if (! $translation) {

            $fallback = __($fullKey, $replace);

            /*
            |--------------------------------------------------------------------------
            | Laravel translation exists
            |--------------------------------------------------------------------------
            */

            if ($fallback !== $fullKey) {
                return $fallback;
            }

            /*
            |--------------------------------------------------------------------------
            | Auto-generate missing translation
            |--------------------------------------------------------------------------
            */

            $this->createMissingTranslation(
                locale: $locale,
                group: $group,
                key: $key,
                value: $fullKey
            );

            return $fullKey;
        }

        /*
        |--------------------------------------------------------------------------
        | Replace placeholders
        |--------------------------------------------------------------------------
        */

        return strtr($translation, $replace);
    }

    /**
     * Auto-create missing translation entry.
     *
     * WHY:
     * Missing translations should become visible
     * inside admin translation management UI.
     */
    protected function createMissingTranslation(
        string $locale,
        string $group,
        string $key,
        string $value
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate creation
        |--------------------------------------------------------------------------
        */

        SystemTranslation::query()
            ->firstOrCreate(
                [
                    'locale' => $locale,
                    'group' => $group,
                    'key' => $key,
                ],
                [
                    'value' => $value,

                    'source' => 'auto-generated',

                    'is_frontend' => true,
                    'is_backend' => true,

                    'is_system' => false,
                    'is_active' => true,

                    'is_auto_generated' => true,
                    'is_translated' => false,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Clear cache for newly created translation
        |--------------------------------------------------------------------------
        */

        $this->cache->forget(
            locale: $locale,
            group: $group,
            key: $key
        );
    }

    /**
     * Parse translation namespace.
     *
     * Example:
     * roles.admin
     *
     * becomes:
     * [roles, admin]
     */
    protected function parseKey(string $fullKey): array
    {
        $parts = explode('.', $fullKey, 2);

        return [
            $parts[0] ?? 'general',
            $parts[1] ?? $fullKey,
        ];
    }
}
