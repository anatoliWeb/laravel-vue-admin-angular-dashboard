import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NotificationsHomeComponent } from './pages/notifications-home/notifications-home.component';

const routes: Routes = [{ path: '', component: NotificationsHomeComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class NotificationsRoutingModule {}

