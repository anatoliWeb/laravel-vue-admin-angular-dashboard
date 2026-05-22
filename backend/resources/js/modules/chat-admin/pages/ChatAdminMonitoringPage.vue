<template>
  <section class="chat-admin-page">
    <header class="chat-admin-page__header c-card">
      <div>
        <h2 class="chat-admin-page__title">Chat Monitoring</h2>
        <p class="chat-admin-page__subtitle">Read-only admin visibility over conversations, messages, and participants.</p>
      </div>
    </header>

    <AdminChatFilters
      :search="filters.search"
      :type="filters.type"
      :status="filters.status"
      :visibility="filters.visibility"
      :source="filters.source"
      :unread-only="filters.unreadOnly"
      :assignment="filters.assignment"
      :participant-restriction="filters.participantRestriction"
      :failed-webhook-delivery-only="filters.failedWebhookDeliveryOnly"
      :imported-only="filters.importedOnly"
      @update:search="onSearchChange"
      @update:type="onFilterTypeChange"
      @update:status="onFilterStatusChange"
      @update:visibility="onFilterVisibilityChange"
      @update:source="onFilterSourceChange"
      @update:unread-only="onUnreadOnlyChange"
      @update:assignment="onAssignmentChange"
      @update:participant-restriction="onParticipantRestrictionChange"
      @update:failed-webhook-delivery-only="onFailedWebhookDeliveryOnlyChange"
      @update:imported-only="onImportedOnlyChange"
      @reset="onResetFilters"
    />

    <section class="chat-admin-page__layout">
      <AdminChatConversationList
        :items="conversations"
        :selected-conversation-id="selectedConversationId"
        :loading="isConversationsLoading"
        :error="conversationsError"
        @select="onSelectConversation"
      />

      <section class="chat-admin-page__details">
        <AdminChatConversationDetails :conversation="selectedConversation" />
        <AdminChatMessageList :items="messages" :loading="isMessagesLoading" :error="messagesError" />
        <AdminChatParticipantsList :items="participants" :loading="isParticipantsLoading" :error="participantsError" />
      </section>
    </section>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import AdminChatConversationDetails from '../components/AdminChatConversationDetails.vue';
import AdminChatConversationList from '../components/AdminChatConversationList.vue';
import AdminChatFilters from '../components/AdminChatFilters.vue';
import AdminChatMessageList from '../components/AdminChatMessageList.vue';
import AdminChatParticipantsList from '../components/AdminChatParticipantsList.vue';
import { chatAdminService } from '../services/chat-admin.service';
import type { ChatAdminConversation, ChatAdminConversationFilters, ChatAdminMessage, ChatAdminParticipant } from '../types/chat-admin.types';

const conversations = ref<ChatAdminConversation[]>([]);
const selectedConversationId = ref<number | null>(null);
const selectedConversation = ref<ChatAdminConversation | null>(null);
const messages = ref<ChatAdminMessage[]>([]);
const participants = ref<ChatAdminParticipant[]>([]);

const isConversationsLoading = ref(false);
const isMessagesLoading = ref(false);
const isParticipantsLoading = ref(false);

const conversationsError = ref('');
const messagesError = ref('');
const participantsError = ref('');

const filters = ref<ChatAdminConversationFilters>({
  search: '',
  type: 'all',
  status: 'all',
  visibility: 'all',
  source: 'all',
  unreadOnly: false,
  assignment: 'all',
  participantRestriction: 'all',
  failedWebhookDeliveryOnly: false,
  importedOnly: false,
});

let searchDebounce: ReturnType<typeof setTimeout> | undefined;

const listParams = computed<Record<string, string>>(() => {
  const params: Record<string, string> = {};
  if (filters.value.search.trim() !== '') params.search = filters.value.search.trim();
  if (filters.value.type !== 'all') params.type = filters.value.type;
  if (filters.value.status !== 'all') params.status = filters.value.status;
  if (filters.value.visibility !== 'all') params.visibility = filters.value.visibility;
  if (filters.value.source !== 'all') params.source = filters.value.source;
  if (filters.value.unreadOnly) params.unread = 'true';
  if (filters.value.assignment !== 'all') params.assignment = filters.value.assignment;
  if (filters.value.participantRestriction !== 'all') params.participant_restriction = filters.value.participantRestriction;
  if (filters.value.failedWebhookDeliveryOnly) params.failed_webhook_delivery = 'true';
  if (filters.value.importedOnly) params.imported = 'true';
  return params;
});

