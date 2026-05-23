import { beforeEach, describe, expect, it, vi } from 'vitest';

const bindMock = vi.fn();
const privateListenMock = vi.fn().mockReturnThis();
const publicListenMock = vi.fn().mockReturnThis();
const activityListenMock = vi.fn().mockReturnThis();
const joinMock = vi.fn().mockReturnValue({
  here: vi.fn().mockReturnThis(),
  joining: vi.fn().mockReturnThis(),
  leaving: vi.fn().mockReturnThis(),
  error: vi.fn().mockReturnThis(),
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

  beforeEach(() => {
    window.localStorage.clear();
    ctorArgs.length = 0;
    bindMock.mockClear();
    privateListenMock.mockClear();
    publicListenMock.mockClear();
    activityListenMock.mockClear();
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
});

