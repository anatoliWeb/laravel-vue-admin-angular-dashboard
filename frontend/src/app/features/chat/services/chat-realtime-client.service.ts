import { Injectable } from '@angular/core';
import { Subscription } from 'rxjs';
import { ChatRealtimeMessagePayload, RealtimeService } from '../../../realtime/services/realtime.service';

@Injectable({ providedIn: 'root' })
export class ChatRealtimeClientService {
  private currentConversationId: number | null = null;
  private createdSub: Subscription | null = null;
  private updatedSub: Subscription | null = null;
  private deletedSub: Subscription | null = null;

  constructor(private readonly realtime: RealtimeService) {}

  subscribeToConversation(
    conversationId: number,
    handlers: {
      onMessageCreated: (message: ChatRealtimeMessagePayload) => void;
      onMessageUpdated: (message: ChatRealtimeMessagePayload) => void;
      onMessageDeleted: (payload: ChatRealtimeMessagePayload) => void;
    },
  ): void {
    if (this.currentConversationId === conversationId) {
      return;
    }

    this.unsubscribeFromConversation();
    this.realtime.connect();
    this.realtime.joinChatMessages(conversationId);

    this.createdSub = this.realtime.observeChatMessageCreated(conversationId).subscribe((payload) => {
      handlers.onMessageCreated(payload);
    });
    this.updatedSub = this.realtime.observeChatMessageUpdated(conversationId).subscribe((payload) => {
      handlers.onMessageUpdated(payload);
    });
    this.deletedSub = this.realtime.observeChatMessageDeleted(conversationId).subscribe((payload) => {
      handlers.onMessageDeleted(payload);
    });

    this.currentConversationId = conversationId;
  }

  unsubscribeFromConversation(): void {
    if (this.currentConversationId === null) {
      return;
    }

    this.realtime.leaveChatMessages(this.currentConversationId);
    this.createdSub?.unsubscribe();
    this.updatedSub?.unsubscribe();
    this.deletedSub?.unsubscribe();
    this.createdSub = null;
    this.updatedSub = null;
    this.deletedSub = null;
    this.currentConversationId = null;
  }
}

