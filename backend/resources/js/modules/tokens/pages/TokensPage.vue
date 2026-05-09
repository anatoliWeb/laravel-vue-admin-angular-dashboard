<template>
  <section class="tokens-page">
    <header class="tokens-page__header c-card">
      <div>
        <h2 class="tokens-page__title">API Tokens Management</h2>
        <p class="tokens-page__subtitle">Control API access credentials for integrations, automations, and external clients.</p>
      </div>
      <div class="tokens-page__header-actions">
        <span class="tokens-page__stat">Total: {{ filteredTokens.length }}</span>
        <button v-if="can('tokens.create')" type="button" class="tokens-page__create-btn" @click="openCreateTokenPanel">
          Create Token
        </button>
      </div>
    </header>

    <TokensFilters
      :search="query.search"
      :owner="query.owner"
      :owners="availableOwners"
      :status="query.status"
      :recent="query.recent"
      :type="query.type"
      @update:search="onSearchChange"
      @update:owner="onOwnerChange"
      @update:status="onStatusChange"
      @update:recent="onRecentChange"
      @update:type="onTypeChange"
    />

    <section class="c-card tokens-page__table-wrap">
      <div v-if="isLoading" class="tokens-page__state"><BaseLoader label="Loading tokens..." /></div>

      <BaseErrorState v-else-if="errorMessage" title="Failed to load API tokens" :description="errorMessage">
        <button type="button" class="tokens-page__retry" @click="loadTokens">Retry</button>
      </BaseErrorState>

      <template v-else>
        <BaseTable :columns="tableColumns" :rows="paginatedTokens" row-key="id">
          <template #empty>
            <BaseEmptyState title="No tokens found" description="Try adjusting filters or create a new token." />
          </template>

          <template #cell:name="{ row }">
            <div class="tokens-main-cell">
              <div class="tokens-main-cell__name">{{ row.name }}</div>
              <div class="tokens-main-cell__meta">ID: {{ row.id }}</div>
            </div>
          </template>

          <template #cell:owner="{ row }">
            <div class="tokens-owner-cell">
              <span class="tokens-owner-cell__avatar">{{ initials((row.owner as { name: string }).name) }}</span>
              <span class="tokens-owner-cell__name">{{ (row.owner as { name: string }).name }}</span>
            </div>
          </template>

          <template #cell:scopes="{ row }">
            <div class="tokens-scopes">
              <span v-for="scope in previewScopes(row.scopes as string[])" :key="scope" class="tokens-badge tokens-badge--scope">{{ scope }}</span>
              <span v-if="(row.scopes as string[]).length > 2" class="tokens-badge tokens-badge--muted">+{{ (row.scopes as string[]).length - 2 }} more</span>
            </div>
          </template>

          <template #cell:last_used_at="{ row }">
            {{ formatLastUsed((row.last_used_at as string | null | undefined) ?? null) }}
          </template>

          <template #cell:created_at="{ row }">
            {{ formatDate((row.created_at as string | null | undefined) ?? null) }}
          </template>

          <template #cell:status="{ row }">
            <span class="tokens-badge" :class="statusClass(row.status as string)">{{ row.status }}</span>
          </template>

          <template #cell:actions="{ row }">
            <TokensRowActions
              :can-edit="can('tokens.edit') || can('tokens.create')"
              :can-delete="can('tokens.delete')"
              @action="(action) => handleRowAction(action, row.id as number, row.name as string)"
            />
          </template>
        </BaseTable>

        <footer class="tokens-page__footer">
          <UsersPagination
            :current-page="query.page"
            :total-pages="totalPages"
            :per-page="query.perPage"
            :total-items="filteredTokens.length"
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

import TokensFilters from '../components/TokensFilters.vue';
import TokensRowActions from '../components/TokensRowActions.vue';
import { tokensService } from '../services/tokens.service';
import type { TokenListItem, TokensQuery } from '../types/tokens.types';
import UsersPagination from '../../users/components/UsersPagination.vue';
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import BaseLoader from '../../../shared/components/ui/BaseLoader.vue';
import BaseTable, { type BaseTableColumn } from '../../../shared/components/ui/BaseTable.vue';

