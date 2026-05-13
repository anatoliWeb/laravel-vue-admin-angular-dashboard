import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-loading-state',
  template: `
    <div class="loading-state" *ngIf="visible">
      <div class="loading-state__spinner"></div>
      <p>{{ message }}</p>
    </div>
  `,
  styles: [`
    .loading-state{display:grid;place-items:center;gap:8px;padding:16px;color:#94a3b8}
    .loading-state__spinner{width:18px;height:18px;border-radius:999px;border:2px solid rgba(148,163,184,.35);border-top-color:#60a5fa;animation:spin .8s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
  `],
  standalone: false,
})
export class LoadingStateComponent {
  @Input() visible = false;
  @Input() message = 'Loading...';
}

