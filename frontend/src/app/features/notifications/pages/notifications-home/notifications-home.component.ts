import { Component, OnInit } from '@angular/core';
import { map } from 'rxjs';
import { RealtimeService } from '../../../../realtime/services/realtime.service';
import { NotificationsService } from '../../services/notifications.service';

@Component({
  selector: 'app-notifications-home',
  templateUrl: './notifications-home.component.html',
  styleUrls: ['./notifications-home.component.scss'],
  standalone: false,
})
export class NotificationsHomeComponent implements OnInit {
  loading = true;
  readonly items$;
  readonly unreadCount$;

  constructor(
    private readonly notifications: NotificationsService,
    private readonly realtime: RealtimeService,
  ) {
    this.items$ = this.notifications.items$;
    this.unreadCount$ = this.notifications.items$.pipe(map((items) => items.filter((item) => !item.read).length));
  }

  ngOnInit(): void {
    this.realtime.connect();
    this.loading = false;
  }

  markAsRead(id: string): void {
    this.notifications.markAsRead(id);
  }

  markAllAsRead(): void {
    this.notifications.markAllAsRead();
  }
}
