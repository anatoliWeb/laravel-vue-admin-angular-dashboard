<template>
  <section class="settings-page">
    <header class="settings-page__header c-card">
      <div>
        <h2 class="settings-page__title">Settings Architecture</h2>
        <p class="settings-page__subtitle">Hierarchical platform settings with deterministic global/role/permission/user overrides.</p>
      </div>
      <div class="settings-page__actions">
        <input v-model.trim="search" type="search" class="settings-page__search" placeholder="Search key, label, description" @input="onSearch" />
        <button type="button" class="settings-page__btn settings-page__btn--primary" @click="startCreate">Create setting</button>
      </div>
    </header>

    <BaseErrorState v-if="errorMessage" title="Failed to load settings" :description="errorMessage" />

    <div v-else class="settings-page__layout">
      <article class="c-card settings-table-card">
        <header class="settings-table-card__header">
          <div>
            <h3 class="settings-table-card__title">Settings Registry</h3>
            <p class="settings-table-card__subtitle">Effective source shows why a value is active for current user context.</p>
          </div>
          <select v-model="selectedGroup" class="settings-page__select" @change="loadSettings">
            <option value="">All groups</option>
            <option v-for="group in groups" :key="group" :value="group">{{ group }}</option>
          </select>
        </header>

        <div class="settings-table-wrap">
          <table class="settings-table">
            <thead>
              <tr>
                <th>Key</th>
                <th>Group</th>
                <th>Scope</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Effective</th>
                <th>Source</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in settings" :key="item.id">
                <td>
                  <div class="settings-table__primary">{{ item.label }}</div>
                  <div class="settings-table__secondary">{{ item.key }}</div>
                </td>
                <td>{{ item.group }}</td>
                <td>{{ scopeLabel(item) }}</td>
                <td>{{ item.type }}</td>
                <td>{{ item.priority }}</td>
                <td>{{ stringify(effective[item.key]?.value ?? item.value) }}</td>
                <td>
                  <span class="scope-badge" :class="`is-${effective[item.key]?.source ?? 'global'}`">{{ effective[item.key]?.source ?? 'global' }}</span>
                </td>
                <td>
                  <button type="button" class="settings-page__icon-btn" @click="startEdit(item)">Edit</button>
                  <button type="button" class="settings-page__icon-btn is-danger" @click="remove(item.id)">Delete</button>
                </td>
              </tr>
              <tr v-if="settings.length === 0">
                <td colspan="8" class="settings-table__empty">No settings found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <article class="c-card settings-editor">
        <h3 class="settings-editor__title">{{ isEditMode ? 'Edit setting' : 'Create setting' }}</h3>
        <p class="settings-editor__subtitle">Priority preview: user > permission > role > global. Higher scope wins, then priority, then latest record.</p>

        <form class="settings-editor__form" @submit.prevent="save">
          <label class="settings-editor__label">
            Key
            <input v-model.trim="form.key" class="settings-editor__input" required />
          </label>
          <label class="settings-editor__label">
            Label
            <input v-model.trim="form.label" class="settings-editor__input" required />
          </label>
          <label class="settings-editor__label">
            Group
            <input v-model.trim="form.group" class="settings-editor__input" required />
          </label>
          <label class="settings-editor__label">
            Type
            <select v-model="form.type" class="settings-editor__input">
              <option v-for="type in settingTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </label>
          <label class="settings-editor__label settings-editor__label--full">
            Description
            <textarea v-model.trim="form.description" class="settings-editor__textarea" rows="2" />
          </label>
          <label class="settings-editor__label">
            Value
            <input v-model="form.value" class="settings-editor__input" />
          </label>
          <label class="settings-editor__label">
            Default Value
            <input v-model="form.default_value" class="settings-editor__input" />
          </label>
          <label class="settings-editor__label">
            Priority
            <input v-model.number="form.priority" type="number" min="0" class="settings-editor__input" />
          </label>
          <label class="settings-editor__label">
            Scope Type
            <select v-model="scopeType" class="settings-editor__input" @change="applyScopeType">
              <option value="global">Global</option>
              <option value="role">Role</option>
              <option value="permission">Permission</option>
              <option value="user">User</option>
            </select>
          </label>

          <label v-if="scopeType === 'role'" class="settings-editor__label settings-editor__label--full">
            Role Scope
            <select v-model.number="form.scope_role_id" class="settings-editor__input">
              <option :value="null">Select role</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.label || role.name }}</option>
            </select>
          </label>

          <label v-if="scopeType === 'permission'" class="settings-editor__label settings-editor__label--full">
            Permission Scope
            <select v-model.number="form.scope_permission_id" class="settings-editor__input">
              <option :value="null">Select permission</option>
              <option v-for="permission in permissions" :key="permission.id" :value="permission.id">{{ permission.label || permission.name }}</option>
            </select>
          </label>

          <label v-if="scopeType === 'user'" class="settings-editor__label settings-editor__label--full">
            User Scope
            <select v-model.number="form.scope_user_id" class="settings-editor__input">
              <option :value="null">Select user</option>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.email }})</option>
            </select>
          </label>

          <label class="settings-editor__check"><input v-model="form.is_frontend" type="checkbox" /> Frontend</label>
          <label class="settings-editor__check"><input v-model="form.is_backend" type="checkbox" /> Backend</label>
          <label class="settings-editor__check"><input v-model="form.is_active" type="checkbox" /> Active</label>
          <label class="settings-editor__check"><input v-model="form.is_system" type="checkbox" /> System</label>

          <div class="settings-editor__footer">
            <button type="button" class="settings-page__btn" @click="resetForm">Reset</button>
            <button type="submit" class="settings-page__btn settings-page__btn--primary">{{ isEditMode ? 'Update' : 'Create' }}</button>
          </div>
        </form>
      </article>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';

