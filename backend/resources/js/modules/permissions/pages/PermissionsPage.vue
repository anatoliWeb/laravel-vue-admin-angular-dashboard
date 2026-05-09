<template>
  <section class="permissions-page">
    <header class="permissions-page__header c-card">
      <div>
        <h2 class="permissions-page__title">Permissions Management</h2>
        <p class="permissions-page__subtitle">Audit and manage RBAC permission capabilities across modules and role assignments.</p>
      </div>
      <span class="permissions-page__stat">Total: {{ filteredPermissions.length }}</span>
    </header>

    <PermissionsFilters
      :search="query.search"
      :module="query.module"
      :modules="availableModules"
      :type="query.type"
      :usage="query.usage"
      @update:search="onSearchChange"
      @update:module="onModuleChange"
      @update:type="onTypeChange"
      @update:usage="onUsageChange"
    />

    <section class="c-card permissions-page__table-wrap">
      <div v-if="isLoading" class="permissions-page__state"><BaseLoader label="Loading permissions..." /></div>

      <BaseErrorState v-else-if="errorMessage" title="Failed to load permissions" :description="errorMessage">
        <button type="button" class="permissions-page__retry" @click="loadPermissions">Retry</button>
      </BaseErrorState>

      <template v-else>
        <BaseTable :columns="tableColumns" :rows="paginatedPermissions" row-key="id">
          <template #empty>
            <BaseEmptyState title="No permissions found" description="Try adjusting search or filter criteria." />
          </template>

          <template #cell:permission="{ row }">
            <div class="permissions-main-cell">
              <div class="permissions-main-cell__name">{{ row.name }}</div>
              <div class="permissions-main-cell__desc">{{ row.description }}</div>
            </div>
          </template>

          <template #cell:module="{ row }">
            <span class="permissions-badge permissions-badge--module">{{ row.module }}</span>
          </template>

          <template #cell:used_by_roles="{ row }">
            <div class="permissions-preview">
              <span v-for="role in previewRoles(row.used_by_roles as string[])" :key="role" class="permissions-badge permissions-badge--role">{{ role }}</span>
              <span v-if="(row.used_by_roles as string[]).length > 2" class="permissions-badge permissions-badge--muted">+{{ (row.used_by_roles as string[]).length - 2 }} more</span>
              <span v-if="(row.used_by_roles as string[]).length === 0" class="permissions-badge permissions-badge--muted">Unused</span>
            </div>
          </template>

          <template #cell:type="{ row }">
            <span class="permissions-badge" :class="typeClass(row.type as string)">{{ row.type }}</span>
          </template>

          <template #cell:created_at="{ row }">
            {{ formatDate((row.created_at as string | null | undefined) ?? null) }}
          </template>

          <template #cell:actions="{ row }">
            <PermissionsRowActions
              :can-edit="can('permissions.edit')"
              :can-assign="can('roles.edit')"
              @action="(action) => handleRowAction(action, row.id as number, row.name as string)"
            />
          </template>
        </BaseTable>

        <footer class="permissions-page__footer">
          <UsersPagination
            :current-page="query.page"
            :total-pages="totalPages"
            :per-page="query.perPage"
            :total-items="filteredPermissions.length"
            :range-start="visibleRange.start"
            :range-end="visibleRange.end"
            @change="onPageChange"
            @update:per-page="onPerPageChange"
          />
        </footer>
      </template>
    </section>

    <section class="c-card permissions-page__matrix-placeholder">
      <h3 class="permissions-page__matrix-title">Permission Matrix Coming Next</h3>
      <p class="permissions-page__matrix-text">Upcoming roles x permissions matrix will be built on top of this module data contract and filtering architecture.</p>
    </section>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import PermissionsFilters from '../components/PermissionsFilters.vue';
import PermissionsRowActions from '../components/PermissionsRowActions.vue';
import { permissionsService } from '../services/permissions.service';
import type { PermissionListItem, PermissionsQuery } from '../types/permissions.types';
import UsersPagination from '../../users/components/UsersPagination.vue';
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import BaseLoader from '../../../shared/components/ui/BaseLoader.vue';
import BaseTable, { type BaseTableColumn } from '../../../shared/components/ui/BaseTable.vue';

/**
 * Permissions module page.
 *
 * WHY GROUPING STRATEGY:
 * Permission keys are grouped by module prefix (`module.action`) to keep large
 * RBAC datasets scannable. This page mirrors permissions for management UX,
 * but backend authorization remains the only security boundary.
 */
const isLoading = ref(true);
const errorMessage = ref('');
const permissions = ref<PermissionListItem[]>([]);
const currentUserPermissions = ref<string[]>([]);

const query = ref<PermissionsQuery>({
  search: '',
  module: 'all',
  type: 'all',
  usage: 'all',
  page: 1,
  perPage: 10,
});

let searchDebounce: ReturnType<typeof setTimeout> | undefined;

const tableColumns: BaseTableColumn[] = [
  { key: 'permission', label: 'Permission' },
  { key: 'module', label: 'Module / Group', width: '130px', align: 'center' },
  { key: 'used_by_roles', label: 'Used by roles' },
  { key: 'type', label: 'Type', width: '90px', align: 'center' },
  { key: 'created_at', label: 'Created date', width: '130px' },
  { key: 'actions', label: 'Actions', width: '110px', align: 'right' },
];

const availableModules = computed(() => [...new Set(permissions.value.map((item) => item.module))].sort());

