export interface PermissionListItem {
  id: number;
  name: string;
  module: string;
  description: string;
  used_by_roles: string[];
  type: 'read' | 'write' | 'manage';
  usage: 'used' | 'unused';
  created_at?: string | null;
}

export interface PermissionsQuery {
  search: string;
  module: string;
  type: 'all' | 'read' | 'write' | 'manage';
  usage: 'all' | 'used' | 'unused';
  page: number;
  perPage: number;
}

export interface PermissionsMetaPayload {
  current_user_permissions: string[];
}
