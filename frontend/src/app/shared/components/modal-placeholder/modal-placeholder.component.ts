import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-modal-placeholder',
  template: `
    <div class="modal-placeholder">
      <strong>{{ title }}</strong>
      <p>{{ description }}</p>
    </div>
  `,
  styles: [`
    .modal-placeholder{border:1px solid rgba(71,85,105,.45);background:rgba(15,23,42,.8);border-radius:12px;padding:12px;color:#cbd5e1}
    .modal-placeholder p{margin:6px 0 0;color:#94a3b8}
  `],
  standalone: false,
})
export class ModalPlaceholderComponent {
  @Input() title = 'Modal foundation';
  @Input() description = 'Modal workflow contracts are prepared for create/edit flows.';
}

