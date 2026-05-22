import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SharedModule } from '../../../../shared/shared.module';
import { AuthStateService } from '../../../../core/services/auth-state.service';
import { ChatStateService } from '../../services/chat-state.service';
import type { ChatConversation } from '../../models/chat.model';
import { ChatConversationListComponent } from '../../components/chat-conversation-list/chat-conversation-list.component';
import { ChatMessageThreadComponent } from '../../components/chat-message-thread/chat-message-thread.component';
import { ChatMessageComposerComponent } from '../../components/chat-message-composer/chat-message-composer.component';

@Component({
  selector: 'app-chat-shell',
  templateUrl: './chat-shell.component.html',
  styleUrls: ['./chat-shell.component.scss'],
  standalone: true,
  imports: [CommonModule, SharedModule, ChatConversationListComponent, ChatMessageThreadComponent, ChatMessageComposerComponent],
})
export class ChatShellComponent implements OnInit {
  readonly conversations$;
  readonly activeConversation$;
  readonly messages$;
  readonly loading$;
  readonly error$;
  readonly sending$;
  readonly currentUserId: number | null;

  selectedConversationId: number | null = null;

  constructor(
    private readonly chatState: ChatStateService,
    private readonly authState: AuthStateService,
  ) {
    this.conversations$ = this.chatState.conversations$;
    this.activeConversation$ = this.chatState.activeConversation$;
    this.messages$ = this.chatState.messages$;
    this.loading$ = this.chatState.loading$;
    this.error$ = this.chatState.error$;
    this.sending$ = this.chatState.sending$;
    this.currentUserId = this.authState.userId;
  }

  ngOnInit(): void {
    void this.chatState.loadConversations();
  }

  selectConversation(conversation: ChatConversation): void {
    this.selectedConversationId = conversation.id;
    void this.chatState.openConversation(conversation.id);
  }

  sendMessage(body: string): void {
    void this.chatState.sendMessage(body);
  }
}
