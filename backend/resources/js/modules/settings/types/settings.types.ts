export type SettingValue = string | number | boolean;

export interface SettingField {
  key: string;
  label: string;
  description: string;
  type: 'text' | 'number' | 'select' | 'toggle';
  value: SettingValue;
  options?: Array<{ label: string; value: string }>;
  editable?: boolean;
}

export interface SettingsSection {
  id: 'general' | 'security' | 'api' | 'realtime' | 'feature_flags' | 'system';
  title: string;
  description: string;
  fields: SettingField[];
}

export interface SettingsState {
  sections: SettingsSection[];
  canView: boolean;
  canEdit: boolean;
}
