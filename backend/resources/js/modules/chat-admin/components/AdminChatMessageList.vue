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
        <div class="chat-admin-messages__monitoring">
          <div class="chat-admin-messages__monitoring-head">
            <strong>Delivery / Read</strong>
            <span
              v-if="isFailedDelivery(message)"
              class="chat-admin-messages__failed-badge"
            >
              failed
            </span>
          </div>

          <ul v-if="hasMonitoringData(message)" class="chat-admin-messages__monitoring-list">
            <li>Status: {{ message.status || '-' }}</li>
            <li>Delivery: {{ message.delivery_status || '-' }}</li>
            <li v-if="message.delivered_at">Delivered at: {{ formatDate(message.delivered_at) }}</li>
            <li v-if="message.read_at">Read at: {{ formatDate(message.read_at) }}</li>
            <li v-if="message.failed_at">Failed at: {{ formatDate(message.failed_at) }}</li>
            <li v-if="resolveReadCount(message) !== null">Read by {{ resolveReadCount(message) }}</li>
            <li v-if="resolveDeliveryCount(message) !== null">Deliveries: {{ resolveDeliveryCount(message) }}</li>
            <li v-if="resolveDeviceReadCount(message) !== null">Device reads: {{ resolveDeviceReadCount(message) }}</li>
            <li v-if="message.read_source">Read source: {{ message.read_source }}</li>
          </ul>
          <p v-else class="chat-admin-messages__monitoring-empty">No delivery/read data</p>

          <div v-if="safeDeviceReads(message).length > 0" class="chat-admin-messages__device-reads">
            <h4>Per-device reads (safe)</h4>
            <ul>
              <li v-for="(row, index) in safeDeviceReads(message)" :key="`${message.id}-device-read-${index}`">
                user: {{ row.user_id ?? '-' }}, device: {{ row.device_type || '-' }}, read_at: {{ formatDate(row.read_at) }}
              </li>
            </ul>
          </div>
        </div>
      </li>
    </ul>
  </section>
</template>

<script setup lang="ts">
import BaseEmptyState from '../../../shared/components/ui/BaseEmptyState.vue';
import BaseErrorState from '../../../shared/components/ui/BaseErrorState.vue';
import BaseLoader from '../../../shared/components/ui/BaseLoader.vue';
import type { ChatAdminMessage, ChatAdminMessageDeviceReadItem } from '../types/chat-admin.types';

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

const toSafeCount = (value: unknown): number | null => {
  if (typeof value === 'number' && Number.isFinite(value) && value >= 0) return value;
  return null;
};

const resolveReadCount = (message: ChatAdminMessage): number | null => {
  const direct = toSafeCount(message.read_count ?? message.reads_count);
  if (direct !== null) return direct;
  if (Array.isArray(message.message_reads)) return message.message_reads.length;
  return null;
};

const resolveDeliveryCount = (message: ChatAdminMessage): number | null => {
  const direct = toSafeCount(message.delivery_count ?? message.deliveries_count);
  if (direct !== null) return direct;
  if (Array.isArray(message.message_deliveries)) return message.message_deliveries.length;
  return null;
};

const resolveDeviceReadCount = (message: ChatAdminMessage): number | null => {
  const direct = toSafeCount(message.device_read_count);
  if (direct !== null) return direct;
  if (Array.isArray(message.device_reads)) return message.device_reads.length;
  return null;
};

const hasMonitoringData = (message: ChatAdminMessage): boolean => {
  return Boolean(
    message.status
      || message.delivery_status
      || message.delivered_at
      || message.read_at
      || message.failed_at
      || message.read_source
      || resolveReadCount(message) !== null
      || resolveDeliveryCount(message) !== null
      || resolveDeviceReadCount(message) !== null,
  );
};

const isFailedDelivery = (message: ChatAdminMessage): boolean => {
  const status = String(message.delivery_status ?? message.status ?? '').toLowerCase();
  return status.includes('fail') || Boolean(message.failed_at);
};

const safeDeviceReads = (message: ChatAdminMessage): ChatAdminMessageDeviceReadItem[] => {
  if (!Array.isArray(message.device_reads)) return [];
  return message.device_reads.map((row) => ({
    user_id: row.user_id,
    read_at: row.read_at ?? null,
    device_type: row.device_type ?? null,
  }));
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
.chat-admin-messages__monitoring{margin-top:8px;padding-top:8px;border-top:1px solid rgba(71,85,105,.45);display:grid;gap:8px}
.chat-admin-messages__monitoring-head{display:flex;align-items:center;gap:8px;color:#cbd5e1;font-size:12px}
.chat-admin-messages__failed-badge{font-size:10px;border-radius:999px;padding:2px 8px;border:1px solid rgba(239,68,68,.5);background:rgba(127,29,29,.25);color:#fca5a5;text-transform:uppercase}
.chat-admin-messages__monitoring-list{list-style:none;padding:0;margin:0;display:grid;gap:4px;color:#94a3b8;font-size:12px}
.chat-admin-messages__monitoring-empty{margin:0;color:#64748b;font-size:12px}
.chat-admin-messages__device-reads h4{margin:0 0 6px;color:#cbd5e1;font-size:12px}
.chat-admin-messages__device-reads ul{list-style:none;padding:0;margin:0;display:grid;gap:4px;color:#94a3b8;font-size:12px}
</style>
