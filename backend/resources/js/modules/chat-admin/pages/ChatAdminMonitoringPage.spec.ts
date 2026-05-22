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
  blockParticipantMock: vi.fn(),
  unblockParticipantMock: vi.fn(),
  updateParticipantAccessMock: vi.fn(),
  updateParticipantCapabilitiesMock: vi.fn(),
}));

vi.mock('../services/chat-admin.service', () => ({
  chatAdminService: {
    listConversations: mocked.listConversationsMock,
    getConversation: mocked.getConversationMock,
    listMessages: mocked.listMessagesMock,
    listParticipants: mocked.listParticipantsMock,
    sendMessage: mocked.sendMessageMock,
    blockParticipant: mocked.blockParticipantMock,
    unblockParticipant: mocked.unblockParticipantMock,
    updateParticipantAccess: mocked.updateParticipantAccessMock,
    updateParticipantCapabilities: mocked.updateParticipantCapabilitiesMock,
  },
}));

describe('ChatAdminMonitoringPage', () => {
  it('passes new filter params to service and reset clears filters', async () => {
    mocked.listConversationsMock.mockResolvedValue({ items: [], meta: {} });
    mocked.getConversationMock.mockResolvedValue(null);
    mocked.listMessagesMock.mockResolvedValue([]);
    mocked.listParticipantsMock.mockResolvedValue([]);
    mocked.sendMessageMock.mockResolvedValue(null);
    mocked.blockParticipantMock.mockResolvedValue(null);
    mocked.unblockParticipantMock.mockResolvedValue(null);
    mocked.updateParticipantAccessMock.mockResolvedValue(null);
    mocked.updateParticipantCapabilitiesMock.mockResolvedValue(null);

    const wrapper = mount(ChatAdminMonitoringPage, {
      global: {
        stubs: {
          AdminChatConversationList: true,
          AdminChatConversationDetails: true,
          AdminChatMessageList: true,
          BaseLoader: { template: '<div>loader</div>', props: ['label'] },
          BaseErrorState: { template: '<div>{{ title }} {{ description }}</div>', props: ['title', 'description'] },
          BaseEmptyState: { template: '<div>{{ title }} {{ description }}</div>', props: ['title', 'description'] },
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
    mocked.blockParticipantMock.mockResolvedValue(null);
    mocked.unblockParticipantMock.mockResolvedValue(null);
    mocked.updateParticipantAccessMock.mockResolvedValue(null);
    mocked.updateParticipantCapabilitiesMock.mockResolvedValue(null);

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

  it('participant actions call service and reload participants', async () => {
    mocked.listConversationsMock.mockResolvedValue({ items: [{ id: 8, title: 'Ops' }], meta: {} });
    mocked.getConversationMock.mockResolvedValue({ id: 8, title: 'Ops', status: 'active' });
    mocked.listMessagesMock.mockResolvedValue([]);
    mocked.listParticipantsMock.mockResolvedValue([{ user_id: 55, name: 'User' }]);
    mocked.blockParticipantMock.mockResolvedValue({});
    mocked.unblockParticipantMock.mockResolvedValue({});
    mocked.updateParticipantAccessMock.mockResolvedValue({});
    mocked.updateParticipantCapabilitiesMock.mockResolvedValue({});

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
    await wrapper.findComponent({ name: 'AdminChatConversationList' }).vm.$emit('select', 8);
    await Promise.resolve();

    await wrapper.findComponent({ name: 'AdminChatParticipantsList' }).vm.$emit('block', 55, 'show_notice');
    await Promise.resolve();
    expect(mocked.blockParticipantMock).toHaveBeenCalledWith(8, 55, { block_display_mode: 'show_notice' });

    await wrapper.findComponent({ name: 'AdminChatParticipantsList' }).vm.$emit('unblock', 55);
    await Promise.resolve();
    expect(mocked.unblockParticipantMock).toHaveBeenCalledWith(8, 55);

    await wrapper.findComponent({ name: 'AdminChatParticipantsList' }).vm.$emit('set-read-only', 55);
    await Promise.resolve();
    expect(mocked.updateParticipantAccessMock).toHaveBeenCalledWith(8, 55, { access_state: 'read_only' });

    await wrapper.findComponent({ name: 'AdminChatParticipantsList' }).vm.$emit('restore-full', 55);
    await Promise.resolve();
    expect(mocked.updateParticipantAccessMock).toHaveBeenCalledWith(8, 55, { access_state: 'full' });

    await wrapper.findComponent({ name: 'AdminChatParticipantsList' }).vm.$emit('hide-chat', 55);
    await Promise.resolve();
    expect(mocked.updateParticipantAccessMock).toHaveBeenCalledWith(8, 55, { access_state: 'hidden' });

    await wrapper.findComponent({ name: 'AdminChatParticipantsList' }).vm.$emit('show-read-only-history', 55);
    await Promise.resolve();
    expect(mocked.updateParticipantAccessMock).toHaveBeenCalledWith(8, 55, {
      access_state: 'blocked',
      block_display_mode: 'show_read_only_history',
    });

    expect(mocked.listParticipantsMock).toHaveBeenCalled();
  });

  it('participant action error does not break page flow', async () => {
    mocked.listConversationsMock.mockResolvedValue({ items: [{ id: 9, title: 'Ops' }], meta: {} });
    mocked.getConversationMock.mockResolvedValue({ id: 9, title: 'Ops', status: 'active' });
    mocked.listMessagesMock.mockResolvedValue([]);
    mocked.listParticipantsMock.mockResolvedValue([{ user_id: 77, name: 'User' }]);
    mocked.blockParticipantMock.mockRejectedValue(new Error('Failed to block participant.'));

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
    await wrapper.findComponent({ name: 'AdminChatConversationList' }).vm.$emit('select', 9);
    await Promise.resolve();

    await wrapper.findComponent({ name: 'AdminChatParticipantsList' }).vm.$emit('block', 77, 'show_notice');
    await Promise.resolve();

    expect(mocked.blockParticipantMock).toHaveBeenCalledWith(9, 77, { block_display_mode: 'show_notice' });
    const text = wrapper.text();
    expect(text).not.toContain('blocked_reason');
    expect(text).not.toContain('token');
    expect(text).not.toContain('secret');
    expect(text).not.toContain('user_agent');
    expect(text).not.toContain('ip_address');
  });
});
