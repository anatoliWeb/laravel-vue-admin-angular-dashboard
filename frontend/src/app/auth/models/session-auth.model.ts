import type { AuthUser } from '../../core/models/auth-user.model';

export interface SessionAuthPayload {
  user: AuthUser | null;
  permissions: string[];
}

