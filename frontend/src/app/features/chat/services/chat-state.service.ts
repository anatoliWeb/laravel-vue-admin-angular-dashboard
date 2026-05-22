import { Injectable } from '@angular/core';
import { BehaviorSubject, combineLatest, firstValueFrom, map } from 'rxjs';
import { ChatApiService } from '../../../core/services/chat-api.service';
import { ChatDeviceService } from '../../../core/services/chat-device.service';
import type { ChatConversation, ChatMessage, ChatPresenceUser } from '../models/chat.model';

@Injectable({ providedIn: 'root' })
export class ChatStateService {
  private readonly conversationsSubject = new BehaviorSubject<ChatConversation[]>([]);
  private readonly activeConversationSubject = new BehaviorSubject<ChatConversation | null>(null);
  private readonly messagesSubject = new BehaviorSubject<ChatMessage[]>([]);
  private readonly loadingSubject = new BehaviorSubject<boolean>(false);
  private readonly sendingSubject = new BehaviorSubject<boolean>(false);
  private readonly errorSubject = new BehaviorSubject<string | null>(null);
  private readonly typingUsersSubject = new BehaviorSubject<number[]>([]);
  private readonly presenceUsersSubject = new BehaviorSubject<ChatPresenceUser[]>([]);
  private readonly conversationSearchSubject = new BehaviorSubject<string>('');
  private readonly conversationTypeFilterSubject = new BehaviorSubject<string>('all');
  private readonly conversationVisibilityFilterSubject = new BehaviorSubject<string>('all');
  private readonly unreadOnlySubject = new BehaviorSubject<boolean>(false);

  readonly conversations$ = this.conversationsSubject.asObservable();
  readonly activeConversation$ = this.activeConversationSubject.asObservable();
  readonly messages$ = this.messagesSubject.asObservable();
  readonly loading$ = this.loadingSubject.asObservable();
  readonly sending$ = this.sendingSubject.asObservable();
  readonly error$ = this.errorSubject.asObservable();
  readonly typingUsers$ = this.typingUsersSubject.asObservable();
  readonly presenceUsers$ = this.presenceUsersSubject.asObservable();
  readonly conversationSearch$ = this.conversationSearchSubject.asObservable();
  readonly conversationTypeFilter$ = this.conversationTypeFilterSubject.asObservable();
  readonly conversationVisibilityFilter$ = this.conversationVisibilityFilterSubject.asObservable();
  readonly unreadOnly$ = this.unreadOnlySubject.asObservable();
  readonly filteredConversations$ = combineLatest([
    this.conversations$,
    this.conversationSearch$,
    this.conversationTypeFilter$,
    this.conversationVisibilityFilter$,
    this.unreadOnly$,
  ]).pipe(
    map(([conversations, search, typeFilter, visibilityFilter, unreadOnly]) => {
      const normalizedSearch = search.trim().toLowerCase();
      return conversations.filter((conversation) => {
        if (typeFilter !== 'all' && (conversation.type ?? '').toLowerCase() !== typeFilter) {
          return false;
        }

        if (visibilityFilter !== 'all' && (conversation.visibility ?? '').toLowerCase() !== visibilityFilter) {
          return false;
        }

        if (unreadOnly && (conversation.unread_count ?? 0) <= 0) {
          return false;
        }

        if (normalizedSearch.length > 0) {
          const haystack = [
            conversation.title ?? '',
            conversation.description ?? '',
            conversation.type ?? '',
            conversation.source ?? '',
          ].join(' ').toLowerCase();

          if (!haystack.includes(normalizedSearch)) {
            return false;
          }
        }

        return true;
      });
    }),
  );

  constructor(
    private readonly chatApi: ChatApiService,
    private readonly chatDevice: ChatDeviceService,
  ) {}

  async loadConversations(params?: Record<string, string | number | boolean>): Promise<void> {
    this.loadingSubject.next(true);
    this.errorSubject.next(null);
    try {
      await this.chatDevice.ensureRegistered(this.chatApi);
      const response = await firstValueFrom(this.chatApi.listConversations(params));
      this.conversationsSubject.next(Array.isArray(response.data) ? response.data : []);
    } catch (error) {
      this.errorSubject.next(this.toSafeError(error, 'Failed to load conversations.'));
    } finally {
      this.loadingSubject.next(false);
    }
  }

  async openConversation(conversationId: number): Promise<void> {
    this.loadingSubject.next(true);
    this.errorSubject.next(null);
    try {
      await this.chatDevice.ensureRegistered(this.chatApi);
      const [conversationResponse, messagesResponse] = await Promise.all([
        firstValueFrom(this.chatApi.getConversation(conversationId)),
        firstValueFrom(this.chatApi.listMessages(conversationId, { per_page: 50 })),
      ]);

      this.activeConversationSubject.next(conversationResponse.data ?? null);
      this.messagesSubject.next(Array.isArray(messagesResponse.data) ? messagesResponse.data : []);
      if (this.canMarkConversationRead(conversationResponse.data ?? null)) {
        await this.markActiveConversationRead();
      }
    } catch (error) {
      this.errorSubject.next(this.toSafeError(error, 'Failed to open conversation.'));
    } finally {
      this.loadingSubject.next(false);
    }
  }

