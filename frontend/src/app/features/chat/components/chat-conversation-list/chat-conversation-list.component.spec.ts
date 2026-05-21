import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ChatConversationListComponent } from './chat-conversation-list.component';

describe('ChatConversationListComponent', () => {
  let fixture: ComponentFixture<ChatConversationListComponent>;
  let component: ChatConversationListComponent;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChatConversationListComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(ChatConversationListComponent);
    component = fixture.componentInstance;
  });

  it('list renders conversations', () => {
    component.conversations = [
      { id: 1, title: 'General' },
      { id: 2, title: 'Support' },
    ];
    fixture.detectChanges();

    const items = fixture.nativeElement.querySelectorAll('[data-testid="conversation-item"]');
    expect(items.length).toBe(2);
  });

  it('list renders empty state', () => {
    component.conversations = [];
    component.loading = false;
    component.error = null;
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('No conversations yet.');
  });
});