import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import { api } from '../../../services/api/client';
import { settingsService } from '../services/settings.service';
import type {
  EffectiveSetting,
  SettingsIndexPayload,
  SettingScopeType,
  SettingValueType,
  SystemSettingRecord,
  UpsertSettingPayload,
} from '../types/settings.types';

interface MetaRef {
  id: number;
  name: string;
  label?: string;
}

interface UserOption {
  id: number;
  name: string;
  email: string;
}

/**
 * Settings admin page uses one editor form for create/update to keep workflow
 * predictable while backend inheritance remains the single source of truth.
 */
const settings = ref<SystemSettingRecord[]>([]);
const effective = ref<Record<string, EffectiveSetting>>({});
const groups = ref<string[]>([]);
const settingTypes = ref<SettingValueType[]>(['string', 'integer', 'number', 'boolean', 'json', 'array', 'enum', 'color', 'select', 'textarea', 'toggle']);
const roles = ref<MetaRef[]>([]);
const permissions = ref<MetaRef[]>([]);
const users = ref<UserOption[]>([]);
const errorMessage = ref('');
const selectedGroup = ref('');
const search = ref('');
const editingId = ref<number | null>(null);

const form = reactive<UpsertSettingPayload>({
  key: '',
  label: '',
  group: 'general',
  description: '',
  type: 'string',
  value: '',
  default_value: '',
  is_frontend: true,
  is_backend: true,
  priority: 100,
  is_active: true,
  is_system: false,
  scope_user_id: null,
  scope_role_id: null,
  scope_permission_id: null,
});

const isEditMode = computed(() => editingId.value !== null);

const scopeType = ref<SettingScopeType>('global');

const applyScopeType = (): void => {
  if (scopeType.value !== 'user') form.scope_user_id = null;
  if (scopeType.value !== 'role') form.scope_role_id = null;
  if (scopeType.value !== 'permission') form.scope_permission_id = null;
};

