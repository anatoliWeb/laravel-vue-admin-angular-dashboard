export type SettingScopeType = 'global' | 'role' | 'permission' | 'user';
export type SettingChannel = 'frontend' | 'backend';
export type SettingValueType = 'string' | 'integer' | 'number' | 'boolean' | 'json' | 'array' | 'enum' | 'color' | 'select' | 'textarea' | 'toggle';

export interface ScopeRef {
  id: number;
  name: string;
}

export interface SettingScope {
  type: SettingScopeType;
  user_id: number | null;
  role_id: number | null;
  permission_id: number | null;
  user: ScopeRef | null;
  role: ScopeRef | null;
  permission: ScopeRef | null;
}

export interface SystemSettingRecord {
  id: number;
  key: string;
  label: string;
  group: string;
  description: string | null;
  type: SettingValueType;
  value: unknown;
  default_value: unknown;
  is_frontend: boolean;
  is_backend: boolean;
  priority: number;
  is_active: boolean;
  is_system: boolean;
  scope: SettingScope;
  created_at: string | null;
  updated_at: string | null;
}

export interface EffectiveSetting {
  value: unknown;
  raw_value: string | null;
  type: SettingValueType | string;
  source: SettingScopeType | 'missing';
  setting_id: number | null;
  priority: number | null;
}

export interface SettingsIndexPayload {
  settings: SystemSettingRecord[];
  effective: Record<string, EffectiveSetting>;
  groups: string[];
  types: SettingValueType[];
}

export interface SettingsListParams {
  search?: string;
  group?: string;
  channel?: SettingChannel;
  for_user_id?: number;
}

export interface UpsertSettingPayload {
  key: string;
  label: string;
  group: string;
  description?: string | null;
  type: SettingValueType;
  value?: unknown;
  default_value?: unknown;
  is_frontend?: boolean;
  is_backend?: boolean;
  priority?: number;
  is_active?: boolean;
  is_system?: boolean;
  scope_user_id?: number | null;
  scope_role_id?: number | null;
  scope_permission_id?: number | null;
}

