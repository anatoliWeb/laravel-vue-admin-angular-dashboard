import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import ChatAdminMonitoringPage from './ChatAdminMonitoringPage.vue';

const mocked = vi.hoisted(() => ({
  listConversationsMock: vi.fn(),
  getConversationMock: vi.fn(),
  listMessagesMock: vi.fn(),
  listParticipantsMock: vi.fn(),
  sendMessageMock: vi.fn(),
}));

vi.mock('../services/chat-admin.service', () => ({
  chatAdminService: {
    listConversations: mocked.listConversationsMock,
    getConversation: mocked.getConversationMock,
    listMessages: mocked.listMessagesMock,
    listParticipants: mocked.listParticipantsMock,
    sendMessage: mocked.sendMessageMock,
  },
}));

describe('ChatAdminMonitoringPage', () => {
  it('passes new filter params to service and reset clears filters', async () => {
    mocked.listConversationsMock.mockResolvedValue({ items: [], meta: {} });
    mocked.getConversationMock.mockResolvedValue(null);
    mocked.listMessagesMock.mockResolvedValue([]);
    mocked.listParticipantsMock.mockResolvedValue([]);
    mocked.sendMessageMock.mockResolvedValue(null);

    const wrapper = mount(ChatAdminMonitoringPage, {
      global: {
        stubs: {
          AdminChatConversationList: true,
          AdminChatConversationDetails: true,
          AdminChatMessageList: true,
          AdminChatParticipantsList: true,
        },
      },
    });

    await nextTick();
    await Promise.resolve();

    await wrapper.get('[data-testid="filter-unread-only"]').setValue(true);
    await wrapper.get('[data-testid="filter-failed-webhook"]').setValue(true);
    await wrapper.get('[data-testid="filter-imported-only"]').setValue(true);
    await wrapper.get('[data-testid="filter-assignment"]').setValue('assigned');
    await wrapper.get('[data-testid="filter-participant-restriction"]').setValue('restricted');

    await Promise.resolve();
    const lastCall = mocked.listConversationsMock.mock.calls.at(-1)?.[0] ?? {};
    expect(lastCall.unread).toBe('true');
    expect(lastCall.failed_webhook_delivery).toBe('true');
    expect(lastCall.imported).toBe('true');
    expect(lastCall.assignment).toBe('assigned');
    expect(lastCall.participant_restriction).toBe('restricted');

    await wrapper.get('[data-testid="filter-reset"]').trigger('click');
    await Promise.resolve();
    const resetCall = mocked.listConversationsMock.mock.calls.at(-1)?.[0] ?? {};
    expect(resetCall.unread).toBeUndefined();
    expect(resetCall.failed_webhook_delivery).toBeUndefined();
    expect(resetCall.imported).toBeUndefined();
    expect(resetCall.assignment).toBeUndefined();
    expect(resetCall.participant_restriction).toBeUndefined();
  });

  it('successful reply sends message and reloads messages', async () => {
    mocked.listConversationsMock.mockResolvedValue({ items: [{ id: 7, title: 'Room' }], meta: {} });
    mocked.getConversationMock.mockResolvedValue({ id: 7, title: 'Room', status: 'active' });
    mocked.listMessagesMock.mockResolvedValue([]);
    mocked.listParticipantsMock.mockResolvedValue([]);
    mocked.sendMessageMock.mockResolvedValue({ id: 33, conversation_id: 7, body: 'reply' });

    const wrapper = mount(ChatAdminMonitoringPage, {
      global: {
        stubs: {
          AdminChatConversationList: true,
          AdminChatConversationDetails: true,
          AdminChatMessageList: true,
          AdminChatParticipantsList: true,
        },
      },
    });

    await nextTick();
    await Promise.resolve();

    await wrapper.findComponent({ name: 'AdminChatConversationList' }).vm.$emit('select', 7);
    await Promise.resolve();

    await wrapper.findComponent({ name: 'AdminChatReplyComposer' }).vm.$emit('submit', { body: 'reply', type: 'text' });
    await Promise.resolve();

    expect(mocked.sendMessageMock).toHaveBeenCalledWith(7, { body: 'reply', type: 'text' });
    expect(mocked.listMessagesMock).toHaveBeenLastCalledWith(7, { per_page: 50 });
  });
});
