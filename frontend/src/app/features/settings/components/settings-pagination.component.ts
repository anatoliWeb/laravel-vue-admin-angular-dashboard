Simport { Component, EventEmitter, Input, Output } from '@angular/core';
import type { SettingsListMeta } from '../models/settings.model';

@Component({
  selector: 'app-settings-pagination',
  templateUrl: './settings-pagination.component.html',
  styleUrls: ['./settings-pagination.component.scss'],
  standalone: false,
})
export class SettingsPaginationComponent {
  @Input({ required: true }) meta!: SettingsListMeta;
  @Output() pageChange = new EventEmitter<number>();

  get startRow(): number {
    return this.meta.total === 0 ? 0 : (this.meta.current_page - 1) * this.meta.per_page + 1;
  }

  get endRow(): number {
    return Math.min(this.meta.current_page * this.meta.per_page, this.meta.total);
  }

  go(page: number): void {
    this.pageChange.emit(page);
  }
}
