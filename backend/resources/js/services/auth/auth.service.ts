import { getToken, removeToken, setToken } from './token.storage';

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
};
