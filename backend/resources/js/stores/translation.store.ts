import { nextTick } from 'vue'
import { defineStore } from 'pinia'

import { i18n, SupportedLocale, setStoredLocale } from '../shared/i18n'

import { translationService } from '../services/translation'
import { useGlobalLoadingStore } from './global-loading.store'

import type { DynamicTranslations } from '../services/translation'

interface TranslationState {
    locale: SupportedLocale
    translations: DynamicTranslations
    isLoaded: boolean
    isLoading: boolean
    pendingSwitchLocale: SupportedLocale | null
}

const normalizeGroupKey = (
    group: string,
    key: string
): string => {
    if (key.startsWith(`${group}.`)) {
        return key.slice(group.length + 1)
    }

    const singularGroup = group.endsWith('s')
        ? group.slice(0, -1)
        : group

    if (key.startsWith(`${singularGroup}.`)) {
        return key.slice(singularGroup.length + 1)
    }

    return key
}

const setNestedValue = (
    target: Record<string, unknown>,
    path: string,
    value: string
): void => {
    const segments = path.split('.').filter(Boolean)

    if (segments.length === 0) {
        return
    }

    let cursor: Record<string, unknown> = target

    for (let index = 0; index < segments.length - 1; index += 1) {
        const segment = segments[index]
        const next = cursor[segment]

        if (!next || typeof next !== 'object') {
            cursor[segment] = {}
        }

        cursor = cursor[segment] as Record<string, unknown>
    }

    const finalKey = segments[segments.length - 1]
    cursor[finalKey] = value
}

const normalizeGroupTranslations = (
    group: string,
    items: Record<string, string>
): Record<string, unknown> => {
    const normalized: Record<string, unknown> = {}

    Object.entries(items).forEach(([rawKey, rawValue]) => {
        const normalizedKey = normalizeGroupKey(group, rawKey)
        setNestedValue(normalized, normalizedKey, rawValue)
    })

    return normalized
}

export const useTranslationStore = defineStore(
    'translation',
    {
        state: (): TranslationState => ({
            locale: 'en',

            translations: {},

            isLoaded: false,
            isLoading: false,
            pendingSwitchLocale: null,
        }),

        actions: {

            /**
             * Load runtime translations from backend.
             */
            async loadTranslations(
                locale: SupportedLocale
            ): Promise<void> {

                if (this.isLoading) {
                    if (import.meta.env.DEV) {
                        console.debug('[i18n] loadTranslations skipped: already loading', {
                            requestedLocale: locale,
                            activeLocale: this.locale,
                        })
                    }
                    return
                }

                this.isLoading = true

                try {
                    if (import.meta.env.DEV) {
                        console.debug('[i18n] loadTranslations:start', {
                            requestedLocale: locale,
                            currentStoreLocale: this.locale,
                            currentI18nLocale: i18n.global.locale.value,
                        })
                    }

                    const response = await translationService
                        .load(locale)

                    /*
                    |--------------------------------------------------------------------------
                    | Backend payload
                    |--------------------------------------------------------------------------
                    */

                    const payload = response

                    if (!payload) {
                        if (import.meta.env.DEV) {
                            console.debug('[i18n] loadTranslations:empty-payload', { locale })
                        }
                        return
                    }

                    const resolvedLocale = payload.locale

                    this.translations = payload.translations

                    if (import.meta.env.DEV) {
                        console.debug('[i18n] payload', {
                            locale: resolvedLocale,
                            groups: Object.keys(payload.translations),
                            sampleRolesAdmin: payload.translations.roles?.admin
                                ?? payload.translations.roles?.['role.admin']
                                ?? null,
                        })
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Merge into vue-i18n runtime
                    |--------------------------------------------------------------------------
                    */

                    Object.entries(payload.translations).forEach(([group, items]) => {
                        const normalizedItems = normalizeGroupTranslations(group, items)

                        i18n.global.mergeLocaleMessage(
                            resolvedLocale,
                            {
                                [group]: normalizedItems,
                            }
                        )
                    })

                    /*
                    |--------------------------------------------------------------------------
                    | Activate locale
                    |--------------------------------------------------------------------------
                    */

                    i18n.global.locale.value = resolvedLocale
                    this.locale = resolvedLocale

                    this.isLoaded = true

                    if (import.meta.env.DEV) {
                        console.debug('[i18n] loadTranslations:done', {
                            requestedLocale: locale,
                            resolvedLocale,
                            mergedGroups: Object.keys(payload.translations),
                            activeI18nLocale: i18n.global.locale.value,
                            probeRoleAdmin: i18n.global.t('roles.admin'),
                        })
                    }

                } finally {

                    this.isLoading = false
                }
            },

            /**
             * Change active application locale.
             */
            async switchLocale(
                locale: SupportedLocale
            ): Promise<void> {

                /*
                |--------------------------------------------------------------------------
                | Avoid duplicate switching
                |--------------------------------------------------------------------------
                */

                if (this.locale === locale) {
                    if (import.meta.env.DEV) {
                        console.debug('[i18n] switchLocale skipped: same locale', {
                            locale,
                            storeLocale: this.locale,
                            i18nLocale: i18n.global.locale.value,
                        })
                    }
                    return
                }

                this.pendingSwitchLocale = locale
                const globalLoadingStore = useGlobalLoadingStore()
                const loadingToken = globalLoadingStore.begin(
                    'Switching language...',
                    'locale',
                    500,
                )

                /*
                |--------------------------------------------------------------------------
                | Load runtime translations
                |--------------------------------------------------------------------------
                */

                try {
                    await this.loadTranslations(locale)
                    await nextTick()

                    /*
                    |--------------------------------------------------------------------------
                    | Persist locale
                    |--------------------------------------------------------------------------
                    */

                    setStoredLocale(this.locale)

                    if (import.meta.env.DEV) {
                        console.debug('[i18n] switchLocale:done', {
                            requestedLocale: locale,
                            persistedLocale: this.locale,
                            activeI18nLocale: i18n.global.locale.value,
                        })
                    }
                } finally {
                    await globalLoadingStore.end(loadingToken)
                    this.pendingSwitchLocale = null
                }
            },
        },
    }
)
