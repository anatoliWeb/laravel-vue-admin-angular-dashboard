import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import type { ChatConversation } from '../../models/chat.model';

@Component({
  selector: 'app-chat-conversation-list',
  templateUrl: './chat-conversation-list.component.html',
  styleUrls: ['./chat-conversation-list.component.scss'],
  standalone: true,
  imports: [CommonModule],
})
export class ChatConversationListComponent {
  @Input() conversations: ChatConversation[] = [];
  @Input() selectedConversationId: number | null = null;
  @Input() loading = false;
  @Input() error: string | null = null;

  @Output() readonly conversationSelected = new EventEmitter<ChatConversation>();

  selectConversation(conversation: ChatConversation): void {
    this.conversationSelected.emit(conversation);
  }

  typeBadgeLabel(type?: string): string {
    const normalized = (type ?? 'chat').toLowerCase();
    if (['direct', 'group', 'support', 'external', 'system'].includes(normalized)) {
      return normalized;
    }
    return 'chat';
  }

  visibilityBadgeLabel(visibility?: string): string {
    const normalized = (visibility ?? '').toLowerCase();
    if (normalized === 'public') return 'public';
    if (normalized === 'private') return 'private';
    return 'private';
  }
}
