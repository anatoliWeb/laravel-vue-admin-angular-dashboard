import type { RealtimeConnectionState, RealtimeStatusMetric } from './realtime.types';

/**
 * Websocket-ready realtime client placeholder.
 *
 * WHY PREPARE NOW:
 * The app shell already surfaces live system status. This client gives us a
 * stable integration seam where Laravel Reverb / Echo can be attached later,
 * while current UI keeps using deterministic mock metrics.
 */
export class RealtimeClient {
  private state: RealtimeConnectionState = {
    connected: false,
    transport: 'none',
  };

  connect(): RealtimeConnectionState {
    this.state = {
      connected: false,
      transport: 'none',
      lastSyncAt: new Date().toISOString(),
    };

    return this.state;
  }

  getState(): RealtimeConnectionState {
    return this.state;
  }

  getMockMetrics(): RealtimeStatusMetric[] {
    return [
      { key: 'backend_online', label: 'BE', count: 12, active: true },
      { key: 'frontend_online', label: 'FE', count: 34, active: true },
    ];
  }
}

export const realtimeClient = new RealtimeClient();
