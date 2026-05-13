import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

export interface RealtimeStatus {
  connected: boolean;
  provider: string;
}

@Injectable({ providedIn: 'root' })
export class RealtimeService {
  private readonly statusSubject = new BehaviorSubject<RealtimeStatus>({
    connected: false,
    provider: 'reverb',
  });

  readonly status$ = this.statusSubject.asObservable();

  connect(): void {
    // Phase 4 foundation: connection lifecycle API exists, transport wiring later.
    this.statusSubject.next({ connected: false, provider: this.statusSubject.value.provider });
  }

  disconnect(): void {
    this.statusSubject.next({ connected: false, provider: this.statusSubject.value.provider });
  }
}