/**
 * API tokens module.
 *
 * SECURITY UX PRINCIPLE:
 * Token screens should foreground ownership, scope visibility, and revocation
 * pathways. UI hints improve operator confidence, but backend authorization and
 * token lifecycle enforcement remain the true security boundary.
 */
const isLoading = ref(true);
const errorMessage = ref('');
const tokens = ref<TokenListItem[]>([]);
const currentUserPermissions = ref<string[]>([]);

const query = ref<TokensQuery>({
  search: '',
  owner: 'all',
  status: 'all',
  recent: 'all',
  type: 'all',
  page: 1,
  perPage: 10,
});

let searchDebounce: ReturnType<typeof setTimeout> | undefined;

const tableColumns: BaseTableColumn[] = [
  { key: 'name', label: 'Token name' },
  { key: 'owner', label: 'Owner', width: '180px' },
  { key: 'scopes', label: 'Permissions / scopes' },
  { key: 'last_used_at', label: 'Last used', width: '120px' },
  { key: 'created_at', label: 'Created date', width: '130px' },
  { key: 'status', label: 'Status', width: '110px', align: 'center' },
  { key: 'actions', label: 'Actions', width: '110px', align: 'right' },
];

const availableOwners = computed(() => [...new Set(tokens.value.map((token) => token.owner.name))].sort((a, b) => a.localeCompare(b)));

const filteredTokens = computed(() => {
  const search = query.value.search.trim().toLowerCase();

  return tokens.value.filter((token) => {
    const searchMatch =
      search.length === 0 ||
      token.name.toLowerCase().includes(search) ||
      token.owner.name.toLowerCase().includes(search);

    const ownerMatch = query.value.owner === 'all' || token.owner.name === query.value.owner;
    const statusMatch = query.value.status === 'all' || token.status === query.value.status;
    const typeMatch = query.value.type === 'all' || token.type === query.value.type;

    const recentMatch =
      query.value.recent === 'all' ||
      (query.value.recent === 'recent' ? isRecentlyUsed(token) : !isRecentlyUsed(token));

    return searchMatch && ownerMatch && statusMatch && typeMatch && recentMatch;
  });
});

const totalPages = computed(() => Math.max(Math.ceil(filteredTokens.value.length / query.value.perPage), 1));

const paginatedTokens = computed(() => {
  const start = (query.value.page - 1) * query.value.perPage;
  return filteredTokens.value.slice(start, start + query.value.perPage);
});

const visibleRange = computed(() => {
  const total = filteredTokens.value.length;
  if (total === 0) return { start: 0, end: 0 };
  const start = (query.value.page - 1) * query.value.perPage + 1;
  const end = Math.min(query.value.page * query.value.perPage, total);
  return { start, end };
});

const can = (permission: string): boolean => currentUserPermissions.value.includes(permission);

const initials = (name: string): string =>
  name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('');

const previewScopes = (scopes: string[]): string[] => scopes.slice(0, 2);

const statusClass = (status: string): string => {
  if (status === 'active') return 'tokens-badge--active';
  if (status === 'revoked') return 'tokens-badge--revoked';
  return 'tokens-badge--expired';
};

const isRecentlyUsed = (token: TokenListItem): boolean => {
  const sourceDate = token.last_used_at ?? token.created_at;
  if (!sourceDate) return false;

  const parsed = new Date(sourceDate);
  if (Number.isNaN(parsed.getTime())) return false;

  const sevenDaysAgo = Date.now() - 7 * 24 * 60 * 60 * 1000;
  return parsed.getTime() >= sevenDaysAgo;
};

const formatDate = (value: string | null): string => {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return '-';
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit', year: 'numeric' }).format(parsed);
};

const formatLastUsed = (value: string | null): string => {
  if (!value) return 'Never';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return 'Unknown';
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit' }).format(parsed);
};

const onSearchChange = (value: string): void => {
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    query.value.search = value;
    query.value.page = 1;
  }, 250);
};

const onOwnerChange = (value: string): void => {
  query.value.owner = value;
  query.value.page = 1;
};

const onStatusChange = (value: 'all' | 'active' | 'revoked' | 'expired'): void => {
  query.value.status = value;
  query.value.page = 1;
};

const onRecentChange = (value: 'all' | 'recent' | 'stale'): void => {
  query.value.recent = value;
  query.value.page = 1;
};

