import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import type { ChatConversation } from '../../models/chat.model';

@Component({
  selector: 'app-chat-message-composer',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './chat-message-composer.component.html',
  styleUrls: ['./chat-message-composer.component.scss'],
})
export class ChatMessageComposerComponent {
  @Input() conversation: ChatConversation | null = null;
  @Input() sending = false;
  @Input() error: string | null = null;

  @Output() readonly messageSubmit = new EventEmitter<{ body: string; file?: File }>();

  draft = '';
  selectedFile: File | null = null;

  get trimmedDraft(): string {
    return this.draft.trim();
  }

  get canSend(): boolean {
    if (this.sending) return false;
    if (!this.conversation?.id) return false;
    if (this.trimmedDraft.length === 0) return false;
    if (this.isBlocked || this.isReadOnly) return false;
    if (this.isConversationClosedLike) return false;
    return true;
  }

  get canAttach(): boolean {
    if (!this.conversation?.id) return false;
    if (this.isBlocked || this.isReadOnly || this.isConversationClosedLike) return false;
    if (this.conversation.current_user_access?.can_attach === false) return false;
    return true;
  }

  get isReadOnly(): boolean {
    return this.conversation?.current_user_access?.access_state === 'read_only';
  }

  get isBlocked(): boolean {
    return this.conversation?.current_user_access?.access_state === 'blocked';
  }

  get isConversationClosedLike(): boolean {
    const status = this.conversation?.status;
    return status === 'closed' || status === 'archived' || status === 'deleted';
  }

  onKeydown(event: KeyboardEvent): void {
    if (event.key !== 'Enter') {
      return;
    }

    if (event.shiftKey) {
      return;
    }

    event.preventDefault();
    this.submit();
  }

  submit(): void {
    if (!this.canSend) {
      return;
    }

    this.messageSubmit.emit({ body: this.trimmedDraft, file: this.selectedFile ?? undefined });
    this.draft = '';
    this.selectedFile = null;
  }

  onFileSelected(event: Event): void {
    const target = event.target as HTMLInputElement | null;
    const file = target?.files?.[0];
    this.selectedFile = file ?? null;
  }

  clearSelectedFile(fileInput: HTMLInputElement): void {
    this.selectedFile = null;
    fileInput.value = '';
  }
}
