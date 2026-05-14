import { Component, OnDestroy, OnInit } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { map } from 'rxjs';
import { ApiClientService } from '../../../../api/services/api-client.service';
import { AuthStateService } from '../../../../core/services/auth-state.service';
import { PermissionService } from '../../../../rbac/services/permission.service';
import { RealtimeService } from '../../../../realtime/services/realtime.service';

@Component({
  selector: 'app-dashboard-home',
  templateUrl: './dashboard-home.component.html',
  styleUrls: ['./dashboard-home.component.scss'],
  standalone: false,
})
export class DashboardHomeComponent implements OnInit, OnDestroy {
  readonly realtimeStatus$;
  readonly realtimeEvents$;
  readonly realtimeCount$;

  isDispatching = false;
  readonly user$;
  readonly permissions$;
  readonly roles$;
  readonly isAdmin: boolean;

  constructor(
    private readonly realtime: RealtimeService,
    private readonly apiClient: ApiClientService,
    private readonly authState: AuthStateService,
    private readonly permissionService: PermissionService,
  ) {
    this.realtimeStatus$ = this.realtime.status$;
    this.realtimeEvents$ = this.realtime.events$;
    this.realtimeCount$ = this.realtime.events$.pipe(map((events) => events.length));
    this.user$ = this.authState.user$;
    this.permissions$ = this.authState.permissions$;
    this.roles$ = this.authState.roles$;
    this.isAdmin = this.permissionService.hasRole('admin');
  }

  ngOnInit(): void {
    this.realtime.connect();
  }

  ngOnDestroy(): void {
    this.realtime.disconnect();
  }

  async dispatchTestNotification(): Promise<void> {
    if (this.isDispatching) return;

    this.isDispatching = true;
    try {
      await firstValueFrom(
        this.apiClient.post<{ dispatched: boolean }, { type: string; title: string; message: string }>('/v1/realtime/notify', {
          type: 'info',
          title: 'Realtime smoke test',
          message: 'Angular received a live websocket event.',
        }),
      );
    } finally {
      this.isDispatching = false;
    }
  }
}
