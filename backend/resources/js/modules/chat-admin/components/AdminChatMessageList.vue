<template>
  <section class="chat-admin-messages c-card">
    <h3 class="chat-admin-messages__title">Messages</h3>

    <div v-if="loading" class="chat-admin-messages__state">
      <BaseLoader label="Loading messages..." />
    </div>

    <BaseErrorState
      v-else-if="error"
      title="Failed to load messages"
      :description="error"
    />

    <BaseEmptyState
      v-else-if="items.length === 0"
      title="No messages"
      description="No messages in this conversation."
    />

    <ul v-else class="chat-admin-messages__items">
      <li v-for="message in items" :key="message.id" class="chat-admin-messages__item">
        <div class="chat-admin-messages__head">
          <span>#{{ message.id }}</span>
          <span>{{ message.type || 'unknown' }}</span>
          <span>{{ message.status || 'unknown' }}</span>
          <span>sender: {{ message.sender_id ?? '-' }}</span>
          <span>{{ formatDate(message.sent_at || message.created_at) }}</span>
        </div>
        <p class="chat-admin-messages__body">
          {{ message.status === 'deleted' ? 'Message deleted' : (message.body || '-') }}
        </p>
      </li>
    </ul>
  </section>
</template>

<script setup lang="ts">
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import BaseLoader from '../../../shared/components/ui/BaseLoader.vue';
import type { ChatAdminMessage } from '../types/chat-admin.types';

defineProps<{
  items: ChatAdminMessage[];
  loading: boolean;
  error: string;
}>();

const formatDate = (value: string | null | undefined): string => {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return '-';
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(parsed);
};
</script>

<style scoped>
.chat-admin-messages{margin-top:0;display:grid;gap:10px}
.chat-admin-messages__title{margin:0;color:#f8fafc;font-size:15px}
.chat-admin-messages__state{padding:8px 0}
.chat-admin-messages__items{list-style:none;padding:0;margin:0;display:grid;gap:8px;max-height:45vh;overflow:auto}
.chat-admin-messages__item{border:1px solid rgba(71,85,105,.45);border-radius:8px;background:rgba(15,23,42,.5);padding:8px}
.chat-admin-messages__head{display:flex;gap:6px;flex-wrap:wrap;color:#94a3b8;font-size:11px}
.chat-admin-messages__body{margin:8px 0 0;color:#e2e8f0;font-size:13px;white-space:pre-wrap;word-break:break-word}
</style>

