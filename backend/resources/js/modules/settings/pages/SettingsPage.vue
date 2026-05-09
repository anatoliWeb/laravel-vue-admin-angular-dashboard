<template>
  <section class="settings-page">
    <header class="settings-page__header c-card">
      <div>
        <h2 class="settings-page__title">Platform Settings</h2>
        <p class="settings-page__subtitle">Manage platform defaults, security controls, feature flags, and runtime configuration.</p>
      </div>
      <div class="settings-page__actions">
        <span class="settings-page__dirty" :class="{ 'is-active': isDirty }">{{ isDirty ? 'Unsaved changes' : 'All changes saved' }}</span>
        <button type="button" class="settings-page__btn" :disabled="!isDirty || !canEdit" @click="resetChanges">Cancel</button>
        <button type="button" class="settings-page__btn settings-page__btn--primary" :disabled="!isDirty || !canEdit" @click="saveChanges">Save changes</button>
      </div>
    </header>

    <BaseErrorState v-if="errorMessage" title="Failed to load settings" :description="errorMessage" />

    <div v-else class="settings-grid">
      <SettingsGroup
        v-for="section in editableSections"
        :key="section.id"
        :title="section.title"
        :description="section.description"
      >
        <SettingsRow
          v-for="field in section.fields"
          :key="field.key"
          :label="field.label"
          :description="field.description"
          :editable="canEdit && field.editable !== false"
        >
          <template v-if="field.type === 'toggle'">
            <SettingsToggle
              :model-value="Boolean(field.value)"
              :disabled="!canEdit || field.editable === false"
              @update:model-value="(value) => updateField(section.id, field.key, value)"
            />
          </template>

          <template v-else-if="field.type === 'select'">
            <BaseDropdown>
              <template #trigger="{ isOpen }">
                <button type="button" class="settings-page__select-trigger" :class="{ 'is-open': isOpen }" :disabled="!canEdit || field.editable === false">
                  <span>{{ selectedLabel(field) }}</span>
                  <span class="settings-page__select-caret">{{ isOpen ? '^' : 'v' }}</span>
                </button>
              </template>
              <template #default="{ close }">
                <button
                  v-for="option in field.options || []"
                  :key="option.value"
                  type="button"
                  class="settings-page__select-option"
                  :class="{ 'is-active': String(field.value) === option.value }"
                  @click="selectOption(section.id, field.key, option.value, close)"
                >
                  {{ option.label }}
                </button>
              </template>
            </BaseDropdown>
          </template>

          <template v-else>
            <input
              :type="field.type === 'number' ? 'number' : 'text'"
              class="settings-page__input"
              :value="String(field.value)"
              :disabled="!canEdit || field.editable === false"
              @input="onInput(section.id, field.key, field.type, $event)"
            />
          </template>
        </SettingsRow>
      </SettingsGroup>

      <section class="settings-page__status c-card">
        <h3 class="settings-page__status-title">System Status</h3>
        <div class="settings-page__status-grid">
          <div v-for="item in systemStatus" :key="item.label" class="settings-page__status-item">
            <span class="settings-page__status-dot" :class="{ 'is-ok': item.ok }" />
            <div>
              <div class="settings-page__status-label">{{ item.label }}</div>
              <div class="settings-page__status-value">{{ item.value }}</div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import SettingsGroup from '../components/SettingsGroup.vue';
import SettingsRow from '../components/SettingsRow.vue';
import SettingsToggle from '../components/SettingsToggle.vue';
import { settingsService } from '../services/settings.service';
import type { SettingField, SettingsSection } from '../types/settings.types';
import BaseDropdown from '../../../shared/components/ui/BaseDropdown.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import { api } from '../../../services/api/client';

/**
 * Enterprise settings architecture.
 *
 * WHY THIS STRUCTURE:
 * - section-based groups scale for tenant/system/org settings
 * - feature flags are first-class configuration fields
 * - dirty-state + save/cancel flow establishes safe mutation UX before
 *   backend persistence endpoints are finalized
 */
const sections = ref<SettingsSection[]>([]);
const originalSnapshot = ref('');
const canEdit = ref(false);
const canView = ref(true);
const errorMessage = ref('');

const editableSections = computed(() => sections.value.filter((section) => section.id !== 'system'));

const systemStatus = computed(() => {
  const systemSection = sections.value.find((section) => section.id === 'system');
  return (systemSection?.fields ?? []).map((field) => ({
    label: field.label,
    value: String(field.value),
    ok: String(field.value).toLowerCase().includes('online') || String(field.value).toLowerCase().includes('ready') || String(field.value).toLowerCase().includes('healthy') || String(field.value).toLowerCase().includes('connected'),
  }));
});

const isDirty = computed(() => JSON.stringify(sections.value) !== originalSnapshot.value);

