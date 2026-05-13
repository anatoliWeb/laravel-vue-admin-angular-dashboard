import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-table-placeholder',
  template: `
    <div class="table-placeholder">
      <p>{{ title }}</p>
      <small>{{ subtitle }}</small>
    </div>
  `,
  styles: [`
    .table-placeholder{border:1px dashed rgba(100,116,139,.45);border-radius:12px;padding:14px;color:#94a3b8}
    .table-placeholder p{margin:0 0 4px;color:#e2e8f0}
  `],
  standalone: false,
})
export class TablePlaceholderComponent {
  @Input() title = 'Table foundation';
  @Input() subtitle = 'Reusable datagrid surface will be implemented in feature phases.';
}

