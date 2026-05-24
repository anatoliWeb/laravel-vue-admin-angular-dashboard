import { describe, expect, it, vi } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import { nextTick } from 'vue';
import DashboardPage from './DashboardPage.vue';

const apiMock = vi.hoisted(() => ({
  get: vi.fn(),
}));

const useCachedRequestMock = vi.hoisted(() => vi.fn());

vi.mock('../../../services/api/client', () => ({
  api: apiMock,
}));

vi.mock('../../../shared/cache', () => ({
  cacheStore: {
    has: vi.fn().mockReturnValue(false),
  },
  useCachedRequest: useCachedRequestMock,
}));

vi.mock('../../../shared/services/realtime/realtime.client', () => ({
  realtimeClient: {
    onSystemNotification: vi.fn(() => () => {}),
  },
}));

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key: string) => key,
    locale: 'en',
  }),
}));

vi.mock('vue-router', () => ({
  useRoute: () => ({
    fullPath: '/dashboard',
  }),
}));

describe('DashboardPage meta bootstrap loading', () => {
  it('loads lightweight /v1/meta/bootstrap instead of full /v1/meta', async () => {
    apiMock.get.mockImplementation(async (url: string) => {
      if (url === '/v1/stats') {
        return { data: { users: 1, admins: 1, managers: 0, tokens: 0, users_with_direct_permissions: 0, recent_activity: [] } };
      }

      if (url === '/v1/meta/bootstrap') {
        return { data: { current_user: { id: 1, name: 'Admin', email: 'admin@example.com', roles: [{ id: 1, name: 'admin' }] }, current_user_permissions: ['users.view'] } };
      }

      return { data: null };
    });

    useCachedRequestMock.mockImplementation(async ({ request, onBackgroundUpdate }: { request: () => Promise<unknown>; onBackgroundUpdate?: (value: unknown) => void }) => {
      const data = await request();
      if (onBackgroundUpdate) {
        onBackgroundUpdate(data);
      }
      return { data, revalidating: false };
    });

    shallowMount(DashboardPage, {
      global: {
        stubs: {
          BaseStatCard: true,
          Doughnut: true,
          Bar: true,
          Line: true,
        },
      },
    });

    await nextTick();
    await nextTick();

    expect(apiMock.get).toHaveBeenCalledWith('/v1/meta/bootstrap');
    expect(apiMock.get).not.toHaveBeenCalledWith('/v1/meta');
  });
});