const parseEditableValue = (value: unknown): string => {
  if (typeof value === 'string') return value;
  if (typeof value === 'number' || typeof value === 'boolean') return String(value);
  if (value === null || value === undefined) return '';
  return JSON.stringify(value);
};

const loadSettings = async (): Promise<void> => {
  try {
    errorMessage.value = '';
    const payload = await settingsService.fetchSettings({
      search: search.value,
      group: selectedGroup.value || undefined,
    });

    settings.value = payload.settings;
    effective.value = payload.effective;
    groups.value = payload.groups;
    settingTypes.value = payload.types;
  } catch (error) {
    errorMessage.value = (error as { message?: string }).message ?? 'Unable to load settings';
  }
};

const loadMeta = async (): Promise<void> => {
  const meta = await api.get<{
    roles?: MetaRef[];
    permissions?: MetaRef[];
  }>('/v1/meta');

  roles.value = meta.data?.roles ?? [];
  permissions.value = meta.data?.permissions ?? [];

  try {
    const usersPayload = await api.get<Array<{ id: number; name: string; email: string }>>('/v1/users');
    users.value = usersPayload.data ?? [];
  } catch {
    users.value = [];
  }
};

const scopeLabel = (item: SystemSettingRecord): string => {
  if (item.scope.type === 'user') return `user:${item.scope.user?.name ?? item.scope.user_id}`;
  if (item.scope.type === 'role') return `role:${item.scope.role?.name ?? item.scope.role_id}`;
  if (item.scope.type === 'permission') return `permission:${item.scope.permission?.name ?? item.scope.permission_id}`;
  return 'global';
};

const stringify = (value: unknown): string => {
  if (typeof value === 'string') return value;
  if (typeof value === 'number' || typeof value === 'boolean') return String(value);
  if (value === null || value === undefined) return '-';
  return JSON.stringify(value);
};

const resetForm = (): void => {
  editingId.value = null;
  scopeType.value = 'global';
  form.key = '';
  form.label = '';
  form.group = 'general';
  form.description = '';
  form.type = 'string';
  form.value = '';
  form.default_value = '';
  form.is_frontend = true;
  form.is_backend = true;
  form.priority = 100;
  form.is_active = true;
  form.is_system = false;
  form.scope_user_id = null;
  form.scope_role_id = null;
  form.scope_permission_id = null;
};

const startCreate = (): void => {
  resetForm();
};

const startEdit = (item: SystemSettingRecord): void => {
  editingId.value = item.id;
  form.key = item.key;
  form.label = item.label;
  form.group = item.group;
  form.description = item.description ?? '';
  form.type = item.type;
  form.value = parseEditableValue(item.value);
  form.default_value = parseEditableValue(item.default_value);
  form.is_frontend = item.is_frontend;
  form.is_backend = item.is_backend;
  form.priority = item.priority;
  form.is_active = item.is_active;
  form.is_system = item.is_system;
  form.scope_user_id = item.scope.user_id;
  form.scope_role_id = item.scope.role_id;
  form.scope_permission_id = item.scope.permission_id;
  scopeType.value = item.scope.type;
};

const save = async (): Promise<void> => {
  const payload: UpsertSettingPayload = {
    ...form,
    value: form.value,
    default_value: form.default_value,
  };

  if (editingId.value === null) {
    await settingsService.createSetting(payload);
  } else {
    await settingsService.updateSetting(editingId.value, payload);
  }

  await loadSettings();
  resetForm();
};

const remove = async (id: number): Promise<void> => {
  const accepted = window.confirm('Delete this setting?');
  if (!accepted) return;

  await settingsService.deleteSetting(id);
  await loadSettings();
};

const onSearch = (): void => {
  void loadSettings();
};

onMounted(async () => {
  await Promise.all([loadMeta(), loadSettings()]);
});
</script>

