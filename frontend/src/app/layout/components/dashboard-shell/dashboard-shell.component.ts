import { Component } from '@angular/core';
import { AuthStateService } from '../../../core/services/auth-state.service';

@Component({
  selector: 'app-dashboard-shell',
  templateUrl: './dashboard-shell.component.html',
  styleUrls: ['./dashboard-shell.component.scss'],
  standalone: false,
})
export class DashboardShellComponent {
  constructor(public readonly authState: AuthStateService) {}
}

