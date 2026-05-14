import { NgModule } from '@angular/core';
import { SharedModule } from '../../shared/shared.module';
import { SettingsRoutingModule } from './settings-routing.module';
import { SettingsHomeComponent } from './pages/settings-home/settings-home.component';
import { SettingsFiltersComponent } from './components/settings-filters.component';
import { SettingsPaginationComponent } from './components/settings-pagination.component';
import { SettingsUpsertModalComponent } from './components/settings-upsert-modal.component';

@NgModule({
  declarations: [
    SettingsHomeComponent,
    SettingsFiltersComponent,
    SettingsPaginationComponent,
    SettingsUpsertModalComponent,
  ],
  imports: [SharedModule, SettingsRoutingModule],
})
export class SettingsModule {}