<style scoped>
.settings-page{display:grid;gap:12px}
.settings-page__header{margin-top:0;display:flex;justify-content:space-between;gap:10px;align-items:flex-start}
.settings-page__title{margin:0;font-size:18px;color:#f8fafc}
.settings-page__subtitle{margin:6px 0 0;color:#94a3b8;font-size:13px}
.settings-page__actions{display:flex;gap:8px;align-items:center}
.settings-page__search{height:34px;min-width:280px;border-radius:8px;border:1px solid rgba(71,85,105,.6);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 10px;font-size:12px}
.settings-page__btn{height:34px;border-radius:8px;border:1px solid rgba(71,85,105,.55);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 12px;font-size:12px}
.settings-page__btn--primary{border-color:rgba(59,130,246,.55);background:rgba(59,130,246,.2);color:#bfdbfe}
.settings-page__layout{display:grid;grid-template-columns:2fr 1fr;gap:12px}
.settings-table-card{margin-top:0;display:grid;gap:10px}
.settings-table-card__header{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}
.settings-table-card__title{margin:0;color:#f8fafc;font-size:15px}
.settings-table-card__subtitle{margin:5px 0 0;color:#94a3b8;font-size:12px}
.settings-page__select{height:32px;border-radius:8px;border:1px solid rgba(71,85,105,.6);background:#0f172a;color:#e2e8f0;padding:0 8px}
.settings-table-wrap{overflow:auto}
.settings-table{width:100%;border-collapse:collapse;min-width:920px}
.settings-table th,.settings-table td{padding:8px;border-bottom:1px solid rgba(71,85,105,.4);font-size:12px;color:#cbd5e1;text-align:left;vertical-align:top}
.settings-table th{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em}
.settings-table__primary{color:#f8fafc;font-weight:600}
.settings-table__secondary{color:#94a3b8;font-size:11px}
.settings-table__empty{text-align:center;color:#94a3b8}
.scope-badge{display:inline-block;padding:2px 7px;border-radius:999px;background:rgba(71,85,105,.45);color:#cbd5e1;font-size:11px}
.scope-badge.is-user{background:rgba(59,130,246,.22);color:#bfdbfe}
.scope-badge.is-permission{background:rgba(16,185,129,.22);color:#6ee7b7}
.scope-badge.is-role{background:rgba(245,158,11,.22);color:#fcd34d}
.settings-page__icon-btn{height:28px;border-radius:7px;border:1px solid rgba(71,85,105,.5);background:rgba(15,23,42,.6);color:#e2e8f0;padding:0 8px;font-size:11px;margin-right:6px}
.settings-page__icon-btn.is-danger{border-color:rgba(239,68,68,.45);color:#fecaca}
.settings-editor{margin-top:0;display:grid;gap:9px;align-content:start}
.settings-editor__title{margin:0;color:#f8fafc;font-size:15px}
.settings-editor__subtitle{margin:0;color:#94a3b8;font-size:12px}
.settings-editor__form{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.settings-editor__label{display:grid;gap:5px;color:#94a3b8;font-size:11px}
.settings-editor__label--full{grid-column:1 / -1}
.settings-editor__input,.settings-editor__textarea{width:100%;border-radius:8px;border:1px solid rgba(71,85,105,.6);background:rgba(15,23,42,.7);color:#e2e8f0;padding:8px 10px;font-size:12px}
.settings-editor__textarea{resize:vertical}
.settings-editor__check{display:flex;align-items:center;gap:6px;color:#cbd5e1;font-size:12px}
.settings-editor__footer{grid-column:1 / -1;display:flex;justify-content:flex-end;gap:8px;padding-top:4px}
@media (max-width:1180px){.settings-page__layout{grid-template-columns:1fr}.settings-page__search{min-width:220px}}
@media (max-width:760px){.settings-page__header{flex-direction:column}.settings-page__actions{width:100%;flex-wrap:wrap}.settings-page__search{width:100%;min-width:0}.settings-editor__form{grid-template-columns:1fr}}
</style>

