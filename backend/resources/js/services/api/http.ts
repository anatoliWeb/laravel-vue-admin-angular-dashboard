import axios, { type AxiosInstance } from 'axios';

import { attachInterceptors } from './interceptors';
import { getToken } from '../auth/token.storage';

const baseURL = import.meta.env.VITE_API_URL || import.meta.env.VITE_API_BASE_URL || '/api';

/**
 * Centralized Axios instance.
 *
 * WHY:
 * A single HTTP boundary keeps transport behavior predictable across modules:
 * - shared base URL and timeout
 * - shared headers
 * - shared auth header injection
 * - shared error normalization via interceptors
 *
 * Components and views should never call axios directly.
 */
export const http: AxiosInstance = axios.create({
  baseURL,
  timeout: 15_000,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

http.interceptors.request.use((config) => {
  const token = getToken();

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

attachInterceptors(http);

