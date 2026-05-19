import { Component, OnInit } from '@angular/core';
import { RealtimeService } from '../../../../realtime/services/realtime.service';
import { NotificationsService } from '../../services/notifications.service';
import { PermissionService } from '../../../../rbac/services/permission.service';

@Component({
  selector: 'app-notifications-home',
  templateUrl: './notifications-home.component.html',
  styleUrls: ['./notifications-home.component.scss'],
  standalone: false,
})
export class NotificationsHomeComponent implements OnInit {
  readonly items$;
  readonly unreadCount$;
  readonly loading$;
  readonly error$;
  readonly canDelete: boolean;
  isMutating = false;

  constructor(
    private readonly notifications: NotificationsService,
    private readonly realtime: RealtimeService,
    private readonly permissionService: PermissionService,
  ) {
    this.items$ = this.notifications.items$;
    this.unreadCount$ = this.notifications.unreadCount$;
    this.loading$ = this.notifications.loading$;
    this.error$ = this.notifications.error$;
    this.canDelete = this.permissionService.hasPermission('notifications.delete');
  }

  ngOnInit(): void {
    this.realtime.connect();
    this.notifications.init();
  }

  async refresh(): Promise<void> {
    await this.notifications.refresh();
  }

  async markAsRead(id: string): Promise<void> {
    this.isMutating = true;
    try {
      await this.notifications.markAsRead(id);
    } finally {
      this.isMutating = false;
    }
  }

  async markAllAsRead(): Promise<void> {
    this.isMutating = true;
    try {
      await this.notifications.markAllAsRead();
    } finally {
      this.isMutating = false;
    }
  }

  async delete(id: string): Promise<void> {
    this.isMutating = true;
    try {
      await this.notifications.delete(id);
    } finally {
      this.isMutating = false;
    }
  }
}
