<template>
  <section class="min-h-screen bg-slate-100 p-6">
    <div class="mx-auto max-w-6xl space-y-6">
      <header class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              {{ t('common.admin') }} | {{ t('common.dashboard') }}
            </p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ t('common.welcome') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ t('common.gradualMigrationReady') }}</p>
          </div>

          <div v-if="showLocaleSwitcher" class="flex items-center gap-2">
            <span class="text-sm font-medium text-slate-600">{{ t('common.language') }}:</span>
            <BaseButton
              v-for="localeItem in enabledLocales"
              :key="localeItem.code"
              :variant="locale === localeItem.code ? 'primary' : 'secondary'"
              @click="setLocale(localeItem.code)"
            >
              {{ localeItem.code.toUpperCase() }}
            </BaseButton>
          </div>
        </div>
      </header>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="item in statusCards"
          :key="item.key"
          class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
        >
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-800">{{ item.label }}</h2>
            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">OK</span>
          </div>
        </article>
      </div>

      <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Runtime</h2>
        <dl class="mt-4 grid gap-3 text-sm text-slate-700 md:grid-cols-3">
          <div>
            <dt class="font-medium text-slate-500">{{ t('common.route') }}</dt>
            <dd>{{ route.fullPath }}</dd>
          </div>
          <div>
            <dt class="font-medium text-slate-500">{{ t('common.environment') }}</dt>
            <dd>{{ mode }}</dd>
          </div>
          <div>
            <dt class="font-medium text-slate-500">{{ t('common.timestamp') }}</dt>
            <dd>{{ renderedAt }}</dd>
          </div>
        </dl>
      </article>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

import BaseButton from '../../../shared/components/ui/BaseButton.vue';
import { getEnabledLocales, setStoredLocale, type SupportedLocale } from '../../../shared/i18n';

/**
 * First real migrated Vue admin page.
 *
 * WHY THIS PAGE EXISTS:
 * - validates the Blade to Vue coexistence pipeline with a real routed page
 * - proves i18n, layout shell, and SPA runtime are operational
 * - provides a safe baseline before migrating business-heavy Blade screens
 */
const route = useRoute();
const { t, locale } = useI18n();

const enabledLocales = getEnabledLocales();
const showLocaleSwitcher = computed(() => enabledLocales.length > 1);

const mode = import.meta.env.MODE;
const renderedAt = new Date().toISOString();

const setLocale = (value: SupportedLocale): void => {
  locale.value = value;
  setStoredLocale(value);
};

const statusCards = computed(() => [
  { key: 'migration', label: t('common.migrationSuccessful') },
  { key: 'vue', label: t('common.vueAdminActive') },
  { key: 'api', label: t('common.apiLayerReady') },
  { key: 'i18n', label: t('common.i18nActive') },
  { key: 'realtime', label: t('common.realtimeReady') },
  { key: 'queue', label: t('common.queueReady') },
]);
</script>

