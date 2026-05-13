import { Component } from '@angular/core';

@Component({
  selector: 'app-notifications-home',
  template: `
    <ui-card>
      <h2>Notifications</h2>
      <p>Notification center foundation prepared for realtime and async event streams.</p>
    </ui-card>
  `,
  styles: [`
    h2{margin:0 0 6px;color:#f8fafc}
    p{margin:0;color:#94a3b8}
  `],
  standalone: false,
})
export class NotificationsHomeComponent {}

