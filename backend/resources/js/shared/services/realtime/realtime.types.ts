export interface RealtimeStatusMetric {
  key: 'backend_online' | 'frontend_online';
  label: string;
  count: number;
  active: boolean;
}

export interface RealtimeConnectionState {
  connected: boolean;
  transport: 'websocket' | 'polling' | 'none';
  lastSyncAt?: string;
}
