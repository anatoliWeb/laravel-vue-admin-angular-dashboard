import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { REALTIME_CHANNELS, REALTIME_EVENTS } from './realtime.channels';
import type { RealtimeConnectionState, RealtimeStatusMetric, SystemNotificationPayload } from './realtime.types';

/**
 * Websocket-ready realtime client placeholder.
 *
 * WHY PREPARE NOW:
 * The app shell already surfaces live system status. This client gives us a
 * stable integration seam where Laravel Reverb / Echo can be attached later,
 * while current UI keeps using deterministic mock metrics.
 */
type RealtimeListener = (payload: SystemNotificationPayload) => void;
type StatusListener = (state: RealtimeConnectionState) => void;

type ReverbEnv = {
  appKey: string;
  host: string;
  port: number;
  scheme: 'http' | 'https';
  forceTLS: boolean;
};

export class RealtimeClient {
  private echo: Echo<'reverb'> | null = null;
  private state: RealtimeConnectionState = {
    connected: false,
    transport: 'none',
    status: 'disconnected',
    eventsReceived: 0,
  };
  private readonly listeners = new Set<RealtimeListener>();
  private readonly statusListeners = new Set<StatusListener>();

  connect(): RealtimeConnectionState {
    if (this.echo) {
      return this.getState();
    }

    const env = this.resolveEnv();

    if (!env.appKey) {
      this.updateState({
        connected: false,
        transport: 'none',
        status: 'error',
        lastError: 'Missing VITE_REVERB_APP_KEY',
        lastSyncAt: new Date().toISOString(),
      });

      return this.getState();
    }

    this.state = {
      connected: false,
      transport: 'websocket',
      status: 'connecting',
      eventsReceived: this.state.eventsReceived ?? 0,
      lastSyncAt: new Date().toISOString(),
    };
    this.notifyStatus();

    (window as Window & { Pusher?: typeof Pusher }).Pusher = Pusher;

    this.echo = new Echo({
      broadcaster: 'reverb',
      key: env.appKey,
      wsHost: env.host,
      wsPort: env.port,
      wssPort: env.port,
      forceTLS: env.forceTLS,
      enabledTransports: ['ws', 'wss'],
    });

    const connection = this.echo.connector.pusher.connection;
    connection.bind('connected', () => {
      this.updateState({
        connected: true,
        transport: 'websocket',
        status: 'connected',
        connectedAt: new Date().toISOString(),
        lastSyncAt: new Date().toISOString(),
        lastError: undefined,
      });
    });
    connection.bind('disconnected', () => {
      this.updateState({
        connected: false,
        status: 'disconnected',
        lastSyncAt: new Date().toISOString(),
      });
    });
    connection.bind('error', (error: unknown) => {
      this.updateState({
        connected: false,
        status: 'error',
        lastError: this.normalizeError(error),
        lastSyncAt: new Date().toISOString(),
      });
    });

    this.echo
      .channel(REALTIME_CHANNELS.systemNotifications)
      .listen(REALTIME_EVENTS.systemNotification, (payload: SystemNotificationPayload) => {
        this.updateState({
          lastEventAt: new Date().toISOString(),
          eventsReceived: (this.state.eventsReceived ?? 0) + 1,
          lastSyncAt: new Date().toISOString(),
        });

        this.listeners.forEach((listener) => listener(payload));
      });

    return this.getState();
  }

  disconnect(): void {
    if (!this.echo) {
      return;
    }

    this.echo.leave(REALTIME_CHANNELS.systemNotifications);
    this.echo.disconnect();
    this.echo = null;

    this.updateState({
      connected: false,
      transport: 'none',
      status: 'disconnected',
      lastSyncAt: new Date().toISOString(),
    });
  }

  getState(): RealtimeConnectionState {
    return { ...this.state };
  }

  onSystemNotification(listener: RealtimeListener): () => void {
    this.listeners.add(listener);

    return () => {
      this.listeners.delete(listener);
    };
  }

  onStatusChange(listener: StatusListener): () => void {
    this.statusListeners.add(listener);
    listener(this.getState());

    return () => {
      this.statusListeners.delete(listener);
    };
  }

  getMetrics(): RealtimeStatusMetric[] {
    return [
      {
        key: 'backend_online',
        label: 'WS',
        count: this.state.connected ? 1 : 0,
        active: this.state.connected,
      },
      {
        key: 'frontend_online',
        label: 'EV',
        count: this.state.eventsReceived ?? 0,
        active: (this.state.eventsReceived ?? 0) > 0,
      },
    ];
  }

  // Backward-compatible wrapper used by current layout.
  getMockMetrics(): RealtimeStatusMetric[] {
    return this.getMetrics();
  }

  private resolveEnv(): ReverbEnv {
    const appKey = String(import.meta.env.VITE_REVERB_APP_KEY ?? '');
    const host = String(import.meta.env.VITE_REVERB_HOST ?? window.location.hostname ?? 'localhost');
    const port = Number.parseInt(String(import.meta.env.VITE_REVERB_PORT ?? '6001'), 10);
    const scheme = String(import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https' ? 'https' : 'http';
    const forceTLS = String(import.meta.env.VITE_REVERB_FORCE_TLS ?? '') === 'true' || scheme === 'https';

    return {
      appKey,
      host,
      port: Number.isNaN(port) ? 6001 : port,
      scheme,
      forceTLS,
    };
  }

  private updateState(update: Partial<RealtimeConnectionState>): void {
    this.state = {
      ...this.state,
      ...update,
    };

    this.notifyStatus();
  }

  private notifyStatus(): void {
    const snapshot = this.getState();
    this.statusListeners.forEach((listener) => listener(snapshot));
  }

  private normalizeError(error: unknown): string {
    if (error instanceof Error) {
      return error.message;
    }

    if (typeof error === 'string') {
      return error;
    }

    return 'Realtime connection error';
  }
}

export const realtimeClient = new RealtimeClient();
