import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import type { ChatConversation, ChatMessage } from '../../models/chat.model';

@Component({
  selector: 'app-chat-message-thread',
  templateUrl: './chat-message-thread.component.html',
  styleUrls: ['./chat-message-thread.component.scss'],
  standalone: true,
  imports: [CommonModule],
})
export class ChatMessageThreadComponent {
  @Input() conversation: ChatConversation | null = null;
  @Input() messages: ChatMessage[] = [];
  @Input() currentUserId: number | null = null;
  @Input() loading = false;
  @Input() error: string | null = null;

  isOwnMessage(message: ChatMessage): boolean {
    return this.currentUserId !== null && message.sender_id === this.currentUserId;
  }

  messageStatusLabel(message: ChatMessage): string {
    if (message.status === 'read') return 'Read';
    if (message.status === 'delivered') return 'Delivered';
    if (message.status === 'sent') return 'Sent';

    const delivery = (message.delivery_status ?? '').toLowerCase();
    if (delivery === 'read') return 'Read';
    if (delivery === 'delivered') return 'Delivered';
    if (delivery === 'sent') return 'Sent';

    return 'Sent';
  }

  readCountLabel(message: ChatMessage): string | null {
    const countRaw = message.read_count ?? message.reads_count;
    const count = typeof countRaw === 'number' ? countRaw : Number(countRaw ?? 0);
    if (!Number.isFinite(count) || count <= 0) {
      return null;
    }
    return `Read by ${count}`;
  }
}
