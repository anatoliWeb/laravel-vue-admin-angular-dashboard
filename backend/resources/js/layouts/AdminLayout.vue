<template>
  <section :class="['admin-layout', { 'is-sidebar-collapsed': isSidebarCollapsed }]">
    <aside class="admin-sidebar">
      <header class="admin-sidebar__header">
        <router-link class="admin-brand" to="/dashboard">
          <span class="admin-brand__dot" />
          <span class="admin-brand__name">SaaS Admin</span>
        </router-link>

        <button
          type="button"
          class="admin-sidebar__toggle"
          :aria-label="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
          @click="isSidebarCollapsed = !isSidebarCollapsed"
        >
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
      </header>

      <nav class="admin-sidebar__nav">
        <section class="admin-sidebar__section">
          <h2 class="admin-sidebar__heading">Overview</h2>
          <router-link class="admin-sidebar__link" to="/dashboard"><IconGrid /><span class="admin-sidebar__label">Dashboard</span></router-link>
        </section>

        <section class="admin-sidebar__section">
          <h2 class="admin-sidebar__heading">Management</h2>
          <router-link class="admin-sidebar__link" to="/users"><IconUsers /><span class="admin-sidebar__label">Users</span></router-link>
          <router-link class="admin-sidebar__link" to="/roles"><IconShield /><span class="admin-sidebar__label">Roles</span></router-link>
          <router-link class="admin-sidebar__link" to="/permissions"><IconKey /><span class="admin-sidebar__label">Permissions</span></router-link>
        </section>

        <section class="admin-sidebar__section">
          <h2 class="admin-sidebar__heading">API</h2>
          <router-link class="admin-sidebar__link" to="/tokens"><IconToken /><span class="admin-sidebar__label">Tokens</span></router-link>
        </section>
      </nav>
    </aside>

    <main class="admin-shell-main">
      <header class="admin-topbar">
        <div class="topbar-shell">
          <div class="topbar-shell__left">
            <h1 class="page-title">{{ pageTitle }}</h1>
            <p class="page-subtitle">Admin / {{ pageTitle }}</p>
          </div>

          <div class="topbar-shell__center">
            <BaseTopbarSearch />
          </div>

          <div class="topbar-shell__right">
            <div class="topbar-shell__metrics" aria-label="Realtime counters">
              <BaseRealtimeStatus
                v-for="metric in realtimeMetrics"
                :key="metric.key"
                :label="metric.label"
                :count="metric.count"
                :active="metric.active"
              />
            </div>

            <div class="topbar-shell__status" aria-label="System status actions">
              <BaseIconButton title="Notifications">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22zm6-6v-5a6 6 0 1 0-12 0v5l-2 2v1h16v-1l-2-2z" /></svg>
              </BaseIconButton>
              <BaseIconButton title="Messages">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H8l-4 3v-3H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm1.8 4.5 6.2 4.1 6.2-4.1" /></svg>
              </BaseIconButton>
              <BaseIconButton title="Realtime status" :active="true">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4a8 8 0 1 0 8 8h-2a6 6 0 1 1-6-6V4zm1 0v7h7A7 7 0 0 0 13 4z" /></svg>
              </BaseIconButton>
              <BaseIconButton title="Queue status" :active="true">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v3H4V6zm0 5h16v3H4v-3zm0 5h10v3H4v-3z" /></svg>
              </BaseIconButton>
            </div>

            <BaseLanguageSwitcher v-model="selectedLocale" :locales="enabledLocales" />
            <BaseUserDropdown :name="userName" />
          </div>
        </div>
      </header>

      <section class="admin-shell-content">
        <router-view />
      </section>
    </main>
  </section>
</template>

