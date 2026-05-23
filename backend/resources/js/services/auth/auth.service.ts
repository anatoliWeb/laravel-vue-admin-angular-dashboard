import { getToken, removeToken, setToken } from './token.storage';
import { api } from '../api/client';
import type { ApiResponse } from '../../types/response.types';
import type { AuthUser } from '../../types/auth.types';

interface SessionAuthPayload {
  user: AuthUser | null;
  permissions: string[];
}

/**
 * Lightweight auth utility service.
 *
 * Scope in this phase:
 * - token persistence helpers only
 * - no login/logout API implementation yet
 */
export const authService = {
  getToken,
  setToken,
  removeToken,
  login: async (payload: { email: string; password: string; remember?: boolean }): Promise<SessionAuthPayload> => {
    const tokenResponse = await api.post<{ token?: string } & SessionAuthPayload, typeof payload>('/v1/auth/login', payload);
    const tokenPayload = (tokenResponse as ApiResponse<{ token?: string } & SessionAuthPayload>).data ?? { user: null, permissions: [] };

    if (tokenPayload.token) {
      setToken(tokenPayload.token);
    }

    return {
      user: tokenPayload.user ?? null,
      permissions: tokenPayload.permissions ?? [],
    };
  },
  fetchSession: async (): Promise<SessionAuthPayload> => {
    const endpoint = getToken() ? '/v1/auth/me' : '/v1/auth/session/me';
    const response = await api.get<SessionAuthPayload>(endpoint);
    return (response as ApiResponse<SessionAuthPayload>).data ?? { user: null, permissions: [] };
  },
  /**
   * Session logout endpoint for Laravel web guard.
   *
   * WHY:
   * Admin SPA is embedded into Laravel and primarily authenticated via
   * session/cookie auth. We therefore call the canonical web logout route
   * instead of inventing a separate frontend-only logout flow.
   */
  logout: async (): Promise<void> => {
    if (getToken()) {
      await api.post('/v1/auth/logout', {});
      removeToken();
      return;
    }

    await api.post('/v1/auth/session/logout', {});
  },
};
