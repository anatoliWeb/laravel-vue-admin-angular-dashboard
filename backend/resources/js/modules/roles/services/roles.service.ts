import { api } from '../../../services/api/client';
import type { ApiResponse } from '../../../types/response.types';
import type { UserListItem } from '../../users/types/users.types';
import type { RoleListItem, RolesMetaPayload } from '../types/roles.types';

interface MetaPayload {
  roles: Array<{ id: number; name: string }>;
  role_permissions: Record<string, string[]>;
  current_user_permissions?: string[];
}

const SYSTEM_ROLE_NAMES = new Set(['admin', 'manager', 'user']);

const inferDescription = (name: string): string => {
  if (name === 'admin') return 'Full administrative access across the platform.';
  if (name === 'manager') return 'Operational management with scoped team control.';
  if (name === 'user') return 'Standard product access with limited configuration rights.';
  return 'Custom business role for specific access policies.';
};

/**
 * Roles module service layer.
 *
 * WHY:
 * RBAC views require combining role metadata and user assignments. Isolating
 * this mapping keeps components focused on presentation and scales to future
 * dedicated /roles endpoints without changing UI contracts.
 */
export const rolesService = {
  async fetchRoles(): Promise<RoleListItem[]> {
    const [metaResponse, usersResponse] = await Promise.all([
      api.get<MetaPayload>('/v1/meta'),
      api.get<UserListItem[]>('/v1/users'),
    ]);

    const metaPayload = (metaResponse as ApiResponse<MetaPayload>).data;
    const users = (usersResponse as ApiResponse<UserListItem[]>).data ?? [];

    const rolePermissions = metaPayload?.role_permissions ?? {};

    return (metaPayload?.roles ?? []).map((role) => {
      const normalizedName = role.name.toLowerCase();
      const usersCount = users.filter((user) => user.roles.includes(role.name)).length;
      const permissions = rolePermissions[role.name] ?? [];

      return {
        id: role.id,
        name: role.name,
        description: inferDescription(normalizedName),
        permissions,
        permissions_count: permissions.length,
        users_count: usersCount,
        status: 'active',
        type: SYSTEM_ROLE_NAMES.has(normalizedName) ? 'system' : 'custom',
        created_at: null,
      };
    });
  },

  async fetchPermissionsMeta(): Promise<RolesMetaPayload> {
    const response = await api.get<MetaPayload>('/v1/meta');

    return {
      current_user_permissions: response.data?.current_user_permissions ?? [],
    };
  },
};
