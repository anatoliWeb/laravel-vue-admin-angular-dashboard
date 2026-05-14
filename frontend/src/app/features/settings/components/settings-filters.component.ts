import { Component, EventEmitter, Input, Output } from '@angular/core';
import type { SettingsFilters } from '../models/settings.model';

@Component({
  selector: 'app-settings-filters',
  templateUrl: './settings-filters.component.html',
  styleUrls: ['./settings-filters.component.scss'],
  standalone: false,
})
export class SettingsFiltersComponent {
  @Input({ required: true }) filters!: SettingsFilters;
  @Input() groups: string[] = [];
  @Input() types: string[] = [];
  @Output() filtersChange = new EventEmitter<Partial<SettingsFilters>>();

  emitChange<K extends keyof SettingsFilters>(key: K, value: SettingsFilters[K]): void {
    this.filtersChange.emit({ [key]: value } as Partial<SettingsFilters>);
  }

  asValue(event: Event): string {
    return (event.target as HTMLSelectElement).value;
  }

  asBooleanFilter(event: Event): '' | 'true' | 'false' {
    const value = (event.target as HTMLSelectElement).value;
    if (value === 'true' || value === 'false') return value;
    return '';
  }

  asChannel(event: Event): '' | 'frontend' | 'backend' {
    const value = (event.target as HTMLSelectElement).value;
    if (value === 'frontend' || value === 'backend') return value;
    return '';
  }
}
