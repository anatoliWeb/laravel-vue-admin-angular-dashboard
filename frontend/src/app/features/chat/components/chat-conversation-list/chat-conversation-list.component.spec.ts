import { ComponentFixture, TestBed } from '@angular/core/testing';
import { vi } from 'vitest';
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
      { id: 1, title: 'General', type: 'group', visibility: 'public' },
      { id: 2, title: 'Support' },
    ];
    fixture.detectChanges();

    const items = fixture.nativeElement.querySelectorAll('[data-testid="conversation-item"]');
    expect(items.length).toBe(2);
    expect(fixture.nativeElement.textContent).toContain('group');
    expect(fixture.nativeElement.textContent).toContain('public');
  });

  it('search input renders', () => {
    fixture.detectChanges();
    const search = fixture.nativeElement.querySelector('[data-testid="conversation-search"]');
    expect(search).not.toBeNull();
  });

  it('search and filters emit changes', () => {
    const searchSpy = vi.spyOn(component.searchChange, 'emit');
    const typeSpy = vi.spyOn(component.typeFilterChange, 'emit');
    const visibilitySpy = vi.spyOn(component.visibilityFilterChange, 'emit');
    const unreadSpy = vi.spyOn(component.unreadOnlyChange, 'emit');
    const resetSpy = vi.spyOn(component.resetFilters, 'emit');

    component.onSearchInput('gen');
    component.onTypeFilterChange('direct');
    component.onVisibilityFilterChange('public');
    component.onUnreadOnlyChange(true);
    component.onResetFilters();

    expect(searchSpy).toHaveBeenCalledWith('gen');
    expect(typeSpy).toHaveBeenCalledWith('direct');
    expect(visibilitySpy).toHaveBeenCalledWith('public');
    expect(unreadSpy).toHaveBeenCalledWith(true);
    expect(resetSpy).toHaveBeenCalled();
  });

  it('list renders empty state', () => {
    component.conversations = [];
    component.loading = false;
    component.error = null;
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('No conversations yet.');
  });

  it('list renders filtered empty state', () => {
    component.totalConversationsCount = 3;
    component.conversations = [];
    component.loading = false;
    component.error = null;
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('No conversations match your filters.');
  });
});
