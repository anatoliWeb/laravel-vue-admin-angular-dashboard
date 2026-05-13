import { Component } from '@angular/core';

@Component({
  selector: 'app-settings-home',
  template: `
    <ui-card>
      <h2>Settings</h2>
      <p>Settings module is wired to preload/runtime architecture and ready for typed forms.</p>
    </ui-card>
  `,
  styles: [`
    h2{margin:0 0 6px;color:#f8fafc}
    p{margin:0;color:#94a3b8}
  `],
  standalone: false,
})
export class SettingsHomeComponent {}

