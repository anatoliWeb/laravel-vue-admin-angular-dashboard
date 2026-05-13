import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-button',
  template: `
    <button class="app-button" [type]="type" [disabled]="disabled">
      <ng-content />
    </button>
  `,
  styles: [`
    .app-button{height:36px;padding:0 12px;border-radius:10px;border:1px solid rgba(59,130,246,.4);background:rgba(59,130,246,.16);color:#dbeafe;cursor:pointer}
    .app-button:disabled{opacity:.6;cursor:not-allowed}
  `],
  standalone: false,
})
export class AppButtonComponent {
  @Input() type: 'button' | 'submit' = 'button';
  @Input() disabled = false;
}