const filteredPermissions = computed(() => {
  const search = query.value.search.trim().toLowerCase();

  return permissions.value.filter((permission) => {
    const searchMatch =
      search.length === 0 ||
      permission.name.toLowerCase().includes(search) ||
      permission.module.toLowerCase().includes(search) ||
      permission.description.toLowerCase().includes(search);

    const moduleMatch = query.value.module === 'all' || permission.module === query.value.module;
    const typeMatch = query.value.type === 'all' || permission.type === query.value.type;
    const usageMatch = query.value.usage === 'all' || permission.usage === query.value.usage;

    return searchMatch && moduleMatch && typeMatch && usageMatch;
  });
});

const totalPages = computed(() => Math.max(Math.ceil(filteredPermissions.value.length / query.value.perPage), 1));

const paginatedPermissions = computed(() => {
  const start = (query.value.page - 1) * query.value.perPage;
  return filteredPermissions.value.slice(start, start + query.value.perPage);
});

const visibleRange = computed(() => {
  const total = filteredPermissions.value.length;
  if (total === 0) return { start: 0, end: 0 };

  const start = (query.value.page - 1) * query.value.perPage + 1;
  const end = Math.min(query.value.page * query.value.perPage, total);
  return { start, end };
});

const can = (permission: string): boolean => {
  return currentUserPermissions.value.includes(permission) || currentUserPermissions.value.includes('permissions.view');
};

const previewRoles = (roles: string[]): string[] => roles.slice(0, 2);

const typeClass = (type: string): string => {
  if (type === 'read') return 'permissions-badge--type-read';
  if (type === 'write') return 'permissions-badge--type-write';
  return 'permissions-badge--type-manage';
};

const formatDate = (value: string | null): string => {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return '-';
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit', year: 'numeric' }).format(parsed);
};

const onSearchChange = (value: string): void => {
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    query.value.search = value;
    query.value.page = 1;
  }, 250);
};

const onModuleChange = (value: string): void => {
  query.value.module = value;
  query.value.page = 1;
};

const onTypeChange = (value: 'all' | 'read' | 'write' | 'manage'): void => {
  query.value.type = value;
  query.value.page = 1;
};

const onUsageChange = (value: 'all' | 'used' | 'unused'): void => {
  query.value.usage = value;
  query.value.page = 1;
};

const onPageChange = (page: number): void => {
  query.value.page = Math.min(Math.max(page, 1), totalPages.value);
};

const onPerPageChange = (size: number): void => {
  query.value.perPage = size;
  query.value.page = 1;
};

const handleRowAction = (action: 'view' | 'edit' | 'assign', permissionId: number, permissionName: string): void => {
  // Placeholder until dedicated modal/panel workflows are implemented.
  // eslint-disable-next-line no-alert
  window.alert(`${action.toUpperCase()} action for ${permissionName} (#${permissionId}) will be wired in next phase.`);
};

const loadPermissions = async (): Promise<void> => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    const [permissionItems, metaPayload] = await Promise.all([
      permissionsService.fetchPermissions(),
      permissionsService.fetchPermissionsMeta(),
    ]);

    permissions.value = permissionItems;
    currentUserPermissions.value = metaPayload.current_user_permissions;
  } catch (error) {
    errorMessage.value = (error as { message?: string })?.message ?? 'Unable to fetch permissions list.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadPermissions();
});
</script>

<style scoped>
.permissions-page{display:grid;gap:12px}
.permissions-page__header{margin-top:0;display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.permissions-page__title{margin:0;font-size:18px;color:#f8fafc}
.permissions-page__subtitle{margin:6px 0 0;color:#94a3b8;font-size:13px}
.permissions-page__stat{border-radius:999px;border:1px solid rgba(71,85,105,.6);padding:4px 9px;font-size:11px;color:#cbd5e1}
.permissions-page__table-wrap{margin-top:0;display:grid;gap:10px}
.permissions-page__state{padding:14px 0}
.permissions-page__retry{height:32px;border-radius:8px;border:1px solid rgba(71,85,105,.55);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 11px}
.permissions-main-cell{min-width:220px}
.permissions-main-cell__name{color:#f8fafc;font-weight:600}
.permissions-main-cell__desc{color:#94a3b8;font-size:12px}
.permissions-preview{display:flex;flex-wrap:wrap;gap:6px}
.permissions-badge{border-radius:999px;font-size:11px;padding:2px 8px;border:1px solid rgba(71,85,105,.6)}
.permissions-badge--module{background:rgba(59,130,246,.18);color:#bfdbfe;border-color:rgba(59,130,246,.4)}
.permissions-badge--role{background:rgba(34,211,238,.14);color:#67e8f9;border-color:rgba(34,211,238,.38)}
.permissions-badge--muted{color:#94a3b8}
.permissions-badge--type-read{background:rgba(132,204,22,.16);color:#bef264;border-color:rgba(132,204,22,.42)}
.permissions-badge--type-write{background:rgba(245,158,11,.16);color:#fcd34d;border-color:rgba(245,158,11,.42)}
.permissions-badge--type-manage{background:rgba(244,114,182,.16);color:#f9a8d4;border-color:rgba(244,114,182,.42)}
.permissions-page__footer{display:flex;justify-content:flex-end}
.permissions-page__matrix-placeholder{margin-top:0;border:1px dashed rgba(71,85,105,.6)}
.permissions-page__matrix-title{margin:0;color:#f8fafc;font-size:15px}
.permissions-page__matrix-text{margin:6px 0 0;color:#94a3b8;font-size:13px}
@media (max-width:760px){.permissions-page__header{flex-direction:column}.permissions-page__footer{justify-content:flex-start}}
</style>
