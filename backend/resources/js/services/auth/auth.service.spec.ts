import { beforeEach, describe, expect, it, vi } from 'vitest';

const apiMock = vi.hoisted(() => ({
  post: vi.fn(),
  get: vi.fn(),
}));

vi.mock('../api/client', () => ({
  api: apiMock,
}));

describe('authService token/session behavior', async () => {
  const { authService } = await import('./auth.service');

  beforeEach(() => {
    vi.clearAllMocks();
    window.localStorage.clear();
  });

  it('login uses token endpoint and stores bearer token', async () => {
    apiMock.post.mockResolvedValue({
      data: {
        token: 'plain-token',
        user: { id: 1, name: 'Admin', email: 'admin@example.com' },
        permissions: ['chat.view'],
      },
    });

    const payload = await authService.login({
      email: 'admin@example.com',
      password: 'secret',
    });

    expect(apiMock.post).toHaveBeenCalledWith('/v1/auth/login', {
      email: 'admin@example.com',
      password: 'secret',
    });
    expect(window.localStorage.getItem('admin_access_token')).toBe('plain-token');
    expect(payload.user?.id).toBe(1);
    expect(payload.permissions).toEqual(['chat.view']);
  });

  it('fetchSession uses /v1/auth/me when token exists and session endpoint otherwise', async () => {
    apiMock.get.mockResolvedValue({
      data: {
        user: { id: 2, name: 'Ops', email: 'ops@example.com' },
        permissions: ['notifications.view'],
      },
    });

    window.localStorage.setItem('admin_access_token', 'token-a');
    await authService.fetchSession();
    expect(apiMock.get).toHaveBeenCalledWith('/v1/auth/me');

    apiMock.get.mockClear();
    window.localStorage.removeItem('admin_access_token');
    await authService.fetchSession();
    expect(apiMock.get).toHaveBeenCalledWith('/v1/auth/session/me');
  });

  it('logout uses token endpoint when token exists and removes token', async () => {
    window.localStorage.setItem('admin_access_token', 'token-b');
    apiMock.post.mockResolvedValue({ data: {} });

    await authService.logout();

    expect(apiMock.post).toHaveBeenCalledWith('/v1/auth/logout', {});
    expect(window.localStorage.getItem('admin_access_token')).toBeNull();
  });
});

