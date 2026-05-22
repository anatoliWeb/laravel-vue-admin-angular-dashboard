import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import type { ChatConversation } from '../../models/chat.model';

@Component({
  selector: 'app-chat-conversation-list',
  templateUrl: './chat-conversation-list.component.html',
  styleUrls: ['./chat-conversation-list.component.scss'],
  standalone: true,
  imports: [CommonModule, FormsModule],
})
export class ChatConversationListComponent {
  @Input() conversations: ChatConversation[] = [];
  @Input() totalConversationsCount = 0;
  @Input() selectedConversationId: number | null = null;
  @Input() loading = false;
  @Input() error: string | null = null;
  @Input() search = '';
  @Input() typeFilter = 'all';
  @Input() visibilityFilter = 'all';
  @Input() unreadOnly = false;

  @Output() readonly conversationSelected = new EventEmitter<ChatConversation>();
  @Output() readonly searchChange = new EventEmitter<string>();
  @Output() readonly typeFilterChange = new EventEmitter<string>();
  @Output() readonly visibilityFilterChange = new EventEmitter<string>();
  @Output() readonly unreadOnlyChange = new EventEmitter<boolean>();
  @Output() readonly resetFilters = new EventEmitter<void>();

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

  onSearchInput(value: string): void {
    this.searchChange.emit(value);
  }

  onTypeFilterChange(value: string): void {
    this.typeFilterChange.emit(value);
  }

  onVisibilityFilterChange(value: string): void {
    this.visibilityFilterChange.emit(value);
  }

  onUnreadOnlyChange(value: boolean): void {
    this.unreadOnlyChange.emit(value);
  }

  onResetFilters(): void {
    this.resetFilters.emit();
  }
}
