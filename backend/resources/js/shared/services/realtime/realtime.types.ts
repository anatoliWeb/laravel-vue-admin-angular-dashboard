export interface RealtimeStatusMetric {
  key: 'backend_online' | 'frontend_online';
  label: string;
  count: number;
  active: boolean;
}

export interface RealtimeConnectionState {
  connected: boolean;
  transport: 'websocket' | 'polling' | 'none';
  status?: 'disconnected' | 'connecting' | 'connected' | 'error';
  lastSyncAt?: string;
  connectedAt?: string;
  lastEventAt?: string;
  eventsReceived?: number;
  lastError?: string;
}

export interface SystemNotificationPayload {
  type: string;
  title: string;
  message: string;
  created_at: string;
}
