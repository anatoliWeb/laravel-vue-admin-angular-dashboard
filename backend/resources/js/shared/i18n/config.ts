export type LocaleCode = 'en' | 'uk' | 'de';

export interface LocaleConfigItem {
  code: LocaleCode;
  label: string;
  enabled: boolean;
}

/**
 * Centralized localization config.
 *
 * WHY CENTRALIZE:
 * Locale metadata (code, label, enabled) must live in one source of truth so
 * routing, switchers, and i18n bootstrap stay consistent across modules.
 *
 * WHY METADATA MATTERS:
 * Locale visibility is not just translation data; it is a product/policy layer.
 * This shape is intentionally ready for backend-driven user locale permissions.
 */
export const LOCALE_CONFIG: LocaleConfigItem[] = [
  { code: 'en', label: 'English', enabled: true },
  { code: 'uk', label: 'Українська', enabled: true },
  { code: 'de', label: 'Deutsch', enabled: true },
];

export const DEFAULT_LOCALE: LocaleCode = 'en';
export const FALLBACK_LOCALE: LocaleCode = 'en';
export const LOCALE_STORAGE_KEY = 'admin_locale';