<script setup lang="ts">
import { computed, defineComponent, h, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';

import BaseIconButton from '../shared/components/ui/BaseIconButton.vue';
import BaseLanguageSwitcher from '../shared/components/ui/BaseLanguageSwitcher.vue';
import BaseRealtimeStatus from '../shared/components/ui/BaseRealtimeStatus.vue';
import BaseTopbarSearch from '../shared/components/ui/BaseTopbarSearch.vue';
import BaseUserDropdown from '../shared/components/ui/BaseUserDropdown.vue';
import { getEnabledLocales, setStoredLocale } from '../shared/i18n';
import type { LocaleCode } from '../shared/i18n/config';
import { realtimeClient } from '../shared/services/realtime/realtime.client';
import type { RealtimeStatusMetric } from '../shared/services/realtime/realtime.types';

/**
 * Application shell composition for gradual migration.
 *
 * WHY THIS MATTERS:
 * - topbar is a shell-level navigation surface, not a dashboard widget
 * - separating topbar from page content keeps layout predictable at scale
 * - realtime counters are isolated as reusable components ready for Reverb data
 */
const route = useRoute();
const { locale } = useI18n();

const isSidebarCollapsed = ref(false);
const enabledLocales = getEnabledLocales();
const userName = 'Admin User';
const realtimeMetrics = ref<RealtimeStatusMetric[]>([]);

const selectedLocale = computed<LocaleCode>({
  get: () => locale.value as LocaleCode,
  set: (value) => {
    locale.value = value;
    setStoredLocale(value);
  },
});

const pageTitle = computed(() => (route.meta.title as string | undefined) ?? 'Admin');

onMounted(() => {
  realtimeClient.connect();
  realtimeMetrics.value = realtimeClient.getMockMetrics();
});

const iconClass = 'w-4 h-4';
const IconGrid = defineIcon('M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z');
const IconUsers = defineIcon('M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4zM8 12a3 3 0 1 0-3-3 3 3 0 0 0 3 3zM16 13c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4zM8 14c-.29 0-.62.02-.97.05C5.31 14.23 2 15.1 2 17v3h4v-3c0-1.1.58-2.07 1.55-2.78A8.4 8.4 0 0 1 8 14z');
const IconShield = defineIcon('M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3z');
const IconKey = defineIcon('M7 14a5 5 0 1 1 4.9 4H10v3H7v-3H4v-3h3.1A5 5 0 0 1 7 14zm10-2h2v2h-2v2h-2v-2h-2v-2h2v-2h2v2z');
const IconToken = defineIcon('M12 2 3 7v10l9 5 9-5V7l-9-5zm0 2.2 6.8 3.8L12 11.8 5.2 8 12 4.2zm-7 5.5 6 3.4v7.4l-6-3.3V9.7zm8 10.8v-7.4l6-3.4v7.5l-6 3.3z');

function defineIcon(path: string) {
  return defineComponent({
    setup() {
      return () => h('svg', { viewBox: '0 0 24 24', class: iconClass }, [h('path', { d: path })]);
    },
  });
}
</script>

<style scoped>
svg {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.admin-shell-main {
  margin-left: var(--sidebar-width);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.2s ease;
}

.admin-topbar {
  position: sticky;
  top: 0;
  z-index: 20;
  border-bottom: 1px solid rgba(71, 85, 105, 0.45);
  background: rgba(15, 23, 42, 0.94);
  backdrop-filter: blur(10px);
}

.topbar-shell {
  min-height: 64px;
  padding: 12px 16px;
  display: grid;
  grid-template-columns: minmax(190px, 1fr) minmax(240px, 1.3fr) auto;
  align-items: center;
  gap: 12px;
}

.topbar-shell__left {
  min-width: 0;
}

.topbar-shell__center {
  min-width: 0;
}

.topbar-shell__right {
  display: inline-flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}

.topbar-shell__metrics,
.topbar-shell__status {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.admin-shell-content {
  flex: 1;
  padding: 16px;
}

@media (max-width: 1180px) {
  .topbar-shell {
    grid-template-columns: minmax(180px, 1fr) auto;
  }

  .topbar-shell__center {
    grid-column: 1 / -1;
    grid-row: 2;
  }
}

@media (max-width: 860px) {
  .topbar-shell {
    grid-template-columns: 1fr;
  }

  .topbar-shell__right {
    justify-content: flex-start;
    flex-wrap: wrap;
  }

  .admin-shell-content {
    padding: 12px;
  }
}
</style>