const loadSettings = async (): Promise<void> => {
  try {
    errorMessage.value = '';

    const meta = await api.get<{ current_user_permissions?: string[] }>('/v1/meta');
    const permissions = meta.data?.current_user_permissions ?? [];

    const payload = await settingsService.fetchSettings(permissions);
    sections.value = payload.sections;
    canView.value = payload.canView;
    canEdit.value = payload.canEdit;
    originalSnapshot.value = JSON.stringify(payload.sections);

    if (!canView.value) {
      errorMessage.value = 'You do not have permission to view settings.';
    }
  } catch (error) {
    errorMessage.value = (error as { message?: string })?.message ?? 'Unable to load settings.';
  }
};

const updateField = (sectionId: SettingsSection['id'], key: string, value: string | number | boolean): void => {
  sections.value = sections.value.map((section) => {
    if (section.id !== sectionId) return section;

    return {
      ...section,
      fields: section.fields.map((field) => (field.key === key ? { ...field, value } : field)),
    };
  });
};

const selectedLabel = (field: SettingField): string => {
  const option = field.options?.find((item) => item.value === String(field.value));
  return option?.label ?? String(field.value);
};

const selectOption = (sectionId: SettingsSection['id'], key: string, value: string, close: () => void): void => {
  updateField(sectionId, key, value);
  close();
};

const onInput = (sectionId: SettingsSection['id'], key: string, type: SettingField['type'], event: Event): void => {
  const rawValue = (event.target as HTMLInputElement).value;
  updateField(sectionId, key, type === 'number' ? Number(rawValue) : rawValue);
};

const resetChanges = (): void => {
  sections.value = JSON.parse(originalSnapshot.value) as SettingsSection[];
};

const saveChanges = (): void => {
  // Placeholder for async persistence flow.
  originalSnapshot.value = JSON.stringify(sections.value);
  // eslint-disable-next-line no-alert
  window.alert('Settings persistence endpoint will be connected in next phase.');
};

onMounted(() => {
  loadSettings();
});
</script>

<style scoped>
.settings-page{display:grid;gap:12px}
.settings-page__header{margin-top:0;display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.settings-page__title{margin:0;font-size:18px;color:#f8fafc}
.settings-page__subtitle{margin:6px 0 0;color:#94a3b8;font-size:13px}
.settings-page__actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.settings-page__dirty{font-size:11px;color:#94a3b8}
.settings-page__dirty.is-active{color:#fcd34d}
.settings-page__btn{height:32px;border-radius:8px;border:1px solid rgba(71,85,105,.55);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 11px;font-size:12px}
.settings-page__btn:disabled{opacity:.55;cursor:not-allowed}
.settings-page__btn--primary{border-color:rgba(59,130,246,.55);background:rgba(59,130,246,.2);color:#bfdbfe}
.settings-grid{display:grid;grid-template-columns:2fr 1fr;gap:12px}
.settings-page__select-trigger{width:100%;height:34px;border-radius:8px;border:1px solid rgba(71,85,105,.6);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 10px;font-size:12px;display:inline-flex;align-items:center;justify-content:space-between;gap:8px}
.settings-page__select-trigger:hover,.settings-page__select-trigger.is-open{border-color:rgba(96,165,250,.5);background:rgba(51,65,85,.8)}
.settings-page__select-trigger:disabled{opacity:.55;cursor:not-allowed}
.settings-page__select-caret{color:#94a3b8;font-size:11px}
.settings-page__select-option{width:100%;text-align:left;border:0;border-radius:7px;background:transparent;color:#e2e8f0;padding:8px 10px;font-size:12px}
.settings-page__select-option:hover{background:rgba(51,65,85,.75)}
.settings-page__select-option.is-active{background:rgba(51,65,85,.95);color:#fff;font-weight:700}
.settings-page__input{width:100%;height:34px;border-radius:8px;border:1px solid rgba(71,85,105,.6);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 10px;font-size:12px}
.settings-page__status{margin-top:0;display:grid;gap:10px;align-content:start}
.settings-page__status-title{margin:0;color:#f8fafc;font-size:15px}
.settings-page__status-grid{display:grid;gap:8px}
.settings-page__status-item{display:flex;align-items:center;gap:8px;padding:8px;border:1px solid rgba(71,85,105,.45);border-radius:10px;background:rgba(15,23,42,.45)}
.settings-page__status-dot{width:9px;height:9px;border-radius:999px;background:#f59e0b}
.settings-page__status-dot.is-ok{background:#22c55e}
.settings-page__status-label{color:#94a3b8;font-size:11px}
.settings-page__status-value{color:#e2e8f0;font-size:12px}
@media (max-width:1100px){.settings-grid{grid-template-columns:1fr}.settings-page__actions{justify-content:flex-start}}
@media (max-width:760px){.settings-page__header{flex-direction:column}}
</style>
