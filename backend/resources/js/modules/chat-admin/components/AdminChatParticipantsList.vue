<template>
  <section class="chat-admin-participants c-card">
    <h3 class="chat-admin-participants__title">Participants</h3>

    <div v-if="loading" class="chat-admin-participants__state">
      <BaseLoader label="Loading participants..." />
    </div>

    <BaseErrorState
      v-else-if="error"
      title="Failed to load participants"
      :description="error"
    />

    <BaseEmptyState
      v-else-if="items.length === 0"
      title="No participants"
      description="This conversation has no participant records."
    />

    <ul v-else class="chat-admin-participants__items">
      <li v-for="participant in items" :key="participant.user_id" class="chat-admin-participants__item">
        <div class="chat-admin-participants__head">
          <strong>{{ participant.name || `User #${participant.user_id}` }}</strong>
          <span>#{{ participant.user_id }}</span>
        </div>
        <div class="chat-admin-participants__badges">
          <span class="badge">{{ participant.role || 'member' }}</span>
          <span class="badge">{{ participant.status || 'unknown' }}</span>
          <span class="badge">{{ participant.access_state || 'full' }}</span>
        </div>
      </li>
    </ul>
  </section>
</template>

<script setup lang="ts">
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import BaseLoader from '../../../shared/components/ui/BaseLoader.vue';
import type { ChatAdminParticipant } from '../types/chat-admin.types';

defineProps<{
  items: ChatAdminParticipant[];
  loading: boolean;
  error: string;
}>();
</script>

<style scoped>
.chat-admin-participants{margin-top:0;display:grid;gap:10px}
.chat-admin-participants__title{margin:0;color:#f8fafc;font-size:15px}
.chat-admin-participants__state{padding:8px 0}
.chat-admin-participants__items{list-style:none;padding:0;margin:0;display:grid;gap:8px;max-height:30vh;overflow:auto}
.chat-admin-participants__item{border:1px solid rgba(71,85,105,.45);border-radius:8px;background:rgba(15,23,42,.5);padding:8px;display:grid;gap:8px}
.chat-admin-participants__head{display:flex;justify-content:space-between;gap:8px;color:#e2e8f0;font-size:12px}
.chat-admin-participants__head span{color:#94a3b8}
.chat-admin-participants__badges{display:flex;gap:6px;flex-wrap:wrap}
.badge{font-size:10px;border-radius:999px;padding:2px 8px;border:1px solid rgba(71,85,105,.55);color:#cbd5e1}
</style>

