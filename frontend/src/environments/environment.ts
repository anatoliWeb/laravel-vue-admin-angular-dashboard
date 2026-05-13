export const environment = {
  production: true,
  appName: 'Customer Dashboard',
  apiBaseUrl: '/api',
  defaultLocale: 'en',
  featureFlags: {
    notifications: true,
    realtimeWidgets: false,
    betaProfile: false,
  },
  realtime: {
    enabled: true,
    provider: 'reverb',
    appKey: 'app-key',
    wsHost: 'localhost',
    wsPort: 6001,
    forceTLS: false,
  },
} as const;
