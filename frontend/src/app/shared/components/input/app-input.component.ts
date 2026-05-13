import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-input',
  template: `
    <label class="app-input">
      <span *ngIf="label" class="app-input__label">{{ label }}</span>
      <input
        [type]="type"
        [value]="value"
        [placeholder]="placeholder"
        [disabled]="disabled"
        (input)="emitValue($event)"
      />
    </label>
  `,
  styles: [`
    .app-input{display:grid;gap:6px}
    .app-input__label{font-size:12px;color:#cbd5e1}
    .app-input input{height:38px;border:1px solid rgba(71,85,105,.55);background:rgba(15,23,42,.75);color:#e2e8f0;border-radius:10px;padding:0 10px}
  `],
  standalone: false,
})
export class AppInputComponent {
  @Input() value = '';
  @Input() label = '';
  @Input() placeholder = '';
  @Input() type: 'text' | 'email' | 'password' = 'text';
  @Input() disabled = false;
  @Output() valueChange = new EventEmitter<string>();

  emitValue(event: Event): void {
    this.valueChange.emit((event.target as HTMLInputElement).value);
  }
}

