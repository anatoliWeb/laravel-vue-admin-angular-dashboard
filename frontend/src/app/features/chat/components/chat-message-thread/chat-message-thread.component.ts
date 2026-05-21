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
  @Input() loading = false;
  @Input() error: string | null = null;
}
