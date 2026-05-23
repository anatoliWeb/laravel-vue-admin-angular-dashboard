import { beforeEach, describe, expect, it, vi } from 'vitest';

const bindMock = vi.fn();
const privateListenMock = vi.fn().mockReturnThis();
const publicListenMock = vi.fn().mockReturnThis();
const joinHereMock = vi.fn();
const joinJoiningMock = vi.fn();
const joinLeavingMock = vi.fn();
const joinErrorMock = vi.fn();
const joinMock = vi.fn().mockReturnValue({
  here: joinHereMock,
  joining: joinJoiningMock,
  leaving: joinLeavingMock,
  error: joinErrorMock,
});
const leaveMock = vi.fn();
const disconnectMock = vi.fn();

const ctorArgs: unknown[] = [];

vi.mock('laravel-echo', () => {
  return {
    default: class EchoMock {
      public connector = {
        pusher: {
          connection: {
            bind: bindMock,
          },
        },
      };

      constructor(options: unknown) {
        ctorArgs.push(options);
      }

      private() {
        return {
          listen: privateListenMock,
        };
      }

      channel() {
        return {
          listen: publicListenMock,
        };
      }

      join() {
        return joinMock();
      }

      leave() {
        leaveMock();
      }

      disconnect() {
        disconnectMock();
      }
    },
  };
});

vi.mock('pusher-js', () => ({
  default: class PusherMock {},
}));

describe('RealtimeClient auth headers', async () => {
  const { RealtimeClient } = await import('./realtime.client');
  const { REALTIME_CHANNELS } = await import('./realtime.channels');

  beforeEach(() => {
    window.localStorage.clear();
    ctorArgs.length = 0;
    bindMock.mockClear();
    privateListenMock.mockClear();
    publicListenMock.mockClear();
    joinMock.mockClear();
    joinHereMock.mockReset();
    joinJoiningMock.mockReset();
    joinLeavingMock.mockReset();
    joinErrorMock.mockReset();
    leaveMock.mockClear();
    disconnectMock.mockClear();
    vi.stubEnv('VITE_REVERB_APP_KEY', 'app-key');
    vi.stubEnv('VITE_REVERB_HOST', 'localhost');
    vi.stubEnv('VITE_REVERB_PORT', '6001');
    vi.stubEnv('VITE_REVERB_SCHEME', 'http');
  });

  it('adds bearer token and accept header for broadcasting auth', () => {
    window.localStorage.setItem('admin_access_token', 'token-123');
    const client = new RealtimeClient();
    client.connect();

    const options = ctorArgs[0] as { auth?: { headers?: Record<string, string> } };
    expect(options.auth?.headers?.Authorization).toBe('Bearer token-123');
    expect(options.auth?.headers?.Accept).toBe('application/json');
  });

  it('updates presence metrics and events counter on presence callbacks', () => {
    const client = new RealtimeClient();
    client.connect();

    const unsubscribe = client.joinPresence(REALTIME_CHANNELS.presenceOnline, {});

    const hereHandler = joinHereMock.mock.calls[0]?.[0] as ((users: Array<{ id: number; name: string }>) => void);
    const joiningHandler = joinJoiningMock.mock.calls[0]?.[0] as ((user: { id: number; name: string }) => void);
    const leavingHandler = joinLeavingMock.mock.calls[0]?.[0] as ((user: { id: number; name: string }) => void);

    hereHandler([{ id: 10, name: 'Admin' }]);
    let metrics = client.getMetrics();
    expect(metrics.find((item) => item.key === 'presence_online')?.count).toBe(1);
    expect(metrics.find((item) => item.key === 'presence_dashboard')?.count).toBe(1);

    joiningHandler({ id: 11, name: 'Member' });
    metrics = client.getMetrics();
    expect(metrics.find((item) => item.key === 'presence_online')?.count).toBe(2);

    leavingHandler({ id: 10, name: 'Admin' });
    metrics = client.getMetrics();
    expect(metrics.find((item) => item.key === 'presence_online')?.count).toBe(1);
    expect(client.getState().eventsReceived).toBeGreaterThan(0);

    unsubscribe();
    metrics = client.getMetrics();
    expect(metrics.find((item) => item.key === 'presence_dashboard')?.count).toBe(0);
  });
});
