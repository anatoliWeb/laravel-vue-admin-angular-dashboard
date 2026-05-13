import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import type { AuthUser } from '../models/auth-user.model';
import type { SessionAuthPayload } from '../../auth/models/session-auth.model';

@Injectable({ providedIn: 'root' })
export class AuthStateService {
  private readonly userSubject = new BehaviorSubject<AuthUser | null>(null);
  private readonly permissionsSubject = new BehaviorSubject<string[]>([]);
  private readonly hydratedSubject = new BehaviorSubject<boolean>(false);

  readonly user$ = this.userSubject.asObservable();
  readonly permissions$ = this.permissionsSubject.asObservable();
  readonly hydrated$ = this.hydratedSubject.asObservable();

  get isAuthenticated(): boolean {
    return this.userSubject.value !== null;
  }

  setSession(payload: SessionAuthPayload): void {
    this.userSubject.next(payload.user);
    this.permissionsSubject.next(payload.permissions);
    this.hydratedSubject.next(true);
  }

  clearSession(): void {
    this.userSubject.next(null);
    this.permissionsSubject.next([]);
    this.hydratedSubject.next(true);
  }

  hasPermission(permission: string): boolean {
    return this.permissionsSubject.value.includes(permission);
  }
}

