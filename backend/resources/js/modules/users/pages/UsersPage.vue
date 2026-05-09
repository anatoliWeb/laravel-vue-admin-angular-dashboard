<template>
  <section class="users-page">
    <header class="users-page__header c-card">
      <div>
        <h2 class="users-page__title">Users Management</h2>
        <p class="users-page__subtitle">Manage accounts, roles, and permission visibility from one operational table.</p>
      </div>
      <div class="users-page__stats">
        <span class="users-page__stat">Total: {{ filteredUsers.length }}</span>
      </div>
    </header>

    <UsersFilters
      :search="query.search"
      :role="query.role"
      :status="query.status"
      :roles="availableRoles"
      @update:search="onSearchChange"
      @update:role="onRoleChange"
      @update:status="onStatusChange"
    />

    <section class="c-card users-page__table-wrap">
      <div v-if="isLoading" class="users-page__state">
        <BaseLoader label="Loading users..." />
      </div>

      <BaseErrorState
        v-else-if="errorMessage"
        title="Failed to load users"
        :description="errorMessage"
      >
        <button type="button" class="users-page__retry" @click="loadUsers">Retry</button>
      </BaseErrorState>

      <template v-else>
        <BaseTable :columns="tableColumns" :rows="paginatedUsers" row-key="id">
          <template #empty>
            <BaseEmptyState title="No users found" description="Try adjusting filters or search query." />
          </template>

          <template #cell:avatar="{ row }">
            <span class="users-avatar">{{ initials(row.name as string) }}</span>
          </template>

          <template #cell:name="{ row }">
            <div class="users-main-cell">
              <div class="users-main-cell__name">{{ row.name }}</div>
              <div class="users-main-cell__email">{{ row.email }}</div>
            </div>
          </template>

          <template #cell:roles="{ row }">
            <div class="users-badges">
              <span v-for="role in (row.roles as string[])" :key="role" class="users-badge users-badge--role">{{ role }}</span>
              <span v-if="(row.roles as string[]).length === 0" class="users-badge users-badge--muted">No roles</span>
            </div>
          </template>

          <template #cell:permissions_count="{ row }">
            {{ (row.permissions as string[]).length }}
          </template>

          <template #cell:status="{ row }">
            <span class="users-badge" :class="(row.status as string) === 'active' ? 'users-badge--active' : 'users-badge--inactive'">
              {{ row.status }}
            </span>
          </template>

          <template #cell:created_at="{ row }">
            {{ formatDate((row.created_at as string | null | undefined) ?? null) }}
          </template>

          <template #cell:actions="{ row }">
            <UsersRowActions
              :can-edit="can('users.edit')"
              :can-delete="can('users.delete')"
              @action="(action) => handleRowAction(action, row.id as number)"
            />
          </template>
        </BaseTable>

        <footer class="users-page__footer">
          <UsersPagination
            :current-page="query.page"
            :total-pages="totalPages"
            :per-page="query.perPage"
            :total-items="filteredUsers.length"
            :range-start="visibleRange.start"
            :range-end="visibleRange.end"
            @change="onPageChange"
            @update:per-page="onPerPageChange"
          />
        </footer>
      </template>
    </section>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import UsersFilters from '../components/UsersFilters.vue';
import UsersPagination from '../components/UsersPagination.vue';
import UsersRowActions from '../components/UsersRowActions.vue';
import { usersService } from '../services/users.service';
import type { UserListItem, UsersQuery } from '../types/users.types';
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import BaseLoader from '../../../shared/components/ui/BaseLoader.vue';
import BaseTable, { type BaseTableColumn } from '../../../shared/components/ui/BaseTable.vue';
import type { PaginationMeta } from '../../../types/response.types';

/**
 * Users module page (CRUD blueprint).
 *
 * WHY THIS MATTERS:
 * This page defines the reusable admin pattern for future modules (roles,
 * permissions, tokens): filters + table + permission-aware actions + states.
 * Keeping this structure modular prevents ad-hoc CRUD implementations.
 */
const isLoading = ref(true);
const errorMessage = ref('');
const users = ref<UserListItem[]>([]);
const currentUserPermissions = ref<string[]>([]);
const backendMeta = ref<PaginationMeta | null>(null);

const query = ref<UsersQuery>({
  search: '',
  role: 'all',
  status: 'all',
  page: 1,
  perPage: 10,
});

let searchDebounce: ReturnType<typeof setTimeout> | undefined;

const tableColumns: BaseTableColumn[] = [
  { key: 'avatar', label: 'Avatar', width: '72px', align: 'center' },
  { key: 'name', label: 'Name' },
  { key: 'roles', label: 'Roles' },
  { key: 'permissions_count', label: 'Permissions', width: '110px', align: 'center' },
  { key: 'status', label: 'Status', width: '120px', align: 'center' },
  { key: 'created_at', label: 'Created date', width: '140px' },
  { key: 'actions', label: 'Actions', width: '110px', align: 'right' },
];

const availableRoles = computed(() => {
  return [...new Set(users.value.flatMap((item) => item.roles))].sort((a, b) => a.localeCompare(b));
});

const filteredUsers = computed(() => {
  const search = query.value.search.trim().toLowerCase();

  return users.value.filter((user) => {
    const searchMatch =
      search.length === 0 ||
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search);

    const roleMatch = query.value.role === 'all' || user.roles.includes(query.value.role);
    const statusMatch = query.value.status === 'all' || user.status === query.value.status;

    return searchMatch && roleMatch && statusMatch;
  });
});

