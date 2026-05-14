import { Injectable } from '@angular/core';
import { map } from 'rxjs';
import { ApiClientService } from '../../../api/services/api-client.service';
import type { ApiResponse } from '../../../api/models/api-response.model';
import type { SettingItem, SettingsFilters, SettingsListPayload, SettingUpsertPayload } from '../models/settings.model';

@Injectable({ providedIn: 'root' })
export class SettingsService {
  constructor(private readonly apiClient: ApiClientService) {}

  list(filters: SettingsFilters) {
    return this.apiClient.get<SettingsListPayload>('/v1/settings', {
      params: {
        search: filters.search,
        group: filters.group,
        type: filters.type,
        is_active: filters.is_active,
        channel: filters.channel,
        is_public: filters.is_public,
        is_encrypted: filters.is_encrypted,
        page: filters.page,
        per_page: filters.per_page,
      },
    }).pipe(
      map((response: ApiResponse<SettingsListPayload>) => response.data as SettingsListPayload),
    );
  }

  create(payload: SettingUpsertPayload) {
    return this.apiClient.post<SettingItem, SettingUpsertPayload>('/v1/settings', payload).pipe(
      map((response: ApiResponse<SettingItem>) => response.data as SettingItem),
    );
  }

  update(id: number, payload: Partial<SettingUpsertPayload>) {
    return this.apiClient.post<SettingItem, Partial<SettingUpsertPayload>>(`/v1/settings/${id}?_method=PATCH`, payload).pipe(
      map((response: ApiResponse<SettingItem>) => response.data as SettingItem),
    );
  }

  delete(id: number) {
    return this.apiClient.post<{ deleted: boolean }, Record<string, never>>(`/v1/settings/${id}?_method=DELETE`, {}).pipe(
      map((response: ApiResponse<{ deleted: boolean }>) => response.data?.deleted ?? false),
    );
  }
}
