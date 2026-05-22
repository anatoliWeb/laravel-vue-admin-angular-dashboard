<template>
  <section class="chat-admin-details c-card">
    <h3 class="chat-admin-details__title">Conversation Details</h3>

    <BaseEmptyState
      v-if="!conversation"
      title="No conversation selected"
      description="Choose a conversation from the list to view details."
    />

    <dl v-else class="chat-admin-details__grid">
      <div><dt>ID</dt><dd>{{ conversation.id }}</dd></div>
      <div><dt>UUID</dt><dd>{{ conversation.uuid || '-' }}</dd></div>
      <div><dt>Type</dt><dd>{{ conversation.type || '-' }}</dd></div>
      <div><dt>Visibility</dt><dd>{{ conversation.visibility || '-' }}</dd></div>
      <div><dt>Status</dt><dd>{{ conversation.status || '-' }}</dd></div>
      <div><dt>Source</dt><dd>{{ conversation.source || '-' }}</dd></div>
      <div><dt>Participants</dt><dd>{{ conversation.participants_count ?? '-' }}</dd></div>
      <div><dt>Unread</dt><dd>{{ conversation.unread_count ?? '-' }}</dd></div>
      <div><dt>Last Message At</dt><dd>{{ formatDate(conversation.last_message_at) }}</dd></div>
      <div class="chat-admin-details__wide"><dt>Title</dt><dd>{{ conversation.title || '-' }}</dd></div>
      <div class="chat-admin-details__wide"><dt>Description</dt><dd>{{ conversation.description || '-' }}</dd></div>
    </dl>
  </section>
</template>

<script setup lang="ts">
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import type { ChatAdminConversation } from '../types/chat-admin.types';

defineProps<{
  conversation: ChatAdminConversation | null;
}>();

const formatDate = (value: string | null | undefined): string => {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return '-';
  return new Intl.DateTimeFormat('en-US', { dateStyle: 'medium', timeStyle: 'short' }).format(parsed);
};
</script>

<style scoped>
.chat-admin-details{margin-top:0;display:grid;gap:10px}
.chat-admin-details__title{margin:0;color:#f8fafc;font-size:15px}
.chat-admin-details__grid{display:grid;grid-template-columns:repeat(3,minmax(150px,1fr));gap:10px;margin:0}
.chat-admin-details__grid div{padding:8px;border:1px solid rgba(71,85,105,.45);border-radius:8px;background:rgba(15,23,42,.4)}
.chat-admin-details__grid dt{font-size:11px;text-transform:uppercase;color:#94a3b8}
.chat-admin-details__grid dd{margin:5px 0 0;color:#e2e8f0;font-size:12px;word-break:break-word}
.chat-admin-details__wide{grid-column:1 / -1}
@media (max-width:980px){.chat-admin-details__grid{grid-template-columns:1fr 1fr}}
@media (max-width:640px){.chat-admin-details__grid{grid-template-columns:1fr}}
</style>

