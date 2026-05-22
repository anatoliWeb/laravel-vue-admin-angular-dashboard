<template>
  <section class="chat-admin-list c-card">
    <h3 class="chat-admin-list__title">Conversations</h3>

    <div v-if="loading" class="chat-admin-list__state">
      <BaseLoader label="Loading conversations..." />
    </div>

    <BaseErrorState
      v-else-if="error"
      title="Failed to load conversations"
      :description="error"
    />

    <BaseEmptyState
      v-else-if="items.length === 0"
      title="No conversations"
      description="No admin-visible conversations match current filters."
    />

    <ul v-else class="chat-admin-list__items">
      <li
        v-for="conversation in items"
        :key="conversation.id"
        :class="['chat-admin-list__item', { 'is-active': selectedConversationId === conversation.id }]"
      >
        <button type="button" class="chat-admin-list__button" @click="$emit('select', conversation.id)">
          <div class="chat-admin-list__item-head">
            <strong>{{ conversation.title || `Conversation #${conversation.id}` }}</strong>
            <span class="chat-admin-list__id">#{{ conversation.id }}</span>
          </div>
          <div class="chat-admin-list__badges">
            <span class="badge">{{ conversation.type || 'unknown' }}</span>
            <span class="badge">{{ conversation.visibility || 'unknown' }}</span>
            <span class="badge">{{ conversation.status || 'unknown' }}</span>
            <span class="badge">{{ conversation.source || 'unknown' }}</span>
          </div>
          <small class="chat-admin-list__meta">Last: {{ formatDate(conversation.last_message_at) }}</small>
        </button>
      </li>
    </ul>
  </section>
</template>

<script setup lang="ts">
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import BaseLoader from '../../../shared/components/ui/BaseLoader.vue';
import type { ChatAdminConversation } from '../types/chat-admin.types';

defineProps<{
  items: ChatAdminConversation[];
  selectedConversationId: number | null;
  loading: boolean;
  error: string;
}>();

defineEmits<{
  select: [conversationId: number];
}>();

const formatDate = (value: string | null | undefined): string => {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return '-';
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(parsed);
};
</script>

<style scoped>
.chat-admin-list{margin-top:0;display:grid;gap:10px}
.chat-admin-list__title{margin:0;color:#f8fafc;font-size:15px}
.chat-admin-list__state{padding:10px 0}
.chat-admin-list__items{list-style:none;padding:0;margin:0;display:grid;gap:8px;max-height:70vh;overflow:auto}
.chat-admin-list__item{border:1px solid rgba(71,85,105,.5);border-radius:10px;background:rgba(15,23,42,.6)}
.chat-admin-list__item.is-active{border-color:rgba(96,165,250,.65);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
.chat-admin-list__button{width:100%;text-align:left;background:transparent;border:0;color:#e2e8f0;padding:10px;display:grid;gap:8px}
.chat-admin-list__item-head{display:flex;justify-content:space-between;gap:8px}
.chat-admin-list__id{color:#94a3b8;font-size:11px}
.chat-admin-list__badges{display:flex;gap:6px;flex-wrap:wrap}
.badge{font-size:10px;border-radius:999px;padding:2px 8px;border:1px solid rgba(71,85,105,.55);color:#cbd5e1}
.chat-admin-list__meta{color:#94a3b8;font-size:11px}
</style>

