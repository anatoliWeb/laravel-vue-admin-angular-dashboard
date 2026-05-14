export const environment = {
  production: false,
  appName: 'Customer Dashboard (Dev)',
  apiBaseUrl: 'http://localhost:8080/api',
  defaultLocale: 'en',
  enabledLocales: ['en', 'uk', 'de'],
  featureFlags: {
    notifications: true,
    realtimeWidgets: true,
    betaProfile: true,
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
