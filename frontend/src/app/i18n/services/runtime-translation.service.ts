import { Injectable } from '@angular/core';
import { catchError, map, of, tap } from 'rxjs';
import { BehaviorSubject } from 'rxjs';
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
  private readonly revisionSubject = new BehaviorSubject<number>(0);
  readonly revision$ = this.revisionSubject.asObservable();

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
          this.revisionSubject.next(this.revisionSubject.value + 1);
        }),
        catchError(() => of(null)),
      );
  }

  get snapshot(): RuntimeTranslationPayload | null {
    return this.payload;
  }
}
