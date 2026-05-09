<template>
  <section class="roles-page">
    <header class="roles-page__header c-card">
      <div>
        <h2 class="roles-page__title">Roles Management</h2>
        <p class="roles-page__subtitle">Define role access boundaries and preview permission scope for enterprise RBAC operations.</p>
      </div>
      <span class="roles-page__stat">Total: {{ filteredRoles.length }}</span>
    </header>

    <RolesFilters
      :search="query.search"
      :type="query.type"
      :status="query.status"
      @update:search="onSearchChange"
      @update:type="onTypeChange"
      @update:status="onStatusChange"
    />

    <section class="c-card roles-page__table-wrap">
      <div v-if="isLoading" class="roles-page__state"><BaseLoader label="Loading roles..." /></div>

      <BaseErrorState v-else-if="errorMessage" title="Failed to load roles" :description="errorMessage">
        <button type="button" class="roles-page__retry" @click="loadRoles">Retry</button>
      </BaseErrorState>

      <template v-else>
        <BaseTable :columns="tableColumns" :rows="paginatedRoles" row-key="id">
          <template #empty>
            <BaseEmptyState title="No roles found" description="Try adjusting role filters or search query." />
          </template>

          <template #cell:role="{ row }">
            <div class="roles-main-cell">
              <div class="roles-main-cell__name">{{ row.name }}</div>
              <div class="roles-main-cell__desc">{{ row.description }}</div>
            </div>
          </template>

          <template #cell:permissions_preview="{ row }">
            <div class="roles-preview">
              <span v-for="permission in previewPermissions(row.permissions as string[])" :key="permission" class="roles-badge roles-badge--permission">{{ permission }}</span>
              <span v-if="(row.permissions as string[]).length > 2" class="roles-badge roles-badge--muted">+{{ (row.permissions as string[]).length - 2 }} more</span>
            </div>
          </template>

          <template #cell:permissions_count="{ row }">{{ row.permissions_count }}</template>
          <template #cell:users_count="{ row }">{{ row.users_count }}</template>

          <template #cell:type="{ row }">
            <span class="roles-badge" :class="roleTypeClass(row.name as string, row.type as string)">{{ row.type }}</span>
          </template>

          <template #cell:status="{ row }">
            <span class="roles-badge" :class="(row.status as string) === 'active' ? 'roles-badge--active' : 'roles-badge--inactive'">{{ row.status }}</span>
          </template>

          <template #cell:created_at="{ row }">{{ formatDate((row.created_at as string | null | undefined) ?? null) }}</template>

          <template #cell:actions="{ row }">
            <RolesRowActions
              :can-edit="can('roles.edit')"
              :can-delete="can('roles.delete')"
              :can-permissions="can('roles.permissions') || can('permissions.edit')"
              @action="(action) => handleRowAction(action, row.id as number, row.name as string)"
            />
          </template>
        </BaseTable>

        <footer class="roles-page__footer">
          <UsersPagination
            :current-page="query.page"
            :total-pages="totalPages"
            :per-page="query.perPage"
            :total-items="filteredRoles.length"
            :range-start="visibleRange.start"
            :range-end="visibleRange.end"
            @change="onPageChange"
            @update:per-page="onPerPageChange"
          />
        </footer>
      </template>
    </section>

    <RolesSidePanel :open="panel.open" :title="panel.title" @close="closePanel" />
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import RolesFilters from '../components/RolesFilters.vue';
import RolesRowActions from '../components/RolesRowActions.vue';
import RolesSidePanel from '../components/RolesSidePanel.vue';
import { rolesService } from '../services/roles.service';
import type { RoleListItem, RolesQuery } from '../types/roles.types';
import UsersPagination from '../../users/components/UsersPagination.vue';
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import BaseLoader from '../../../shared/components/ui/BaseLoader.vue';
import BaseTable, { type BaseTableColumn } from '../../../shared/components/ui/BaseTable.vue';

/**
 * Roles module page (enterprise RBAC blueprint).
 *
 * ARCHITECTURE NOTE:
 * This module extends CRUD scaffolding into access-management flows by combining
 * role metadata, permission previews, and permission-aware actions. It provides
 * a scalable base for future permissions matrix and organization-level RBAC.
 */
const isLoading = ref(true);
const errorMessage = ref('');
const roles = ref<RoleListItem[]>([]);
const currentUserPermissions = ref<string[]>([]);

const query = ref<RolesQuery>({
  search: '',
  type: 'all',
  status: 'all',
  page: 1,
  perPage: 10,
});

const panel = ref({ open: false, title: '' });
let searchDebounce: ReturnType<typeof setTimeout> | undefined;

const tableColumns: BaseTableColumn[] = [
  { key: 'role', label: 'Role' },
  { key: 'permissions_preview', label: 'Permission preview' },
  { key: 'permissions_count', label: 'Permissions', width: '110px', align: 'center' },
  { key: 'users_count', label: 'Users', width: '90px', align: 'center' },
  { key: 'type', label: 'Type', width: '110px', align: 'center' },
  { key: 'status', label: 'Status', width: '110px', align: 'center' },
  { key: 'created_at', label: 'Created date', width: '130px' },
  { key: 'actions', label: 'Actions', width: '120px', align: 'right' },
];

