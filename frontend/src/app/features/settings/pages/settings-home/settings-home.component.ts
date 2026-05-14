import { Component, OnInit } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { PermissionService } from '../../../../rbac/services/permission.service';
import { RealtimeService } from '../../../../realtime/services/realtime.service';
import { TranslationFacadeService } from '../../../../i18n/services/translation-facade.service';
import { LocaleService } from '../../../../i18n/services/locale.service';
import { SettingsService } from '../../services/settings.service';
import type { SettingItem, SettingsFilters, SettingsListMeta, SettingsListPayload, SettingUpsertPayload } from '../../models/settings.model';

@Component({
  selector: 'app-settings-home',
  templateUrl: './settings-home.component.html',
  styleUrls: ['./settings-home.component.scss'],
  standalone: false,
})
export class SettingsHomeComponent implements OnInit {
  loading = false;
  saving = false;
  items: SettingItem[] = [];
  effective: SettingsListPayload['effective'] = {};
  groups: string[] = [];
  types: string[] = [];
  locales: string[] = [];
  meta: SettingsListMeta = { current_page: 1, last_page: 1, per_page: 15, total: 0 };
  filters: SettingsFilters = {
    search: '',
    group: '',
    type: '',
    is_active: '',
    channel: '',
    is_public: '',
    is_encrypted: '',
    page: 1,
    per_page: 15,
  };

  selected: SettingItem | null = null;
  modalOpen = false;
  modalMode: 'create' | 'edit' | 'view' = 'view';
  realtimeConnected = false;

  constructor(
    private readonly settingsService: SettingsService,
    private readonly permissionService: PermissionService,
    private readonly realtimeService: RealtimeService,
    private readonly t: TranslationFacadeService,
    private readonly localeService: LocaleService,
  ) {
    this.locales = [...this.localeService.enabledLocales];
  }

  get canCreate(): boolean {
    return this.permissionService.can('settings.create') || this.permissionService.hasRole('admin');
  }
  get canEdit(): boolean {
    return this.permissionService.can('settings.edit') || this.permissionService.hasRole('admin');
  }
  get canDelete(): boolean {
    return this.permissionService.can('settings.delete') || this.permissionService.hasRole('admin');
  }

  ngOnInit(): void {
    this.realtimeService.status$.subscribe((state) => {
      this.realtimeConnected = state.connected;
    });
    void this.refresh();
  }

  async refresh(): Promise<void> {
    this.loading = true;
    try {
      const payload = await firstValueFrom(this.settingsService.list(this.filters));
      this.items = payload.settings;
      this.effective = payload.effective;
      this.groups = payload.groups;
      this.types = payload.types;
      this.meta = payload.meta;
    } finally {
      this.loading = false;
    }
  }

  onFiltersChange(change: Partial<SettingsFilters>): void {
    this.filters = { ...this.filters, ...change, page: 1 };
    void this.refresh();
  }

  goToPage(page: number): void {
    this.filters = { ...this.filters, page };
    void this.refresh();
  }

  openCreate(): void {
    this.selected = null;
    this.modalMode = 'create';
    this.modalOpen = true;
  }

  openEdit(item: SettingItem): void {
    this.selected = item;
    this.modalMode = 'edit';
    this.modalOpen = true;
  }

  openView(item: SettingItem): void {
    this.selected = item;
    this.modalMode = 'view';
    this.modalOpen = true;
  }

  closeModal(): void {
    this.modalOpen = false;
  }

  async persist(payload: SettingUpsertPayload): Promise<void> {
    this.saving = true;
    try {
      if (this.modalMode === 'create') {
        await firstValueFrom(this.settingsService.create(payload));
      } else if (this.selected) {
        await firstValueFrom(this.settingsService.update(this.selected.id, payload));
      }
      this.closeModal();
      await this.refresh();
    } finally {
      this.saving = false;
    }
  }

  async remove(item: SettingItem): Promise<void> {
    if (!confirm(this.t.t('settings.confirmDelete', `Delete setting "${item.label}"?`))) return;
    await firstValueFrom(this.settingsService.delete(item.id));
    await this.refresh();
  }

  formatEffective(key: string): string {
    const value = this.effective[key]?.value;
    if (value === null || value === undefined) return '-';
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value);
  }

  trackById(_: number, item: SettingItem): number {
    return item.id;
  }
}
