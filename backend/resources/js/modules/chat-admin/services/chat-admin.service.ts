import { api } from '../../../services/api/client';
import type { ApiResponse, PaginationMeta } from '../../../types/response.types';
import type { ChatAdminConversation, ChatAdminMessage, ChatAdminParticipant } from '../types/chat-admin.types';

export interface ChatAdminListParams {
  search?: string;
  type?: string;
  status?: string;
  visibility?: string;
  source?: string;
  unread?: boolean;
  assignment?: 'all' | 'assigned' | 'unassigned';
  participant_restriction?: 'all' | 'blocked' | 'restricted';
  failed_webhook_delivery?: boolean;
  imported?: boolean;
  per_page?: number;
}

export interface ChatAdminMessagesParams {
  per_page?: number;
}

const normalizeMessagesPayload = (payload: unknown): ChatAdminMessage[] => {
  if (!payload) return [];
  if (Array.isArray(payload)) return payload as ChatAdminMessage[];
  if (typeof payload === 'object' && payload !== null && Array.isArray((payload as { data?: unknown[] }).data)) {
    return ((payload as { data?: unknown[] }).data ?? []) as ChatAdminMessage[];
  }
  return [];
};

export const chatAdminService = {
  async listConversations(params: ChatAdminListParams = {}): Promise<{ items: ChatAdminConversation[]; meta?: PaginationMeta | Record<string, unknown> }> {
    const response = await api.get<ChatAdminConversation[] | { data?: ChatAdminConversation[] }>('/v1/chat/conversations', { params });
    const payload = response as ApiResponse<ChatAdminConversation[] | { data?: ChatAdminConversation[] }>;
    const items = Array.isArray(payload.data)
      ? payload.data
      : (payload.data?.data ?? []);

    return {
      items,
      meta: payload.meta,
    };
  },

  async getConversation(conversationId: number): Promise<ChatAdminConversation | null> {
    const response = await api.get<ChatAdminConversation>(`/v1/chat/conversations/${conversationId}`);
    return response.data ?? null;
  },

  async listMessages(conversationId: number, params: ChatAdminMessagesParams = {}): Promise<ChatAdminMessage[]> {
    const response = await api.get<ChatAdminMessage[] | { data?: ChatAdminMessage[] }>(
      `/v1/chat/conversations/${conversationId}/messages`,
      { params },
    );

    return normalizeMessagesPayload(response.data);
  },

  async searchMessages(
    conversationId: number,
    params: { q?: string; type?: string; sender_id?: number; from?: string; to?: string } = {},
  ): Promise<ChatAdminMessage[]> {
    const response = await api.get<ChatAdminMessage[] | { data?: ChatAdminMessage[] }>(
      `/v1/chat/conversations/${conversationId}/messages/search`,
      { params },
    );

    return normalizeMessagesPayload(response.data);
  },

  async listParticipants(conversationId: number): Promise<ChatAdminParticipant[]> {
    const response = await api.get<ChatAdminParticipant[] | { data?: ChatAdminParticipant[] }>(
      `/v1/chat/conversations/${conversationId}/participants`,
    );

    if (Array.isArray(response.data)) {
      return response.data;
    }

    return response.data?.data ?? [];
  },

  async sendMessage(conversationId: number, payload: { body: string; type?: 'text' }): Promise<ChatAdminMessage | null> {
    const response = await api.post<ChatAdminMessage, { body: string; type?: 'text' }>(
      `/v1/chat/conversations/${conversationId}/messages`,
      payload,
    );

    return response.data ?? null;
  },
};
