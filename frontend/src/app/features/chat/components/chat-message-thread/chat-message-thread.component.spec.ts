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
    component.currentUserId = 5;
    component.messages = [
      { id: 1, conversation_id: 11, sender_id: 5, body: 'Hello', status: 'read', read_count: 2 },
      { id: 2, conversation_id: 11, body: null, status: 'deleted' },
    ];
    fixture.detectChanges();

    const items = fixture.nativeElement.querySelectorAll('[data-testid="message-item"]');
    expect(items.length).toBe(2);
    expect(fixture.nativeElement.textContent).toContain('Message deleted');
    expect(fixture.nativeElement.textContent).toContain('Read');
    expect(fixture.nativeElement.textContent).toContain('Read by 2');
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

  it('thread renders delivered/sent fallback', () => {
    component.conversation = { id: 11, title: 'Room' };
    component.currentUserId = 9;
    component.messages = [
      { id: 3, conversation_id: 11, sender_id: 9, body: 'A', status: 'delivered' },
      { id: 4, conversation_id: 11, sender_id: 8, body: 'B', status: 'sent' },
    ];
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('Delivered');
    expect(fixture.nativeElement.textContent).toContain('Sent');
  });

  it('does not render sensitive device metadata fields', () => {
    component.conversation = { id: 11, title: 'Room' };
    component.currentUserId = 9;
    component.messages = [
      {
        id: 5,
        conversation_id: 11,
        sender_id: 9,
        body: 'safe',
        status: 'sent',
        device_key: 'chatdev_secret',
        user_agent: 'UA',
        ip_address: '127.0.0.1',
      } as any,
    ];
    fixture.detectChanges();

    const content = fixture.nativeElement.textContent as string;
    expect(content).not.toContain('chatdev_secret');
    expect(content).not.toContain('127.0.0.1');
    expect(content).not.toContain('UA');
  });
});
