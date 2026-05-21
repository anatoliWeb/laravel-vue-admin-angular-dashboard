import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ChatMessageThreadComponent } from './chat-message-thread.component';

describe('ChatMessageThreadComponent', () => {
  let fixture: ComponentFixture<ChatMessageThreadComponent>;
  let component: ChatMessageThreadComponent;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChatMessageThreadComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(ChatMessageThreadComponent);
    component = fixture.componentInstance;
  });

  it('thread renders messages', () => {
    component.conversation = { id: 11, title: 'Room' };
    component.messages = [
      { id: 1, conversation_id: 11, body: 'Hello', status: 'sent' },
      { id: 2, conversation_id: 11, body: null, status: 'deleted' },
    ];
    fixture.detectChanges();

    const items = fixture.nativeElement.querySelectorAll('[data-testid="message-item"]');
    expect(items.length).toBe(2);
    expect(fixture.nativeElement.textContent).toContain('Message deleted');
  });

  it('thread renders empty state', () => {
    component.conversation = { id: 11, title: 'Room' };
    component.messages = [];
    component.loading = false;
    component.error = null;
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('No messages yet.');
  });

  it('loading state renders safely', () => {
    component.loading = true;
    component.error = null;
    fixture.detectChanges();
    expect(fixture.nativeElement.textContent).toContain('Loading messages...');
  });

  it('error state renders safely', () => {
    component.loading = false;
    component.error = 'Load failed';
    fixture.detectChanges();
    expect(fixture.nativeElement.textContent).toContain('Load failed');
  });
});
