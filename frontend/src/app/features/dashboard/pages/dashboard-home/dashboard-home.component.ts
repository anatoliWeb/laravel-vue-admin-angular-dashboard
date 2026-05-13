import { Component, OnDestroy, OnInit } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { map } from 'rxjs';
import { ApiClientService } from '../../../../api/services/api-client.service';
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

  constructor(
    private readonly realtime: RealtimeService,
    private readonly apiClient: ApiClientService,
  ) {
    this.realtimeStatus$ = this.realtime.status$;
    this.realtimeEvents$ = this.realtime.events$;
    this.realtimeCount$ = this.realtime.events$.pipe(map((events) => events.length));
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