const totalPages = computed(() => {
  const useBackendMeta = hasServerPagination.value && !hasActiveFilters.value;
  if (useBackendMeta && backendMeta.value) {
    return Math.max(backendMeta.value.last_page, 1);
  }

  return Math.max(Math.ceil(filteredUsers.value.length / query.value.perPage), 1);
});

const paginatedUsers = computed(() => {
  const useBackendMeta = hasServerPagination.value && !hasActiveFilters.value;

  if (useBackendMeta) {
    // Backend-pagination-ready branch: if API returns already paginated rows,
    // UI keeps consuming server meta without changing table/pager contracts.
    return filteredUsers.value;
  }

  const start = (query.value.page - 1) * query.value.perPage;
  return filteredUsers.value.slice(start, start + query.value.perPage);
});

const visibleRange = computed(() => {
  const total = filteredUsers.value.length;
  if (total === 0) {
    return { start: 0, end: 0 };
  }

  const start = (query.value.page - 1) * query.value.perPage + 1;
  const end = Math.min(query.value.page * query.value.perPage, total);
  return { start, end };
});

const hasActiveFilters = computed(() => {
  return query.value.search.length > 0 || query.value.role !== 'all' || query.value.status !== 'all';
});

const hasServerPagination = computed(() => {
  return !!backendMeta.value;
});

const can = (permission: string): boolean => {
  // Permission-aware rendering keeps destructive actions hidden by default.
  // Backend remains source-of-truth, UI only reflects allowed capabilities.
  return currentUserPermissions.value.includes(permission);
};

const initials = (name: string): string => {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('');
};

const formatDate = (value: string | null): string => {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return '-';

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
  }).format(parsed);
};

const onSearchChange = (value: string): void => {
  if (searchDebounce) {
    clearTimeout(searchDebounce);
  }

  searchDebounce = setTimeout(() => {
    query.value.search = value;
    query.value.page = 1;
  }, 250);
};

const onRoleChange = (value: string): void => {
  query.value.role = value;
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

const handleRowAction = (action: 'view' | 'edit' | 'delete', userId: number): void => {
  // Placeholder hook for future modals/forms; keeps action contract stable now.
  // eslint-disable-next-line no-alert
  window.alert(`${action.toUpperCase()} action for user #${userId} will be wired in next phase.`);
};

const isPaginationMeta = (meta: unknown): meta is PaginationMeta => {
  return (
    typeof meta === 'object' &&
    meta !== null &&
    typeof (meta as PaginationMeta).current_page === 'number' &&
    typeof (meta as PaginationMeta).last_page === 'number' &&
    typeof (meta as PaginationMeta).per_page === 'number' &&
    typeof (meta as PaginationMeta).total === 'number'
  );
};

const loadUsers = async (): Promise<void> => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    const [usersPayload, permissionsPayload] = await Promise.all([
      usersService.fetchUsers(),
      usersService.fetchPermissionsMeta(),
    ]);

    users.value = usersPayload.items;
    currentUserPermissions.value = permissionsPayload.current_user_permissions;

    if (isPaginationMeta(usersPayload.meta)) {
      backendMeta.value = usersPayload.meta;
      query.value.perPage = usersPayload.meta.per_page;
      query.value.page = usersPayload.meta.current_page;
    } else {
      backendMeta.value = null;
    }
  } catch (error) {
    errorMessage.value = (error as { message?: string })?.message ?? 'Unable to fetch users list.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadUsers();
});
</script>

<style scoped>
.users-page {
  display: grid;
  gap: 12px;
}

.users-page__header {
  margin-top: 0;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.users-page__title {
  margin: 0;
  font-size: 18px;
  color: #f8fafc;
}

.users-page__subtitle {
  margin: 6px 0 0;
  color: #94a3b8;
  font-size: 13px;
}

.users-page__stat {
  border-radius: 999px;
  border: 1px solid rgba(71, 85, 105, 0.6);
  padding: 4px 9px;
  font-size: 11px;
  color: #cbd5e1;
}

.users-page__table-wrap {
  margin-top: 0;
  display: grid;
  gap: 10px;
}

.users-page__state {
  padding: 14px 0;
}

.users-page__retry {
  height: 32px;
  border-radius: 8px;
  border: 1px solid rgba(71, 85, 105, 0.55);
  background: rgba(15, 23, 42, 0.7);
  color: #e2e8f0;
  padding: 0 11px;
}

.users-main-cell {
  min-width: 200px;
}

.users-main-cell__name {
  color: #f8fafc;
  font-weight: 600;
}

.users-main-cell__email {
  color: #94a3b8;
  font-size: 12px;
}

.users-avatar {
  width: 30px;
  height: 30px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(59, 130, 246, 0.2);
  color: #bfdbfe;
  font-size: 11px;
  font-weight: 700;
}

.users-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.users-badge {
  border-radius: 999px;
  font-size: 11px;
  padding: 2px 8px;
  border: 1px solid rgba(71, 85, 105, 0.6);
}

.users-badge--role {
  background: rgba(59, 130, 246, 0.18);
  color: #bfdbfe;
  border-color: rgba(59, 130, 246, 0.4);
}

.users-badge--muted {
  color: #94a3b8;
}

.users-badge--active {
  background: rgba(16, 185, 129, 0.16);
  color: #6ee7b7;
  border-color: rgba(16, 185, 129, 0.45);
}

.users-badge--inactive {
  background: rgba(239, 68, 68, 0.16);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.45);
}

.users-page__footer {
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 760px) {
  .users-page__header {
    flex-direction: column;
  }

  .users-page__footer {
    justify-content: flex-start;
  }
}
</style>
