import { Injectable } from '@angular/core';
import { BehaviorSubject, firstValueFrom } from 'rxjs';
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

  readonly conversations$ = this.conversationsSubject.asObservable();
  readonly activeConversation$ = this.activeConversationSubject.asObservable();
  readonly messages$ = this.messagesSubject.asObservable();
  readonly loading$ = this.loadingSubject.asObservable();
  readonly sending$ = this.sendingSubject.asObservable();
  readonly error$ = this.errorSubject.asObservable();
  readonly typingUsers$ = this.typingUsersSubject.asObservable();
  readonly presenceUsers$ = this.presenceUsersSubject.asObservable();

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
      const [conversationResponse, messagesResponse] = await Promise.all([
        firstValueFrom(this.chatApi.getConversation(conversationId)),
        firstValueFrom(this.chatApi.listMessages(conversationId, { per_page: 50 })),
      ]);

      this.activeConversationSubject.next(conversationResponse.data ?? null);
      this.messagesSubject.next(Array.isArray(messagesResponse.data) ? messagesResponse.data : []);
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
