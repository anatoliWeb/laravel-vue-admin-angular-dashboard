import type { RealtimeStatusMetric } from './realtime.types';

/**
 * Realtime channel contracts prepared for future Reverb/Echo integration.
 *
 * WHY THIS LAYER EXISTS:
 * Centralizing channel names avoids tight coupling between UI widgets and
 * transport details, making it safer to migrate from mock counters to live
 * subscriptions without rewriting topbar components.
 */
export const REALTIME_CHANNELS = {
  backendOnline: 'presence.backend.online',
  frontendOnline: 'presence.frontend.online',
  systemNotifications: 'system.notifications',
} as const;

export const REALTIME_EVENTS = {
  systemNotification: '.system.notification',
} as const;

export const REALTIME_METRIC_KEYS: ReadonlyArray<RealtimeStatusMetric['key']> = [
  'backend_online',
  'frontend_online',
];
