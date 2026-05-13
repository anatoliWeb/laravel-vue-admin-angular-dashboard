import { Injectable } from '@angular/core';
import { AuthStateService } from '../../core/services/auth-state.service';

@Injectable({ providedIn: 'root' })
export class PermissionService {
  constructor(private readonly authState: AuthStateService) {}

  can(permission: string): boolean {
    return this.authState.hasPermission(permission);
  }
}

