import { Injectable } from '@angular/core';
import { of } from 'rxjs';
import type { SettingsGroupPreview } from '../models/settings.model';

@Injectable({ providedIn: 'root' })
export class SettingsService {
  listGroups() {
    return of<SettingsGroupPreview[]>([
      { group: 'general', entries: 0 },
      { group: 'security', entries: 0 },
    ]);
  }
}