const filteredRoles = computed(() => {
  const search = query.value.search.trim().toLowerCase();

  return roles.value.filter((role) => {
    const searchMatch =
      search.length === 0 ||
      role.name.toLowerCase().includes(search) ||
      role.description.toLowerCase().includes(search) ||
      role.permissions.some((permission) => permission.toLowerCase().includes(search));

    const typeMatch = query.value.type === 'all' || role.type === query.value.type;
    const statusMatch = query.value.status === 'all' || role.status === query.value.status;

    return searchMatch && typeMatch && statusMatch;
  });
});

const totalPages = computed(() => Math.max(Math.ceil(filteredRoles.value.length / query.value.perPage), 1));

const paginatedRoles = computed(() => {
  const start = (query.value.page - 1) * query.value.perPage;
  return filteredRoles.value.slice(start, start + query.value.perPage);
});

const visibleRange = computed(() => {
  const total = filteredRoles.value.length;
  if (total === 0) return { start: 0, end: 0 };

  const start = (query.value.page - 1) * query.value.perPage + 1;
  const end = Math.min(query.value.page * query.value.perPage, total);
  return { start, end };
});

const can = (permission: string): boolean => currentUserPermissions.value.includes(permission);

const previewPermissions = (permissions: string[]): string[] => permissions.slice(0, 2);

const formatDate = (value: string | null): string => {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return '-';
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit', year: 'numeric' }).format(parsed);
};

const roleTypeClass = (name: string, type: string): string => {
  const normalized = name.toLowerCase();
  if (normalized === 'admin') return 'roles-badge--admin';
  if (normalized === 'manager') return 'roles-badge--manager';
  if (normalized === 'user') return 'roles-badge--user';
  return type === 'system' ? 'roles-badge--system' : 'roles-badge--custom';
};

const onSearchChange = (value: string): void => {
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    query.value.search = value;
    query.value.page = 1;
  }, 250);
};

const onTypeChange = (value: 'all' | 'system' | 'custom'): void => {
  query.value.type = value;
  query.value.page = 1;
};

const onStatusChange = (value: 'all' | 'active' | 'inactive'): void => {
  query.value.status = value;
  query.value.page = 1;
};

const onPageChange = (page: number): void => {
  query.value.page = Math.min(Math.max(page, 1), totalPages.value);
};

const onPerPageChange = (size: number): void => {
  query.value.perPage = size;
  query.value.page = 1;
};

const handleRowAction = (action: 'view' | 'edit' | 'permissions' | 'delete', roleId: number, roleName: string): void => {
  panel.value = {
    open: true,
    title: `${action.toUpperCase()} - ${roleName} (#${roleId})`,
  };
};

const closePanel = (): void => {
  panel.value.open = false;
};

const loadRoles = async (): Promise<void> => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    const [rolesPayload, permissionsMeta] = await Promise.all([
      rolesService.fetchRoles(),
      rolesService.fetchPermissionsMeta(),
    ]);

    roles.value = rolesPayload;
    currentUserPermissions.value = permissionsMeta.current_user_permissions;
  } catch (error) {
    errorMessage.value = (error as { message?: string })?.message ?? 'Unable to fetch roles list.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadRoles();
});
</script>

<style scoped>
.roles-page{display:grid;gap:12px}
.roles-page__header{margin-top:0;display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.roles-page__title{margin:0;font-size:18px;color:#f8fafc}
.roles-page__subtitle{margin:6px 0 0;color:#94a3b8;font-size:13px}
.roles-page__stat{border-radius:999px;border:1px solid rgba(71,85,105,.6);padding:4px 9px;font-size:11px;color:#cbd5e1}
.roles-page__table-wrap{margin-top:0;display:grid;gap:10px}
.roles-page__state{padding:14px 0}
.roles-page__retry{height:32px;border-radius:8px;border:1px solid rgba(71,85,105,.55);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 11px}
.roles-main-cell{min-width:200px}
.roles-main-cell__name{color:#f8fafc;font-weight:600}
.roles-main-cell__desc{color:#94a3b8;font-size:12px}
.roles-preview{display:flex;flex-wrap:wrap;gap:6px}
.roles-badge{border-radius:999px;font-size:11px;padding:2px 8px;border:1px solid rgba(71,85,105,.6)}
.roles-badge--permission{background:rgba(59,130,246,.18);color:#bfdbfe;border-color:rgba(59,130,246,.4)}
.roles-badge--muted{color:#94a3b8}
.roles-badge--active{background:rgba(16,185,129,.16);color:#6ee7b7;border-color:rgba(16,185,129,.45)}
.roles-badge--inactive{background:rgba(239,68,68,.16);color:#fca5a5;border-color:rgba(239,68,68,.45)}
.roles-badge--admin{background:rgba(244,114,182,.18);color:#f9a8d4;border-color:rgba(244,114,182,.42)}
.roles-badge--manager{background:rgba(34,211,238,.16);color:#67e8f9;border-color:rgba(34,211,238,.42)}
.roles-badge--user{background:rgba(132,204,22,.16);color:#bef264;border-color:rgba(132,204,22,.42)}
.roles-badge--system{background:rgba(59,130,246,.16);color:#93c5fd;border-color:rgba(59,130,246,.42)}
.roles-badge--custom{background:rgba(245,158,11,.16);color:#fcd34d;border-color:rgba(245,158,11,.42)}
.roles-page__footer{display:flex;justify-content:flex-end}
@media (max-width:760px){.roles-page__header{flex-direction:column}.roles-page__footer{justify-content:flex-start}}
</style>
