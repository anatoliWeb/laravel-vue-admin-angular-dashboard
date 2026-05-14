import { Inject, Injectable } from '@angular/core';
import { Observable, catchError, finalize, map, of, shareReplay, tap } from 'rxjs';
import { ApiClientService } from '../../api/services/api-client.service';
import type { ApiResponse } from '../../api/models/api-response.model';
import { APP_CONFIG, AppEnvironment } from '../../core/tokens/app-config.token';

export interface FrontendSettingsPayload {
  settings: Record<string, unknown>;
}

@Injectable({ providedIn: 'root' })
export class SettingsPreloadService {
  private snapshot: FrontendSettingsPayload = { settings: {} };
  private loaded = false;
  private inFlight$: Observable<FrontendSettingsPayload> | null = null;

  constructor(
    private readonly apiClient: ApiClientService,
    @Inject(APP_CONFIG) private readonly config: AppEnvironment,
  ) {}

  preload(force = false): Observable<FrontendSettingsPayload> {
    if (!force && this.loaded) {
      if (!this.config.production) {
        console.debug('[SettingsPreload] cache hit');
      }
      return of(this.snapshot);
    }

    if (!force && this.inFlight$) {
      if (!this.config.production) {
        console.debug('[SettingsPreload] join in-flight');
      }
      return this.inFlight$;
    }
    if (!this.config.production) {
      console.debug('[SettingsPreload] request');
    }

    this.inFlight$ = this.apiClient.get<FrontendSettingsPayload>('/v1/settings/preload').pipe(
      map((response: ApiResponse<FrontendSettingsPayload>) => response.data ?? { settings: {} }),
      tap((payload) => {
        this.snapshot = payload;
        this.loaded = true;
      }),
      catchError(() => of({ settings: {} })),
      finalize(() => {
        this.inFlight$ = null;
      }),
      shareReplay(1),
    );

    return this.inFlight$;
  }

  get current(): FrontendSettingsPayload {
    return this.snapshot;
  }
}