  async loadMessages(conversationId: number, params?: Record<string, string | number | boolean>): Promise<void> {
    this.loadingSubject.next(true);
    this.errorSubject.next(null);
    try {
      const response = await firstValueFrom(this.chatApi.listMessages(conversationId, params));
      this.messagesSubject.next(Array.isArray(response.data) ? response.data : []);
    } catch (error) {
      this.errorSubject.next(this.toSafeError(error, 'Failed to load messages.'));
    } finally {
      this.loadingSubject.next(false);
    }
  }

  async sendMessage(body: string): Promise<void> {
    const active = this.activeConversationSubject.value;
    if (!active?.id) {
      return;
    }

    const trimmedBody = body.trim();
    if (trimmedBody.length === 0) {
      return;
    }

    this.errorSubject.next(null);
    this.sendingSubject.next(true);
    try {
      const response = await firstValueFrom(this.chatApi.sendMessage(active.id, { body: trimmedBody, type: 'text' }));
      if (response.data) {
        const exists = this.messagesSubject.value.some((message) => message.id === response.data?.id);
        if (!exists) {
          this.messagesSubject.next([...this.messagesSubject.value, response.data]);
        }
      }
    } catch (error) {
      this.errorSubject.next(this.toSafeError(error, 'Failed to send message.'));
    } finally {
      this.sendingSubject.next(false);
    }
  }

  async sendMessageWithAttachment(body: string, file: File): Promise<void> {
    const active = this.activeConversationSubject.value;
    if (!active?.id) {
      return;
    }

    const trimmedBody = body.trim();
    if (trimmedBody.length === 0) {
      return;
    }

    this.errorSubject.next(null);
    this.sendingSubject.next(true);
    try {
      const messageResponse = await firstValueFrom(this.chatApi.sendMessage(active.id, { body: trimmedBody, type: 'text' }));
      const createdMessage = messageResponse.data;

      if (!createdMessage?.id) {
        throw new Error('Failed to create message for attachment upload.');
      }

      const exists = this.messagesSubject.value.some((message) => message.id === createdMessage.id);
      if (!exists) {
        this.messagesSubject.next([...this.messagesSubject.value, createdMessage]);
      }

      await this.uploadAttachment(createdMessage.id, file);
      await this.loadMessages(active.id, { per_page: 50 });
    } catch (error) {
      this.errorSubject.next(this.toSafeError(error, 'Failed to upload attachment.'));
    } finally {
      this.sendingSubject.next(false);
    }
  }

  async uploadAttachment(messageId: number, file: File): Promise<void> {
    await firstValueFrom(this.chatApi.uploadAttachment(messageId, file));
  }

  async markActiveConversationRead(): Promise<void> {
    const active = this.activeConversationSubject.value;
    if (!active?.id) {
      return;
    }

    try {
      await this.chatDevice.ensureRegistered(this.chatApi);
      await firstValueFrom(this.chatApi.markConversationRead(active.id, {
        device_key: this.chatDevice.getDeviceKey(),
      }));
    } catch (error) {
      this.errorSubject.next(this.toSafeError(error, 'Failed to mark conversation as read.'));
    }
  }

  private canMarkConversationRead(conversation: ChatConversation | null): boolean {
    if (!conversation?.id) {
      return false;
    }

    const access = conversation.current_user_access;
    if (access?.access_state === 'hidden') return false;
    if (access?.block_display_mode === 'hide_chat') return false;
    if (access?.access_state === 'blocked' && access?.block_display_mode === 'show_notice') return false;
    return true;
  }

  async markMessageRead(messageId: number): Promise<void> {
    const active = this.activeConversationSubject.value;
    if (!active?.id || !messageId) {
      return;
    }

    try {
      await this.chatDevice.ensureRegistered(this.chatApi);
      await firstValueFrom(this.chatApi.markMessageRead(messageId, {
        device_key: this.chatDevice.getDeviceKey(),
      }));
    } catch (error) {
      this.errorSubject.next(this.toSafeError(error, 'Failed to mark message as read.'));
    }
  }

  async startTyping(): Promise<void> {
    const active = this.activeConversationSubject.value;
    if (!active?.id) {
      return;
    }

    try {
      await firstValueFrom(this.chatApi.startTyping(active.id, {
        device_key: this.chatDevice.getDeviceKey(),
        device_type: 'browser',
      }));
    } catch {
      // Typing is best-effort and should not break the screen flow.
    }
  }

  async stopTyping(): Promise<void> {
    const active = this.activeConversationSubject.value;
    if (!active?.id) {
      return;
    }

    try {
      await firstValueFrom(this.chatApi.stopTyping(active.id, {
        device_key: this.chatDevice.getDeviceKey(),
        device_type: 'browser',
      }));
    } catch {
      // Typing is best-effort and should not break the screen flow.
    }
  }

  setConversationSearch(value: string): void {
    this.conversationSearchSubject.next(value);
  }

  setConversationTypeFilter(value: string): void {
    this.conversationTypeFilterSubject.next(value);
  }

  setConversationVisibilityFilter(value: string): void {
    this.conversationVisibilityFilterSubject.next(value);
  }

  setUnreadOnly(value: boolean): void {
    this.unreadOnlySubject.next(value);
  }

  resetConversationFilters(): void {
    this.conversationSearchSubject.next('');
    this.conversationTypeFilterSubject.next('all');
    this.conversationVisibilityFilterSubject.next('all');
    this.unreadOnlySubject.next(false);
  }

  private toSafeError(error: unknown, fallback: string): string {
    if (error && typeof error === 'object' && 'message' in error) {
      const message = String((error as { message?: unknown }).message ?? '').trim();
      if (message.length > 0) {
        return message;
      }
    }
    return fallback;
  }
}
