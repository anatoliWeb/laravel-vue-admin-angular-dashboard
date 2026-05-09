import { createI18n } from 'vue-i18n';

import { DEFAULT_LOCALE, FALLBACK_LOCALE, LOCALE_STORAGE_KEY, type LocaleCode } from './config';
import { getDefaultLocale, getEnabledLocales, isLocaleEnabled } from './helpers';
import enCommon from './locales/en/common';
import deCommon from './locales/de/common';
import ukCommon from './locales/uk/common';

export type SupportedLocale = LocaleCode;

/**
 * i18n foundation for admin frontend.
 *
 * WHY EARLY:
 * Introducing translation keys early avoids hardcoded UI text spread across
 * pages/components and keeps migration work language-neutral from day one.
 *
 * WHY CENTRALIZED:
 * A shared translation registry scales better across modules and aligns with
 * future multi-client needs (including Angular parity on vocabulary/contracts).
 *
 * WHY DYNAMIC LOCALES:
 * Frontend must render language controls from configuration/permissions, not
 * hardcoded buttons. This is essential for user-specific locale visibility.
 */
export const getStoredLocale = (): SupportedLocale => {
  const stored = window.localStorage.getItem(LOCALE_STORAGE_KEY);
  if (stored && isLocaleEnabled(stored)) {
    return stored;
  }

  return getDefaultLocale();
};

export const setStoredLocale = (locale: SupportedLocale): void => {
  window.localStorage.setItem(LOCALE_STORAGE_KEY, locale);
};

const localeMessages = {
  en: { common: enCommon },
  uk: { common: ukCommon },
  de: { common: deCommon },
} as const;

const enabledMessages = Object.fromEntries(
  getEnabledLocales().map((item) => [item.code, localeMessages[item.code]]),
) as Record<SupportedLocale, (typeof localeMessages)[SupportedLocale]>;

export const i18n = createI18n({
  legacy: false,
  locale: getStoredLocale(),
  fallbackLocale: FALLBACK_LOCALE,
  messages: enabledMessages,
});

export { getAvailableLocales, getEnabledLocales } from './helpers';
