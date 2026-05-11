<?php


namespace App\Services\Translation;

use App\Models\SystemTranslation;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    public function get(
        string  $key,
        ?string $locale = null,
        ?string $group = 'general'
    ): string
    {
        $locale ??= app()->getLocale();

        $cacheKey = sprintf(
            'translations.%s.%s.%s',
            $locale,
            $group,
            $key
        );

        return Cache::rememberForever($cacheKey, function () use (
            $locale,
            $group,
            $key
        ) {

            $translation = SystemTranslation::query()
                ->where('locale', $locale)
                ->where('group', $group)
                ->where('key', $key)
                ->where('is_active', true)
                ->value('value');

            if ($translation) {
                return $translation;
            }

            /*
            |--------------------------------------------------------------------------
            | Fallback to Laravel static translations
            |--------------------------------------------------------------------------
            */

            return __($key);
        });
    }
}
