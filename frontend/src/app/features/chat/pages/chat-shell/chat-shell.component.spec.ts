import { ComponentFixture, TestBed } from '@angular/core/testing';
import { BehaviorSubject } from 'rxjs';
import { vi } from 'vitest';
import { ChatShellComponent } from './chat-shell.component';
import { ChatStateService } from '../../services/chat-state.service';
import type { ChatConversation, ChatMessage } from '../../models/chat.model';

describe('ChatShellComponent', () => {
  let fixture: ComponentFixture<ChatShellComponent>;
  let component: ChatShellComponent;

  const conversations$ = new BehaviorSubject<ChatConversation[]>([
    { id: 1, title: 'General', type: 'group' },
  ]);
  const activeConversation$ = new BehaviorSubject<ChatConversation | null>(null);
  const messages$ = new BehaviorSubject<ChatMessage[]>([]);
  const loading$ = new BehaviorSubject<boolean>(false);
  const error$ = new BehaviorSubject<string | null>(null);

  const chatStateMock = {
    conversations$,
    activeConversation$,
    messages$,
    loading$,
    error$,
    loadConversations: vi.fn().mockResolvedValue(undefined),
    openConversation: vi.fn().mockResolvedValue(undefined),
    sendMessage: vi.fn().mockResolvedValue(undefined),
    sending$: new BehaviorSubject<boolean>(false),
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChatShellComponent],
      providers: [
        { provide: ChatStateService, useValue: chatStateMock },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(ChatShellComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('shell loads conversations on init', () => {
    expect(chatStateMock.loadConversations).toHaveBeenCalled();
  });

  it('clicking conversation opens it', () => {
    const button: HTMLButtonElement | null = fixture.nativeElement.querySelector('[data-testid="conversation-item"]');
    expect(button).not.toBeNull();
    button?.click();

    expect(chatStateMock.openConversation).toHaveBeenCalledWith(1);
    expect(component.selectedConversationId).toBe(1);
  });

  it('composer submit sends message via state service', () => {
    component.sendMessage('Hi');
    expect(chatStateMock.sendMessage).toHaveBeenCalledWith('Hi');
  });
});
