import { of, throwError } from 'rxjs';
import { vi } from 'vitest';
import { ChatStateService } from './chat-state.service';
import { ChatApiService } from '../../../core/services/chat-api.service';
import { ChatDeviceService } from '../../../core/services/chat-device.service';

describe('ChatStateService', () => {
  let service: ChatStateService;
  let chatApi: {
    listConversations: ReturnType<typeof vi.fn>;
    getConversation: ReturnType<typeof vi.fn>;
    listMessages: ReturnType<typeof vi.fn>;
    sendMessage: ReturnType<typeof vi.fn>;
    markConversationRead: ReturnType<typeof vi.fn>;
    startTyping: ReturnType<typeof vi.fn>;
    stopTyping: ReturnType<typeof vi.fn>;
    registerDevice: ReturnType<typeof vi.fn>;
    searchMessages: ReturnType<typeof vi.fn>;
    editMessage: ReturnType<typeof vi.fn>;
    deleteMessage: ReturnType<typeof vi.fn>;
    createDirectConversation: ReturnType<typeof vi.fn>;
    createGroupConversation: ReturnType<typeof vi.fn>;
    createPrivateGroupFromDirect: ReturnType<typeof vi.fn>;
    markMessageRead: ReturnType<typeof vi.fn>;
    leavePresence: ReturnType<typeof vi.fn>;
    uploadAttachment: ReturnType<typeof vi.fn>;
    deleteAttachment: ReturnType<typeof vi.fn>;
    registerDeviceOnce: ReturnType<typeof vi.fn>;
  };
  let chatDevice: {
    ensureRegistered: ReturnType<typeof vi.fn>;
    getDeviceKey: ReturnType<typeof vi.fn>;
    buildRegisterPayload: ReturnType<typeof vi.fn>;
  };

  beforeEach(() => {
    chatApi = {
      listConversations: vi.fn(),
      getConversation: vi.fn(),
      listMessages: vi.fn(),
      sendMessage: vi.fn(),
      markConversationRead: vi.fn(),
      startTyping: vi.fn(),
      stopTyping: vi.fn(),
      registerDevice: vi.fn(),
      searchMessages: vi.fn(),
      editMessage: vi.fn(),
      deleteMessage: vi.fn(),
      createDirectConversation: vi.fn(),
      createGroupConversation: vi.fn(),
      createPrivateGroupFromDirect: vi.fn(),
      markMessageRead: vi.fn(),
      leavePresence: vi.fn(),
      uploadAttachment: vi.fn(),
      deleteAttachment: vi.fn(),
      registerDeviceOnce: vi.fn(),
    };

    chatDevice = {
      ensureRegistered: vi.fn(),
      getDeviceKey: vi.fn(),
      buildRegisterPayload: vi.fn(),
    };

    chatDevice.ensureRegistered.mockResolvedValue(undefined);
    chatDevice.getDeviceKey.mockReturnValue('chatdev_test');

    service = new ChatStateService(chatApi as unknown as ChatApiService, chatDevice as unknown as ChatDeviceService);
  });

  it('loads conversations', async () => {
    chatApi.listConversations.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [{ id: 1, title: 'A' }],
    }));

    await service.loadConversations();

    let conversationsCount = 0;
    service.conversations$.subscribe((items) => {
      conversationsCount = items.length;
    });
    expect(chatApi.listConversations).toHaveBeenCalled();
    expect(conversationsCount).toBe(1);
  });

  it('opens conversation and loads messages', async () => {
    chatApi.getConversation.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: { id: 7, title: 'Room' },
    }));
    chatApi.listMessages.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [{ id: 10, conversation_id: 7, body: 'Hi' }],
    }));

    await service.openConversation(7);

    let messageCount = 0;
    service.messages$.subscribe((items) => {
      messageCount = items.length;
    });

    expect(chatApi.getConversation).toHaveBeenCalledWith(7);
    expect(chatApi.listMessages).toHaveBeenCalledWith(7, { per_page: 50 });
    expect(messageCount).toBe(1);
  });

  it('handles API error safely', async () => {
    chatApi.listConversations.mockReturnValue(throwError(() => new Error('Boom')));

    await service.loadConversations();

    let errorMessage: string | null = null;
    service.error$.subscribe((value) => {
      errorMessage = value;
    });
    expect(errorMessage).toBe('Boom');
  });
});