const loadConversations = async (): Promise<void> => {
  isConversationsLoading.value = true;
  conversationsError.value = '';

  try {
    const response = await chatAdminService.listConversations(listParams.value);
    conversations.value = response.items;

    if (selectedConversationId.value && !conversations.value.some((item) => item.id === selectedConversationId.value)) {
      selectedConversationId.value = null;
      selectedConversation.value = null;
      messages.value = [];
      participants.value = [];
    }
  } catch (error) {
    conversationsError.value = (error as { message?: string })?.message ?? 'Failed to load conversations.';
    conversations.value = [];
  } finally {
    isConversationsLoading.value = false;
  }
};

const loadConversationDetails = async (conversationId: number): Promise<void> => {
  isMessagesLoading.value = true;
  isParticipantsLoading.value = true;
  messagesError.value = '';
  participantsError.value = '';

  try {
    const [conversation, nextMessages, nextParticipants] = await Promise.all([
      chatAdminService.getConversation(conversationId),
      chatAdminService.listMessages(conversationId, { per_page: 50 }),
      chatAdminService.listParticipants(conversationId),
    ]);

    selectedConversation.value = conversation;
    messages.value = nextMessages;
    participants.value = nextParticipants;
  } catch (error) {
    const safeMessage = (error as { message?: string })?.message ?? 'Failed to load conversation details.';
    messagesError.value = safeMessage;
    participantsError.value = safeMessage;
    messages.value = [];
    participants.value = [];
  } finally {
    isMessagesLoading.value = false;
    isParticipantsLoading.value = false;
  }
};

const onSelectConversation = async (conversationId: number): Promise<void> => {
  selectedConversationId.value = conversationId;
  await loadConversationDetails(conversationId);
};

const onSearchChange = (value: string): void => {
  filters.value.search = value;
  if (searchDebounce) clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    void loadConversations();
  }, 250);
};

const onFilterTypeChange = async (value: string): Promise<void> => {
  filters.value.type = value;
  await loadConversations();
};

const onFilterStatusChange = async (value: string): Promise<void> => {
  filters.value.status = value;
  await loadConversations();
};

const onFilterVisibilityChange = async (value: string): Promise<void> => {
  filters.value.visibility = value;
  await loadConversations();
};

const onFilterSourceChange = async (value: string): Promise<void> => {
  filters.value.source = value;
  await loadConversations();
};

const onUnreadOnlyChange = async (value: boolean): Promise<void> => {
  filters.value.unreadOnly = value;
  await loadConversations();
};

const onAssignmentChange = async (value: 'all' | 'assigned' | 'unassigned'): Promise<void> => {
  filters.value.assignment = value;
  await loadConversations();
};

const onParticipantRestrictionChange = async (value: 'all' | 'blocked' | 'restricted'): Promise<void> => {
  filters.value.participantRestriction = value;
  await loadConversations();
};

const onFailedWebhookDeliveryOnlyChange = async (value: boolean): Promise<void> => {
  filters.value.failedWebhookDeliveryOnly = value;
  await loadConversations();
};

const onImportedOnlyChange = async (value: boolean): Promise<void> => {
  filters.value.importedOnly = value;
  await loadConversations();
};

const onResetFilters = async (): Promise<void> => {
  filters.value = {
    search: '',
    type: 'all',
    status: 'all',
    visibility: 'all',
    source: 'all',
    unreadOnly: false,
    assignment: 'all',
    participantRestriction: 'all',
    failedWebhookDeliveryOnly: false,
    importedOnly: false,
  };
  await loadConversations();
};

onMounted(async () => {
  await loadConversations();
});
</script>

<style scoped>
.chat-admin-page{display:grid;gap:12px}
.chat-admin-page__header{margin-top:0}
.chat-admin-page__title{margin:0;font-size:18px;color:#f8fafc}
.chat-admin-page__subtitle{margin:6px 0 0;color:#94a3b8;font-size:13px}
.chat-admin-page__layout{display:grid;grid-template-columns:minmax(280px,360px) minmax(0,1fr);gap:12px;align-items:start}
.chat-admin-page__details{display:grid;gap:12px}
@media (max-width:1080px){.chat-admin-page__layout{grid-template-columns:1fr}}
</style>
