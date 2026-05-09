import { api } from '../../../services/api/client';
import type { ApiResponse } from '../../../types/response.types';
import type { ActivityLogItem, ActivityMetaPayload } from '../types/activity.types';

interface StatsPayload {
  data?: {
    recent_activity?: Array<Record<string, unknown>>;
  };
}

interface MetaPayload {
  current_user_permissions?: string[];
}

const deriveModule = (action: string): string => {
  if (!action) return 'system';
  const normalized = action.replaceAll('.', '_');
  return normalized.split('_')[0] || 'system';
};

const deriveEntity = (action: string): string => {
  const normalized = action.replaceAll('.', '_');
  const parts = normalized.split('_');
  return parts.length > 1 ? parts.slice(1).join('_') : 'event';
};

const deriveStatus = (action: string, description: string): 'success' | 'warning' | 'error' => {
  const source = `${action} ${description}`.toLowerCase();
  if (source.includes('failed') || source.includes('error') || source.includes('denied')) return 'error';
  if (source.includes('revoked') || source.includes('deleted') || source.includes('expired')) return 'warning';
  return 'success';
};

const normalizeActivityItem = (entry: Record<string, unknown>, index: number): ActivityLogItem => {
  const action = String(entry.action ?? 'unknown');
  const description = String(entry.description ?? action.replaceAll('_', ' '));
  const meta = (entry.meta as Record<string, unknown> | undefined) ?? {};

  return {
    id: String(entry.id ?? `${action}-${index}`),
    user: (entry.user as { id?: number; name?: string; email?: string } | null) ?? null,
    action,
    module: deriveModule(action),
    entity: deriveEntity(action),
    description,
    status: deriveStatus(action, description),
    ip_address: (meta.ip_address as string | undefined) ?? null,
    created_at: (entry.created_at as string | undefined) ?? null,
    meta,
  };
};

/**
 * Activity module service.
 *
 * ARCHITECTURE NOTE:
 * The backend may expose activity through different endpoints over time.
 * This service isolates source-selection and normalization, so monitoring UI
 * remains stable while audit APIs evolve toward realtime timeline feeds.
 */
export const activityService = {
  async fetchActivity(): Promise<ActivityLogItem[]> {
    try {
      const direct = await api.get<Array<Record<string, unknown>>>('/v1/activity');
      if (Array.isArray(direct.data)) {
        return direct.data.map(normalizeActivityItem);
      }
    } catch {
      // fall through
    }

    try {
      const directLogs = await api.get<Array<Record<string, unknown>>>('/v1/logs');
      if (Array.isArray(directLogs.data)) {
        return directLogs.data.map(normalizeActivityItem);
      }
    } catch {
      // fall through to stats foundation
    }

    const statsResponse = await api.get<StatsPayload>('/v1/stats');
    const payload = (statsResponse as ApiResponse<StatsPayload>).data;
    const items = payload?.data?.recent_activity ?? [];

    return items.map((entry, index) => normalizeActivityItem(entry, index));
  },

  async fetchActivityMeta(): Promise<ActivityMetaPayload> {
    const response = await api.get<MetaPayload>('/v1/meta');

    return {
      current_user_permissions: response.data?.current_user_permissions ?? [],
    };
  },
};
