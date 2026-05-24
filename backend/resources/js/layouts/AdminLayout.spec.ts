import { flushPromises, shallowMount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const hydrateSessionMock = vi.fn();
const hasPermissionMock = vi.fn(() => true);
const routerReplaceMock = vi.fn();
const loadUnreadCountMock = vi.fn(() => Promise.resolve());
const initRealtimeBridgeMock = vi.fn();
const connectMock = vi.fn();
const getMetricsMock = vi.fn(() => []);
const onStatusChangeMock = vi.fn(() => () => undefined);
const onSystemNotificationMock = vi.fn(() => () => undefined);
const joinPresenceMock = vi.fn(() => () => undefined);
const disconnectMock = vi.fn();

vi.mock('vue-router', () => ({
  useRoute: () => ({ name: 'dashboard' }),
  useRouter: () => ({ replace: routerReplaceMock, push: vi.fn() }),
  RouterLink: {
    name: 'RouterLink',
    props: ['to'],
    template: '<a><slot /></a>',
  },
}));

vi.mock('vue-i18n', async (importOriginal) => {
  const actual = await importOriginal<typeof import('vue-i18n')>();
  return {
    ...actual,
    useI18n: () => ({
      t: (key: string) => key,
    }),
  };
});

vi.mock('../stores/auth.store', () => ({
  useAuthStore: () => ({
    user: { id: 10, name: 'Admin' },
    permissions: ['notifications.view'],
    hydrateSession: hydrateSessionMock,
    hasPermission: hasPermissionMock,
    hasAnyPermission: vi.fn(() => true),
    logout: vi.fn(),
  }),
}));

vi.mock('../stores/translation.store', () => ({
  useTranslationStore: () => ({
    locale: 'en',
    switchLocale: vi.fn(() => Promise.resolve()),
  }),
}));

vi.mock('../modules/notifications/services/notifications.service', () => ({
  notificationsService: {
    unreadCount: { value: 0 },
    initRealtimeBridge: initRealtimeBridgeMock,
    loadUnreadCount: loadUnreadCountMock,
    disposeRealtimeBridge: vi.fn(),
  },
}));

vi.mock('../shared/services/realtime/realtime.client', () => ({
  realtimeClient: {
    connect: connectMock,
    getMetrics: getMetricsMock,
    onStatusChange: onStatusChangeMock,
    onSystemNotification: onSystemNotificationMock,
    joinPresence: joinPresenceMock,
    disconnect: disconnectMock,
  },
}));

vi.mock('../shared/services/realtime/realtime.channels', () => ({
  REALTIME_CHANNELS: {
    presenceOnline: 'presence-online',
    presenceDashboard: 'presence-dashboard',
  },
}));

describe('AdminLayout auth bootstrap guard', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('redirects to login and skips protected loading when hydrate fails', async () => {
    hydrateSessionMock.mockResolvedValue(false);
    const { default: AdminLayout } = await import('./AdminLayout.vue');

    shallowMount(AdminLayout, {
      global: {
        stubs: {
          BaseIconButton: true,
          BaseLanguageSwitcher: true,
          BaseRealtimeStatus: true,
          BaseTopbarSearch: true,
          BaseUserDropdown: true,
          RouterView: true,
          'router-link': true,
        },
      },
    });
    await flushPromises();

    expect(routerReplaceMock).toHaveBeenCalledWith('/login');
    expect(loadUnreadCountMock).not.toHaveBeenCalled();
    expect(connectMock).not.toHaveBeenCalled();
  }, 10000);

  it('starts realtime and unread loading after successful hydrate', async () => {
    hydrateSessionMock.mockResolvedValue(true);
    const { default: AdminLayout } = await import('./AdminLayout.vue');

    shallowMount(AdminLayout, {
      global: {
        stubs: {
          BaseIconButton: true,
          BaseLanguageSwitcher: true,
          BaseRealtimeStatus: true,
          BaseTopbarSearch: true,
          BaseUserDropdown: true,
          RouterView: true,
          'router-link': true,
        },
      },
    });
    await flushPromises();

    expect(connectMock).toHaveBeenCalled();
    expect(initRealtimeBridgeMock).toHaveBeenCalled();
    expect(loadUnreadCountMock).toHaveBeenCalled();
  });
});
