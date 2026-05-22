import { describe, expect, it, vi } from 'vitest';
import { chatAdminService } from './chat-admin.service';

const apiMock = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn(),
  patch: vi.fn(),
  put: vi.fn(),
  delete: vi.fn(),
}));

vi.mock('../../../services/api/client', () => ({
  api: apiMock,
}));

describe('chatAdminService participant actions', () => {
  it('block action calls API with block_display_mode payload', async () => {
    apiMock.patch.mockResolvedValue({ data: { user_id: 11 } });
    await chatAdminService.blockParticipant(7, 11, { block_display_mode: 'show_notice' });
    expect(apiMock.patch).toHaveBeenCalledWith(
      '/v1/chat/conversations/7/participants/11/block',
      { block_display_mode: 'show_notice' },
    );
  });

  it('unblock action calls API', async () => {
    apiMock.patch.mockResolvedValue({ data: { user_id: 11 } });
    await chatAdminService.unblockParticipant(7, 11);
    expect(apiMock.patch).toHaveBeenCalledWith('/v1/chat/conversations/7/participants/11/unblock', {});
  });

  it('set read-only/full/hide/history call update access API payloads', async () => {
    apiMock.patch.mockResolvedValue({ data: { user_id: 11 } });

    await chatAdminService.updateParticipantAccess(7, 11, { access_state: 'read_only' });
    expect(apiMock.patch).toHaveBeenCalledWith(
      '/v1/chat/conversations/7/participants/11/access',
      { access_state: 'read_only' },
    );

    await chatAdminService.updateParticipantAccess(7, 11, { access_state: 'full' });
    expect(apiMock.patch).toHaveBeenCalledWith(
      '/v1/chat/conversations/7/participants/11/access',
      { access_state: 'full' },
    );

    await chatAdminService.updateParticipantAccess(7, 11, { access_state: 'hidden' });
    expect(apiMock.patch).toHaveBeenCalledWith(
      '/v1/chat/conversations/7/participants/11/access',
      { access_state: 'hidden' },
    );

    await chatAdminService.updateParticipantAccess(7, 11, {
      access_state: 'blocked',
      block_display_mode: 'show_read_only_history',
    });
    expect(apiMock.patch).toHaveBeenCalledWith(
      '/v1/chat/conversations/7/participants/11/access',
      { access_state: 'blocked', block_display_mode: 'show_read_only_history' },
    );
  });
});

