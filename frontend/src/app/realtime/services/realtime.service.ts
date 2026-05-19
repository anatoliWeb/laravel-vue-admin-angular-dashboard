import { Inject, Injectable, OnDestroy } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { APP_CONFIG, AppEnvironment } from '../../core/tokens/app-config.token';
import { AuthTokenStorageService } from '../../auth/services/auth-token-storage.service';

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

export interface ActivityStreamPayload {
  id: number;
  action: string;
  description: string | null;
  user: {
    id: number;
    name: string;
  } | null;
  created_at: string | null;
  meta?: {
    source?: string;
    module?: string;
  };
}

export interface RealtimePresenceUser {
  id: number;
  name: string;
}

@Injectable({ providedIn: 'root' })
export class RealtimeService implements OnDestroy {
  private static readonly CHANNEL = 'system.notifications';
  private static readonly EVENT = '.system.notification';
  private static readonly ACTIVITY_CHANNEL = 'activity.stream';
  private static readonly ACTIVITY_EVENT = '.activity.logged';
  private static readonly PRESENCE_ONLINE_CHANNEL = 'presence-online';
  private static readonly PRESENCE_DASHBOARD_CHANNEL = 'presence-dashboard';

  private readonly statusSubject = new BehaviorSubject<RealtimeStatus>({
    connected: false,
    provider: 'reverb',
  });
  private readonly eventsSubject = new BehaviorSubject<SystemNotificationPayload[]>([]);
  private readonly activityEventsSubject = new BehaviorSubject<ActivityStreamPayload[]>([]);
  private readonly onlineUsersSubject = new BehaviorSubject<RealtimePresenceUser[]>([]);
  private readonly dashboardPresenceSubject = new BehaviorSubject<RealtimePresenceUser[]>([]);
  private readonly joinedPresenceChannels = new Set<string>();
  private echo: Echo<'reverb'> | null = null;
  private isConnected = false;
  private isDisconnecting = false;

  readonly status$ = this.statusSubject.asObservable();
  readonly events$ = this.eventsSubject.asObservable();
  readonly activityEvents$ = this.activityEventsSubject.asObservable();
  readonly onlineUsers$ = this.onlineUsersSubject.asObservable();
  readonly dashboardPresence$ = this.dashboardPresenceSubject.asObservable();

  constructor(
    @Inject(APP_CONFIG) private readonly config: AppEnvironment,
    private readonly tokenStorage: AuthTokenStorageService,
  ) {}

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
      authEndpoint: '/broadcasting/auth',
      withCredentials: true,
      auth: {
        headers: this.resolveAuthHeaders(),
      },
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

    const notificationChannel = this.config.realtime.usePrivateChannel
      ? this.echo.private(RealtimeService.CHANNEL)
      : this.echo.channel(RealtimeService.CHANNEL);

    notificationChannel
      .subscribed(() => {
        console.info('[Realtime] subscribed to', RealtimeService.CHANNEL);
      })
      .listen(RealtimeService.EVENT, (payload: SystemNotificationPayload) => {
        console.info('[Realtime] event received', payload);
        const nextEvents = [payload, ...this.eventsSubject.value].slice(0, 20);
        this.eventsSubject.next(nextEvents);
      });

    this.echo
      .private(RealtimeService.ACTIVITY_CHANNEL)
      .subscribed(() => {
        console.info('[Realtime] subscribed to', RealtimeService.ACTIVITY_CHANNEL);
      })
      .listen(RealtimeService.ACTIVITY_EVENT, (payload: ActivityStreamPayload) => {
        const nextActivityEvents = [payload, ...this.activityEventsSubject.value].slice(0, 20);
        this.activityEventsSubject.next(nextActivityEvents);
      });

    this.joinPresence(RealtimeService.PRESENCE_ONLINE_CHANNEL);
    this.joinPresence(RealtimeService.PRESENCE_DASHBOARD_CHANNEL);
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
      this.echo.leave(`private-${RealtimeService.CHANNEL}`);
      this.echo.leave(`private-${RealtimeService.ACTIVITY_CHANNEL}`);
      this.echo.leave(`presence-${RealtimeService.PRESENCE_ONLINE_CHANNEL}`);
      this.echo.leave(`presence-${RealtimeService.PRESENCE_DASHBOARD_CHANNEL}`);
      this.joinedPresenceChannels.clear();
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
    this.activityEventsSubject.next([]);
    this.onlineUsersSubject.next([]);
    this.dashboardPresenceSubject.next([]);
  }

  ngOnDestroy(): void {
    this.disconnect();
  }

  joinPresence(channelName: string): void {
    if (!this.echo || this.joinedPresenceChannels.has(channelName)) {
      return;
    }

    const target = this.resolvePresenceSubject(channelName);
    this.echo.join(channelName)
      .here((users: RealtimePresenceUser[]) => {
        target.next(users);
      })
      .joining((user: RealtimePresenceUser) => {
        if (target.value.some((item) => item.id === user.id)) {
          return;
        }

        target.next([...target.value, user]);
      })
      .leaving((user: RealtimePresenceUser) => {
        target.next(target.value.filter((item) => item.id !== user.id));
      });

    this.joinedPresenceChannels.add(channelName);
  }

  leavePresence(channelName: string): void {
    if (!this.echo) {
      return;
    }

    this.echo.leave(`presence-${channelName}`);
    this.resolvePresenceSubject(channelName).next([]);
    this.joinedPresenceChannels.delete(channelName);
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

  private resolveAuthHeaders(): Record<string, string> {
    const token = this.tokenStorage.getToken();
    if (!token) {
      return {};
    }

    return {
      Authorization: `Bearer ${token}`,
    };
  }

  private resolvePresenceSubject(channelName: string): BehaviorSubject<RealtimePresenceUser[]> {
    if (channelName === RealtimeService.PRESENCE_ONLINE_CHANNEL) {
      return this.onlineUsersSubject;
    }

    if (channelName === RealtimeService.PRESENCE_DASHBOARD_CHANNEL) {
      return this.dashboardPresenceSubject;
    }

    return this.dashboardPresenceSubject;
  }
}
