import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { SharedModule } from '../shared/shared.module';
import { DashboardShellComponent } from './components/dashboard-shell/dashboard-shell.component';

@NgModule({
  declarations: [DashboardShellComponent],
  imports: [RouterModule, SharedModule],
  exports: [DashboardShellComponent],
})
export class LayoutModule {}

