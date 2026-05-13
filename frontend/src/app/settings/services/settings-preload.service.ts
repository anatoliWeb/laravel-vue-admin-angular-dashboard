import { Injectable } from '@angular/core';
import { catchError, map, of, tap } from 'rxjs';
import { ApiClientService } from '../../api/services/api-client.service';
import type { ApiResponse } from '../../api/models/api-response.model';

export interface FrontendSettingsPayload {
  settings: Record<string, unknown>;
}

@Injectable({ providedIn: 'root' })
export class SettingsPreloadService {
  private snapshot: FrontendSettingsPayload = { settings: {} };

  constructor(private readonly apiClient: ApiClientService) {}

  preload() {
    return this.apiClient.get<FrontendSettingsPayload>('/v1/settings/preload').pipe(
      map((response: ApiResponse<FrontendSettingsPayload>) => response.data ?? { settings: {} }),
      tap((payload) => {
        this.snapshot = payload;
      }),
      catchError(() => of({ settings: {} })),
    );
  }

  get current(): FrontendSettingsPayload {
    return this.snapshot;
  }
}

