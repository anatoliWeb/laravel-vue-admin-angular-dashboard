import { ComponentFixture, TestBed } from '@angular/core/testing';
import { vi } from 'vitest';
import { ChatMessageComposerComponent } from './chat-message-composer.component';

describe('ChatMessageComposerComponent', () => {
  let fixture: ComponentFixture<ChatMessageComposerComponent>;
  let component: ChatMessageComposerComponent;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChatMessageComposerComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(ChatMessageComposerComponent);
    component = fixture.componentInstance;
    component.conversation = { id: 10, status: 'active' };
    fixture.detectChanges();
  });

  it('composer renders input and send button', () => {
    const textarea = fixture.nativeElement.querySelector('[data-testid="composer-textarea"]');
    const sendBtn = fixture.nativeElement.querySelector('[data-testid="composer-send"]');
    const fileInput = fixture.nativeElement.querySelector('[data-testid="composer-file-input"]');
    expect(textarea).not.toBeNull();
    expect(sendBtn).not.toBeNull();
    expect(fileInput).not.toBeNull();
  });

  it('empty body cannot be sent', () => {
    component.draft = '   ';
    fixture.detectChanges();
    const spy = vi.spyOn(component.messageSubmit, 'emit');
    component.submit();
    expect(spy).not.toHaveBeenCalled();
  });

  it('Enter sends message', () => {
    component.draft = 'Hello';
    fixture.detectChanges();
    const spy = vi.spyOn(component.messageSubmit, 'emit');
    const event = new KeyboardEvent('keydown', { key: 'Enter' });
    const preventDefault = vi.spyOn(event, 'preventDefault');
    component.onKeydown(event);
    expect(preventDefault).toHaveBeenCalled();
    expect(spy).toHaveBeenCalledWith({ body: 'Hello', file: undefined });
  });

  it('Shift+Enter does not send', () => {
    component.draft = 'Hello';
    fixture.detectChanges();
    const spy = vi.spyOn(component.messageSubmit, 'emit');
    component.onKeydown(new KeyboardEvent('keydown', { key: 'Enter', shiftKey: true }));
    expect(spy).not.toHaveBeenCalled();
  });

  it('composer clears after success submit', () => {
    component.draft = 'Hello';
    component.selectedFile = new File(['demo'], 'demo.txt', { type: 'text/plain' });
    component.submit();
    expect(component.draft).toBe('');
    expect(component.selectedFile).toBeNull();
  });

  it('composer disabled for read_only', () => {
    component.conversation = { id: 10, current_user_access: { user_id: 1, access_state: 'read_only' } };
    component.draft = 'Hello';
    fixture.detectChanges();
    expect(component.canSend).toBe(false);
  });

  it('composer disabled for blocked', () => {
    component.conversation = { id: 10, current_user_access: { user_id: 1, access_state: 'blocked' } };
    component.draft = 'Hello';
    fixture.detectChanges();
    expect(component.canSend).toBe(false);
  });

  it('composer disabled for closed/archived conversation', () => {
    component.conversation = { id: 10, status: 'closed' };
    component.draft = 'Hello';
    fixture.detectChanges();
    expect(component.canSend).toBe(false);

    component.conversation = { id: 10, status: 'archived' };
    fixture.detectChanges();
    expect(component.canSend).toBe(false);
  });

  it('send error renders safe message', () => {
    component.error = 'Failed to send message.';
    fixture.detectChanges();
    expect(fixture.nativeElement.textContent).toContain('Failed to send message.');
  });

  it('selected file name is shown and can be removed', () => {
    const file = new File(['a'], 'photo.png', { type: 'image/png' });
    component.selectedFile = file;
    fixture.detectChanges();
    expect(fixture.nativeElement.textContent).toContain('photo.png');

    const input = fixture.nativeElement.querySelector('[data-testid="composer-file-input"]') as HTMLInputElement;
    component.clearSelectedFile(input);
    expect(component.selectedFile).toBeNull();
  });

  it('composer disables attachment for read_only or blocked', () => {
    component.conversation = { id: 10, current_user_access: { user_id: 1, access_state: 'read_only' } };
    expect(component.canAttach).toBe(false);

    component.conversation = { id: 10, current_user_access: { user_id: 1, access_state: 'blocked' } };
    expect(component.canAttach).toBe(false);
  });
});
