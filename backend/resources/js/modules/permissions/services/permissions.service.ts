import { api } from '../../../services/api/client';
import type { ApiResponse } from '../../../types/response.types';
import type { PermissionListItem, PermissionsMetaPayload } from '../types/permissions.types';

interface MetaPayload {
  permissions: Array<{ id: number; name: string }>;
  role_permissions: Record<string, string[]>;
  current_user_permissions?: string[];
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

const inferDescription = (name: string): string => {
  const [module, action = 'manage'] = name.split('.');
  return `Allows ${action.replaceAll('_', ' ')} access in ${module} module.`;
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
    const response = await api.get<MetaPayload>('/v1/meta');
    const payload = (response as ApiResponse<MetaPayload>).data;

    const rolePermissions = payload?.role_permissions ?? {};
    const permissionRolesMap = new Map<string, string[]>();

    Object.entries(rolePermissions).forEach(([roleName, permissionNames]) => {
      permissionNames.forEach((permissionName) => {
        const current = permissionRolesMap.get(permissionName) ?? [];
        permissionRolesMap.set(permissionName, [...current, roleName]);
      });
    });

    return (payload?.permissions ?? []).map((permission) => {
      const usedByRoles = permissionRolesMap.get(permission.name) ?? [];

      return {
        id: permission.id,
        name: permission.name,
        module: inferModule(permission.name),
        description: inferDescription(permission.name),
        used_by_roles: usedByRoles,
        type: inferType(permission.name),
        usage: usedByRoles.length > 0 ? 'used' : 'unused',
        created_at: null,
      };
    });
  },

  async fetchPermissionsMeta(): Promise<PermissionsMetaPayload> {
    const response = await api.get<MetaPayload>('/v1/meta');

    return {
      current_user_permissions: response.data?.current_user_permissions ?? [],
    };
  },
};
