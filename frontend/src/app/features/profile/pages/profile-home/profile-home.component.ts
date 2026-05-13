import { Component } from '@angular/core';

@Component({
  selector: 'app-profile-home',
  template: `
    <ui-card>
      <h2>Profile</h2>
      <p>Profile feature module foundation is ready for account center workflows.</p>
    </ui-card>
  `,
  styles: [`
    h2{margin:0 0 6px;color:#f8fafc}
    p{margin:0;color:#94a3b8}
  `],
  standalone: false,
})
export class ProfileHomeComponent {}

