import { Injectable } from '@angular/core';
import { map } from 'rxjs';
import { ApiClientService } from '../../api/services/api-client.service';
import type { ApiResponse } from '../../api/models/api-response.model';
import type { SessionAuthPayload } from '../models/session-auth.model';

@Injectable({ providedIn: 'root' })
export class AuthSessionService {
  constructor(private readonly apiClient: ApiClientService) {}

  getSession() {
    return this.apiClient
      .get<SessionAuthPayload>('/v1/auth/session/me')
      .pipe(map((response: ApiResponse<SessionAuthPayload>) => response.data ?? { user: null, permissions: [] }));
  }

  login(credentials: { email: string; password: string; remember: boolean }) {
    return this.apiClient
      .post<SessionAuthPayload, { email: string; password: string; remember: boolean }>(
        '/v1/auth/session/login',
        credentials,
      )
      .pipe(map((response: ApiResponse<SessionAuthPayload>) => response.data ?? { user: null, permissions: [] }));
  }

  logout() {
    return this.apiClient.post<unknown, Record<string, never>>('/v1/auth/session/logout', {});
  }
}

