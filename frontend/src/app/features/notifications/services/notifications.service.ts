import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import type { NotificationPreview } from '../models/notification.model';
import { RealtimeService } from '../../../realtime/services/realtime.service';

@Injectable({ providedIn: 'root' })
export class NotificationsService {
  private readonly itemsSubject = new BehaviorSubject<NotificationPreview[]>([]);
  readonly items$ = this.itemsSubject.asObservable();

  constructor(private readonly realtimeService: RealtimeService) {
    this.realtimeService.events$.subscribe((events) => {
      const next = events.map((event, index) => ({
        id: `${event.created_at}-${index}`,
        type: (event.type as NotificationPreview['type']) || 'info',
        title: event.title,
        message: event.message,
        createdAt: event.created_at,
        read: false,
      }));
      this.itemsSubject.next(next);
    });
  }

  markAsRead(id: string): void {
    this.itemsSubject.next(
      this.itemsSubject.value.map((item) => (item.id === id ? { ...item, read: true } : item)),
    );
  }

  markAllAsRead(): void {
    this.itemsSubject.next(this.itemsSubject.value.map((item) => ({ ...item, read: true })));
  }

  unreadCount(): number {
    return this.itemsSubject.value.filter((item) => !item.read).length;
  }
}
