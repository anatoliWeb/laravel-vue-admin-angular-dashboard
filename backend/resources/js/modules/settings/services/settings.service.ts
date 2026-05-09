import type { SettingsState } from '../types/settings.types';

/**
 * Settings service foundation.
 *
 * WHY:
 * Settings pages require a stable contract that can later map to system-level,
 * tenant-level, and organization-level persistence endpoints. This service
 * currently provides structured seed state and can be replaced by API calls
 * without changing view/component architecture.
 */
export const settingsService = {
  async fetchSettings(currentPermissions: string[]): Promise<SettingsState> {
    const canView = currentPermissions.includes('settings.view') || currentPermissions.includes('users.view');
    const canEdit = currentPermissions.includes('settings.edit');

    return {
      canView,
      canEdit,
      sections: [
        {
          id: 'general',
          title: 'General',
          description: 'Core platform defaults and baseline application identity.',
          fields: [
            { key: 'app_name', label: 'App Name', description: 'Displayed in admin and notifications.', type: 'text', value: 'SaaS Platform', editable: true },
            {
              key: 'default_locale',
              label: 'Default Locale',
              description: 'Fallback locale for new sessions.',
              type: 'select',
              value: 'en',
              options: [
                { label: 'English', value: 'en' },
                { label: 'Українська', value: 'uk' },
                { label: 'Deutsch', value: 'de' },
              ],
              editable: true,
            },
            {
              key: 'timezone',
              label: 'Timezone',
              description: 'Default timezone used by scheduled jobs and reports.',
              type: 'select',
              value: 'UTC',
              options: [
                { label: 'UTC', value: 'UTC' },
                { label: 'Europe/Kyiv', value: 'Europe/Kyiv' },
                { label: 'America/New_York', value: 'America/New_York' },
              ],
              editable: true,
            },
            { key: 'maintenance_mode', label: 'Maintenance Mode', description: 'Temporarily restrict public access.', type: 'toggle', value: false, editable: true },
          ],
        },
        {
          id: 'security',
          title: 'Security',
          description: 'Authentication hardening and access safeguards.',
          fields: [
            { key: 'session_timeout', label: 'Session Timeout (minutes)', description: 'Idle timeout before forced re-authentication.', type: 'number', value: 60, editable: true },
            { key: 'password_policy', label: 'Password Policy', description: 'Policy editor placeholder (coming next).', type: 'text', value: 'Min 12 chars + complexity', editable: false },
            { key: 'mfa_enforcement', label: 'MFA Enforcement', description: 'MFA rollout policy placeholder.', type: 'text', value: 'Optional for now', editable: false },
            { key: 'login_restrictions', label: 'Login Restrictions', description: 'Geo/IP restrictions placeholder.', type: 'text', value: 'Not configured', editable: false },
          ],
        },
        {
          id: 'api',
          title: 'API',
          description: 'API consumption limits and integration behavior.',
          fields: [
            { key: 'token_expiration_days', label: 'Token Expiration (days)', description: 'Default token lifetime for generated credentials.', type: 'number', value: 30, editable: true },
            { key: 'api_rate_limit', label: 'API Rate Limit (req/min)', description: 'Default per-client request budget.', type: 'number', value: 120, editable: true },
            { key: 'webhooks_enabled', label: 'Webhooks', description: 'Webhook management placeholder.', type: 'toggle', value: true, editable: true },
            { key: 'public_api_enabled', label: 'Public API', description: 'Enable/disable public API access.', type: 'toggle', value: true, editable: true },
          ],
        },
        {
          id: 'realtime',
          title: 'Realtime',
          description: 'Broadcasting and event-delivery configuration.',
          fields: [
            { key: 'websocket_status', label: 'WebSocket Status', description: 'Current transport health.', type: 'text', value: 'Ready', editable: false },
            { key: 'queue_status', label: 'Queue Status', description: 'Queue worker runtime status.', type: 'text', value: 'Running', editable: false },
            { key: 'broadcast_driver', label: 'Broadcast Driver', description: 'Configured broadcast backend.', type: 'text', value: 'reverb', editable: false },
            { key: 'realtime_enabled', label: 'Realtime Enabled', description: 'Master switch for realtime delivery.', type: 'toggle', value: true, editable: true },
          ],
        },
        {
          id: 'feature_flags',
          title: 'Feature Flags',
          description: 'Progressive rollout controls for product capabilities.',
          fields: [
            { key: 'ff_notifications', label: 'Enable Notifications', description: 'Activates in-app notification surfaces.', type: 'toggle', value: true, editable: true },
            { key: 'ff_realtime', label: 'Enable Realtime', description: 'Activates realtime event stream UI.', type: 'toggle', value: true, editable: true },
            { key: 'ff_analytics', label: 'Enable Analytics', description: 'Activates analytics widgets and reports.', type: 'toggle', value: true, editable: true },
            { key: 'ff_public_api', label: 'Enable Public API', description: 'Activates external developer API endpoints.', type: 'toggle', value: true, editable: true },
          ],
        },
        {
          id: 'system',
          title: 'System',
          description: 'Runtime and platform-level operational indicators.',
          fields: [
            { key: 'api_status', label: 'API Status', description: 'Service availability signal.', type: 'text', value: 'Online', editable: false },
            { key: 'queue_runtime', label: 'Queue Runtime', description: 'Current queue execution state.', type: 'text', value: 'Healthy', editable: false },
            { key: 'redis_status', label: 'Redis Status', description: 'Cache and pub/sub connectivity.', type: 'text', value: 'Connected', editable: false },
            { key: 'build_version', label: 'Build Version', description: 'Current deployed frontend build.', type: 'text', value: 'v1.0.0-dev', editable: false },
          ],
        },
      ],
    };
  },
};
