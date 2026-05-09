import { createI18n } from 'vue-i18n';

import enCommon from './locales/en/common';
import ukCommon from './locales/uk/common';

const LOCALE_STORAGE_KEY = 'admin_locale';
const DEFAULT_LOCALE = 'en';

export type SupportedLocale = 'en' | 'uk';

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
 */
export const getStoredLocale = (): SupportedLocale => {
  const stored = window.localStorage.getItem(LOCALE_STORAGE_KEY);
  return stored === 'uk' ? 'uk' : DEFAULT_LOCALE;
};

export const setStoredLocale = (locale: SupportedLocale): void => {
  window.localStorage.setItem(LOCALE_STORAGE_KEY, locale);
};

export const i18n = createI18n({
  legacy: false,
  locale: getStoredLocale(),
  fallbackLocale: DEFAULT_LOCALE,
  messages: {
    en: { common: enCommon },
    uk: { common: ukCommon },
  },
});

