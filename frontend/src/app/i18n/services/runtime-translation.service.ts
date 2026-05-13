import { Injectable } from '@angular/core';
import { catchError, map, of, tap } from 'rxjs';
import { ApiClientService } from '../../api/services/api-client.service';
import type { ApiResponse } from '../../api/models/api-response.model';

export interface RuntimeTranslationPayload {
  locale: string;
  fallback_locale: string;
  translations: Record<string, Record<string, string>>;
}

@Injectable({ providedIn: 'root' })
export class RuntimeTranslationService {
  private payload: RuntimeTranslationPayload | null = null;

  constructor(private readonly apiClient: ApiClientService) {}

  preload(locale: string) {
    return this.apiClient
      .get<RuntimeTranslationPayload>('/v1/translations', {
        params: { locale, frontend: 1 },
      })
      .pipe(
        map((response: ApiResponse<RuntimeTranslationPayload>) => response.data ?? null),
        tap((payload) => {
          this.payload = payload;
        }),
        catchError(() => of(null)),
      );
  }

  get snapshot(): RuntimeTranslationPayload | null {
    return this.payload;
  }
}

