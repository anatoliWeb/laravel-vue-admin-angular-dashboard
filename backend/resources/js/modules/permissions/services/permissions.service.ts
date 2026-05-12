import { api } from '../../../services/api/client';
import type { ApiResponse } from '../../../types/response.types';
import type { PermissionListItem, PermissionsMetaPayload } from '../types/permissions.types';

interface MetaPayload {
  permissions: Array<{ id: number; name: string; label?: string; description?: string | null; translations?: Record<string, { label: string; description: string | null }> }>;
  role_permissions: Record<string, string[]>;
  current_user_permissions?: string[];
}

interface PermissionApiItem {
  id: number;
  name: string;
  label: string;
  description: string | null;
  translations?: Record<string, { label: string; description: string | null }>;
  module?: string;
  used_by_roles?: string[];
  type?: 'read' | 'write' | 'manage';
  usage?: 'used' | 'unused';
  created_at?: string | null;
}

const inferModule = (permissionName: string): string => {
  return permissionName.split('.')[0] || 'system';
};

const inferType = (permissionName: string): 'read' | 'write' | 'manage' => {
  const suffix = permissionName.split('.').slice(1).join('.');
  if (suffix.includes('view') || suffix.includes('list') || suffix.includes('show')) {
    return 'read';
  }

  if (suffix.includes('create') || suffix.includes('edit') || suffix.includes('update') || suffix.includes('delete')) {
    return 'write';
  }

  return 'manage';
};

/**
 * Permissions module service.
 *
 * WHY:
 * RBAC permission screens require normalized metadata (grouping, usage by role,
 * and action type). Centralizing this mapping keeps page components focused on
 * rendering and prepares easy switch to a dedicated permissions endpoint later.
 */
export const permissionsService = {
  async fetchPermissions(): Promise<PermissionListItem[]> {
    const response = await api.get<PermissionApiItem[]>('/v1/permissions');
    const payload = (response as ApiResponse<PermissionApiItem[]>).data ?? [];

    return payload.map((permission) => {
      const usedByRoles = permission.used_by_roles ?? [];

      return {
        id: permission.id,
        name: permission.name,
        label: permission.label ?? permission.name,
        translations: permission.translations,
        module: permission.module ?? inferModule(permission.name),
        description: permission.description ?? null,
        used_by_roles: usedByRoles,
        type: permission.type ?? inferType(permission.name),
        usage: permission.usage ?? (usedByRoles.length > 0 ? 'used' : 'unused'),
        created_at: permission.created_at ?? null,
      };
    });
  },

  async fetchPermissionsMeta(): Promise<PermissionsMetaPayload> {
    const response = await api.get<MetaPayload>('/v1/meta');

    return {
      current_user_permissions: response.data?.current_user_permissions ?? [],
    };
  },

  async createPermission(payload: {
    name: string;
    description?: string;
    translations?: Record<string, { label?: string; description?: string }>;
  }): Promise<PermissionListItem> {
    const response = await api.post<PermissionApiItem, typeof payload>('/v1/permissions', payload);
    const item = response.data as PermissionApiItem;
    return {
      id: item.id,
      name: item.name,
      label: item.label ?? item.name,
      translations: item.translations,
      module: item.module ?? inferModule(item.name),
      description: item.description ?? null,
      used_by_roles: item.used_by_roles ?? [],
      type: item.type ?? inferType(item.name),
      usage: item.usage ?? 'unused',
      created_at: item.created_at ?? null,
    };
  },

  async updatePermission(
    permissionId: number,
    payload: {
      description?: string;
      translations?: Record<string, { label?: string; description?: string }>;
    },
  ): Promise<PermissionListItem> {
    const response = await api.put<PermissionApiItem, typeof payload>(`/v1/permissions/${permissionId}`, payload);
    const item = response.data as PermissionApiItem;
    return {
      id: item.id,
      name: item.name,
      label: item.label ?? item.name,
      translations: item.translations,
      module: item.module ?? inferModule(item.name),
      description: item.description ?? null,
      used_by_roles: item.used_by_roles ?? [],
      type: item.type ?? inferType(item.name),
      usage: item.usage ?? ((item.used_by_roles?.length ?? 0) > 0 ? 'used' : 'unused'),
      created_at: item.created_at ?? null,
    };
  },
};
