export const environment = {
  production: false,
  appName: 'Customer Dashboard (Dev)',
  apiBaseUrl: 'http://localhost:8080/api',
  defaultLocale: 'en',
  featureFlags: {
    notifications: true,
    realtimeWidgets: true,
    betaProfile: true,
  },
  realtime: {
    enabled: true,
    provider: 'reverb',
    wsUrl: 'ws://localhost:8080',
  },
} as const;