const onTypeChange = (value: 'all' | 'system' | 'user'): void => {
  query.value.type = value;
  query.value.page = 1;
};

const onPageChange = (page: number): void => {
  query.value.page = Math.min(Math.max(page, 1), totalPages.value);
};

const onPerPageChange = (size: number): void => {
  query.value.perPage = size;
  query.value.page = 1;
};

const openCreateTokenPanel = (): void => {
  // Placeholder until create-token modal/side-panel flow is implemented.
  // eslint-disable-next-line no-alert
  window.alert('Create Token panel will be implemented in next phase.');
};

const handleRowAction = (action: 'view' | 'regenerate' | 'revoke' | 'delete', tokenId: number, tokenName: string): void => {
  // Placeholder until destructive token flows are connected to backend mutation endpoints.
  // eslint-disable-next-line no-alert
  window.alert(`${action.toUpperCase()} action for token ${tokenName} (#${tokenId}) will be wired in next phase.`);
};

const loadTokens = async (): Promise<void> => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    const [tokenItems, metaPayload] = await Promise.all([
      tokensService.fetchTokens(),
      tokensService.fetchTokensMeta(),
    ]);

    tokens.value = tokenItems;
    currentUserPermissions.value = metaPayload.current_user_permissions;
  } catch (error) {
    errorMessage.value = (error as { message?: string })?.message ?? 'Unable to fetch tokens list.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadTokens();
});
</script>

<style scoped>
.tokens-page{display:grid;gap:12px}
.tokens-page__header{margin-top:0;display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.tokens-page__title{margin:0;font-size:18px;color:#f8fafc}
.tokens-page__subtitle{margin:6px 0 0;color:#94a3b8;font-size:13px}
.tokens-page__header-actions{display:flex;align-items:center;gap:8px}
.tokens-page__stat{border-radius:999px;border:1px solid rgba(71,85,105,.6);padding:4px 9px;font-size:11px;color:#cbd5e1}
.tokens-page__create-btn{height:32px;border-radius:8px;border:1px solid rgba(245,158,11,.55);background:rgba(245,158,11,.18);color:#fde68a;padding:0 11px;font-size:12px;font-weight:600}
.tokens-page__create-btn:hover{background:rgba(245,158,11,.24)}
.tokens-page__table-wrap{margin-top:0;display:grid;gap:10px}
.tokens-page__state{padding:14px 0}
.tokens-page__retry{height:32px;border-radius:8px;border:1px solid rgba(71,85,105,.55);background:rgba(15,23,42,.7);color:#e2e8f0;padding:0 11px}
.tokens-main-cell{min-width:180px}
.tokens-main-cell__name{color:#f8fafc;font-weight:600}
.tokens-main-cell__meta{color:#94a3b8;font-size:11px}
.tokens-owner-cell{display:inline-flex;align-items:center;gap:8px}
.tokens-owner-cell__avatar{width:28px;height:28px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:rgba(59,130,246,.2);color:#bfdbfe;font-size:10px;font-weight:700}
.tokens-owner-cell__name{color:#e2e8f0;font-size:12px}
.tokens-scopes{display:flex;flex-wrap:wrap;gap:6px}
.tokens-badge{border-radius:999px;font-size:11px;padding:2px 8px;border:1px solid rgba(71,85,105,.6)}
.tokens-badge--scope{background:rgba(34,211,238,.14);color:#67e8f9;border-color:rgba(34,211,238,.38)}
.tokens-badge--muted{color:#94a3b8}
.tokens-badge--active{background:rgba(16,185,129,.16);color:#6ee7b7;border-color:rgba(16,185,129,.45)}
.tokens-badge--revoked{background:rgba(239,68,68,.16);color:#fca5a5;border-color:rgba(239,68,68,.45)}
.tokens-badge--expired{background:rgba(245,158,11,.16);color:#fcd34d;border-color:rgba(245,158,11,.45)}
.tokens-page__footer{display:flex;justify-content:flex-end}
@media (max-width:760px){.tokens-page__header{flex-direction:column}.tokens-page__header-actions{width:100%;justify-content:space-between}.tokens-page__footer{justify-content:flex-start}}
</style>
