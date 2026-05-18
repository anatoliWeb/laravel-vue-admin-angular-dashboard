import { Inject, Injectable, OnDestroy } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { APP_CONFIG, AppEnvironment } from '../../core/tokens/app-config.token';

export interface RealtimeStatus {
  connected: boolean;
  provider: string;
}

export interface SystemNotificationPayload {
  type: string;
  title: string;
  message: string;
  created_at: string;
}

@Injectable({ providedIn: 'root' })
export class RealtimeService implements OnDestroy {
  private static readonly CHANNEL = 'system.notifications';
  private static readonly EVENT = '.system.notification';

  private readonly statusSubject = new BehaviorSubject<RealtimeStatus>({
    connected: false,
    provider: 'reverb',
  });
  private readonly eventsSubject = new BehaviorSubject<SystemNotificationPayload[]>([]);
  private echo: Echo<'reverb'> | null = null;
  private isConnected = false;
  private isDisconnecting = false;

  readonly status$ = this.statusSubject.asObservable();
  readonly events$ = this.eventsSubject.asObservable();

  constructor(@Inject(APP_CONFIG) private readonly config: AppEnvironment) {}

  connect(): void {
    if (!this.config.realtime.enabled || this.echo) {
      return;
    }

    if (!this.config.realtime.appKey) {
      console.warn('[Realtime] skipped: missing realtime.appKey');
      return;
    }

    // Reverb uses the Pusher protocol; Echo delegates reconnection to pusher-js.
    (window as Window & { Pusher?: typeof Pusher }).Pusher = Pusher;
    const wsHost = this.config.realtime.wsHost || window.location.hostname;
    this.echo = new Echo({
      broadcaster: 'reverb',
      key: this.config.realtime.appKey,
      wsHost,
      wsPort: this.config.realtime.wsPort,
      wssPort: this.config.realtime.wsPort,
      forceTLS: this.config.realtime.forceTLS,
      enabledTransports: ['ws', 'wss'],
    });

    const connector = this.echo.connector.pusher.connection;
    connector.bind('connected', () => {
      console.info('[Realtime] connected');
      this.updateConnectionState(true);
    });
    connector.bind('disconnected', () => {
      console.warn('[Realtime] disconnected');
      this.updateConnectionState(false);
    });
    connector.bind('unavailable', () => {
      console.warn('[Realtime] unavailable');
      this.updateConnectionState(false);
    });
    connector.bind('failed', () => {
      console.error('[Realtime] failed');
      this.updateConnectionState(false);
    });
    connector.bind('error', (error: unknown) => {
      console.error('[Realtime] error', error);
    });
    connector.bind('state_change', (states: { previous: string; current: string }) => {
      console.info('[Realtime] state', states.previous, '->', states.current);
    });

    this.echo
      .channel(RealtimeService.CHANNEL)
      .subscribed(() => {
        console.info('[Realtime] subscribed to', RealtimeService.CHANNEL);
      })
      .listen(RealtimeService.EVENT, (payload: SystemNotificationPayload) => {
        console.info('[Realtime] event received', payload);
        const nextEvents = [payload, ...this.eventsSubject.value].slice(0, 20);
        this.eventsSubject.next(nextEvents);
      });
  }

  reconnect(): void {
    this.disconnect();
    this.connect();
  }

  disconnect(): void {
    if (!this.echo || this.isDisconnecting) {
      return;
    }

    this.isDisconnecting = true;
    try {
      this.echo.leave(RealtimeService.CHANNEL);
      const connection = this.echo.connector.pusher.connection;
      if (connection.state !== 'disconnected' && connection.state !== 'disconnecting') {
        this.echo.disconnect();
      }
    } catch (error) {
      if (!this.config.production) {
        console.warn('[Realtime] safe disconnect warning', error);
      }
    } finally {
      this.echo = null;
      this.isDisconnecting = false;
      this.updateConnectionState(false);
    }
  }

  clearEvents(): void {
    this.eventsSubject.next([]);
  }

  ngOnDestroy(): void {
    this.disconnect();
  }

  private updateConnectionState(connected: boolean): void {
    if (this.isConnected === connected) {
      return;
    }

    this.isConnected = connected;
    this.statusSubject.next({
      connected,
      provider: this.statusSubject.value.provider,
    });
  }
}
