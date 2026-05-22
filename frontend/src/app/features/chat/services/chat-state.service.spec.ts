import { of, throwError } from 'rxjs';
import { vi } from 'vitest';
import { ChatStateService } from './chat-state.service';
import { ChatApiService } from '../../../core/services/chat-api.service';
import { ChatDeviceService } from '../../../core/services/chat-device.service';
import { ChatPresenceClientService } from './chat-presence-client.service';

describe('ChatStateService', () => {
  let service: ChatStateService;
  let chatApi: {
    listConversations: ReturnType<typeof vi.fn>;
    getConversation: ReturnType<typeof vi.fn>;
    listMessages: ReturnType<typeof vi.fn>;
    listConversationParticipants: ReturnType<typeof vi.fn>;
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
  let chatPresenceClient: {
    joinConversationPresence: ReturnType<typeof vi.fn>;
    leaveConversationPresence: ReturnType<typeof vi.fn>;
  };

  beforeEach(() => {
    chatApi = {
      listConversations: vi.fn(),
      getConversation: vi.fn(),
      listMessages: vi.fn(),
      listConversationParticipants: vi.fn(),
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
    chatPresenceClient = {
      joinConversationPresence: vi.fn(),
      leaveConversationPresence: vi.fn(),
    };

    chatDevice.ensureRegistered.mockResolvedValue(undefined);
    chatDevice.getDeviceKey.mockReturnValue('chatdev_test');
    chatApi.markConversationRead.mockReturnValue(of({ success: true, message: 'ok', data: {} }));
    chatApi.markMessageRead.mockReturnValue(of({ success: true, message: 'ok', data: {} }));

    service = new ChatStateService(
      chatApi as unknown as ChatApiService,
      chatDevice as unknown as ChatDeviceService,
      chatPresenceClient as unknown as ChatPresenceClientService,
    );
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
    chatApi.listConversationParticipants.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [{ user_id: 1, role: 'owner', status: 'active', access_state: 'full' }],
    }));

    await service.openConversation(7);

    let messageCount = 0;
    service.messages$.subscribe((items) => {
      messageCount = items.length;
    });

    expect(chatApi.getConversation).toHaveBeenCalledWith(7);
    expect(chatApi.listMessages).toHaveBeenCalledWith(7, { per_page: 50 });
    expect(chatApi.listConversationParticipants).toHaveBeenCalledWith(7);
    expect(chatPresenceClient.joinConversationPresence).toHaveBeenCalledWith(7, expect.any(Object));
    expect(chatDevice.ensureRegistered).toHaveBeenCalled();
    expect(chatApi.markConversationRead).toHaveBeenCalledWith(7, { device_key: 'chatdev_test' });
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

  it('sendMessage appends response once and avoids duplicates', async () => {
    chatApi.getConversation.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: { id: 7, title: 'Room' },
    }));
    chatApi.listMessages.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [],
    }));
    await service.openConversation(7);

    chatApi.sendMessage.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: { id: 99, conversation_id: 7, body: 'Hello' },
    }));

    await service.sendMessage('Hello');
    await service.sendMessage('Hello again');

    let messagesCount = 0;
    service.messages$.subscribe((items) => {
      messagesCount = items.length;
    });
    expect(messagesCount).toBe(1);
  });

  it('markMessageRead sends device_key', async () => {
    chatApi.getConversation.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: { id: 7, title: 'Room' },
    }));
    chatApi.listMessages.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [],
    }));
    chatApi.listConversationParticipants.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [],
    }));
    chatApi.markConversationRead.mockReturnValue(of({ success: true, message: 'ok', data: {} }));
    chatApi.markMessageRead.mockReturnValue(of({ success: true, message: 'ok', data: {} }));

    await service.openConversation(7);
    await service.markMessageRead(55);

    expect(chatApi.markMessageRead).toHaveBeenCalledWith(55, { device_key: 'chatdev_test' });
  });

  it('no read request without active conversation', async () => {
    chatApi.markConversationRead.mockReturnValue(of({ success: true, message: 'ok', data: {} }));
    await service.markActiveConversationRead();
    expect(chatApi.markConversationRead).not.toHaveBeenCalled();
  });

  it('openConversation does not mark read for hidden conversation', async () => {
    chatApi.getConversation.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: {
        id: 7,
        title: 'Hidden',
        current_user_access: { user_id: 1, access_state: 'hidden' },
      },
    }));
    chatApi.listMessages.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [],
    }));
    chatApi.listConversationParticipants.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [],
    }));

    await service.openConversation(7);

    expect(chatApi.markConversationRead).not.toHaveBeenCalled();
  });

  it('send with file calls sendMessage then uploadAttachment', async () => {
    const file = new File(['file-content'], 'demo.txt', { type: 'text/plain' });

    chatApi.getConversation.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: { id: 7, title: 'Room' },
    }));
    chatApi.listMessages.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [],
    }));
    chatApi.listConversationParticipants.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [],
    }));
    chatApi.sendMessage.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: { id: 501, conversation_id: 7, body: 'With file' },
    }));
    chatApi.uploadAttachment.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: { id: 9001, message_id: 501 },
    }));

    await service.openConversation(7);
    await service.sendMessageWithAttachment('With file', file);

    expect(chatApi.sendMessage).toHaveBeenCalledWith(7, { body: 'With file', type: 'text' });
    expect(chatApi.uploadAttachment).toHaveBeenCalledWith(501, file);
  });

  it('filters conversations by search/type/visibility/unread and reset works', async () => {
    chatApi.listConversations.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [
        { id: 1, title: 'Direct A', type: 'direct', visibility: 'private', unread_count: 0 },
        { id: 2, title: 'Group Ops', type: 'group', visibility: 'public', unread_count: 3 },
      ],
    }));

    await service.loadConversations();

    service.setConversationSearch('group');
    service.setConversationTypeFilter('group');
    service.setConversationVisibilityFilter('public');
    service.setUnreadOnly(true);

    let filtered: any[] = [];
    service.filteredConversations$.subscribe((items) => {
      filtered = items;
    });
    expect(filtered.length).toBe(1);
    expect(filtered[0].id).toBe(2);

    service.resetConversationFilters();
    expect((service as any).conversationSearchSubject.value).toBe('');
    expect((service as any).conversationTypeFilterSubject.value).toBe('all');
    expect((service as any).conversationVisibilityFilterSubject.value).toBe('all');
    expect((service as any).unreadOnlySubject.value).toBe(false);
  });

  it('filters do not call backend per keystroke', async () => {
    chatApi.listConversations.mockReturnValue(of({
      success: true,
      message: 'ok',
      data: [{ id: 1, title: 'General' }],
    }));
    await service.loadConversations();
    expect(chatApi.listConversations).toHaveBeenCalledTimes(1);

    service.setConversationSearch('g');
    service.setConversationSearch('ge');
    service.setConversationSearch('gen');
    service.setConversationTypeFilter('group');
    service.setConversationVisibilityFilter('private');
    service.setUnreadOnly(true);

    expect(chatApi.listConversations).toHaveBeenCalledTimes(1);
  });

  it('switching conversation leaves previous presence and joins new one', async () => {
    chatApi.getConversation.mockReturnValue(of({ success: true, message: 'ok', data: { id: 7, title: 'A' } }));
    chatApi.listMessages.mockReturnValue(of({ success: true, message: 'ok', data: [] }));
    chatApi.listConversationParticipants.mockReturnValue(of({ success: true, message: 'ok', data: [] }));
    chatApi.leavePresence.mockReturnValue(of({ success: true, message: 'ok', data: {} }));

    await service.openConversation(7);

    chatApi.getConversation.mockReturnValue(of({ success: true, message: 'ok', data: { id: 8, title: 'B' } }));
    await service.openConversation(8);

    expect(chatPresenceClient.leaveConversationPresence).toHaveBeenCalled();
    expect(chatApi.leavePresence).toHaveBeenCalledWith(7, { device_key: 'chatdev_test' });
    expect(chatPresenceClient.joinConversationPresence).toHaveBeenCalledWith(8, expect.any(Object));
  });

  it('teardownPresence leaves active conversation with device_key', async () => {
    chatApi.getConversation.mockReturnValue(of({ success: true, message: 'ok', data: { id: 7, title: 'A' } }));
    chatApi.listMessages.mockReturnValue(of({ success: true, message: 'ok', data: [] }));
    chatApi.listConversationParticipants.mockReturnValue(of({ success: true, message: 'ok', data: [] }));
    chatApi.leavePresence.mockReturnValue(of({ success: true, message: 'ok', data: {} }));

    await service.openConversation(7);
    await service.teardownPresence();

    expect(chatPresenceClient.leaveConversationPresence).toHaveBeenCalled();
    expect(chatApi.leavePresence).toHaveBeenCalledWith(7, { device_key: 'chatdev_test' });
  });

  it('presence state handlers set/add/remove users', () => {
    service.setPresenceUsers([{ id: 1, name: 'A' } as any]);
    service.addPresenceUser({ id: 2, name: 'B' } as any);
    service.removePresenceUser(1);

    let users: any[] = [];
    service.presenceUsers$.subscribe((items) => {
      users = items;
    });
    expect(users.length).toBe(1);
    expect(users[0].id).toBe(2);
  });

  it('presence leave errors do not break UI', async () => {
    chatApi.getConversation.mockReturnValue(of({ success: true, message: 'ok', data: { id: 7, title: 'A' } }));
    chatApi.listMessages.mockReturnValue(of({ success: true, message: 'ok', data: [] }));
    chatApi.listConversationParticipants.mockReturnValue(of({ success: true, message: 'ok', data: [] }));
    chatApi.leavePresence.mockReturnValue(throwError(() => new Error('leave failed')));

    await service.openConversation(7);
    await expect(service.teardownPresence()).resolves.toBeUndefined();
  });

  it('clearParticipants clears state', () => {
    (service as any).participantsSubject.next([{ user_id: 7 }]);
    service.clearParticipants();

    let count = -1;
    service.participants$.subscribe((items) => {
      count = items.length;
    });
    expect(count).toBe(0);
  });
});
