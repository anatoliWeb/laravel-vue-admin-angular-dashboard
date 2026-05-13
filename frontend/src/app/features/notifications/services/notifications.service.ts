import { Injectable } from '@angular/core';
import { of } from 'rxjs';
import type { NotificationPreview } from '../models/notification.model';

@Injectable({ providedIn: 'root' })
export class NotificationsService {
  list() {
    return of<NotificationPreview[]>([]);
  }
}

