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
    enabled: false,
    provider: 'reverb',
    wsUrl: '',
  },
} as const;

