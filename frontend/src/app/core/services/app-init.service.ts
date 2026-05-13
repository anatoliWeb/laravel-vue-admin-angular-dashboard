import { Injectable } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { AuthSessionService } from '../../auth/services/auth-session.service';
import { LocaleService } from '../../i18n/services/locale.service';
import { RuntimeTranslationService } from '../../i18n/services/runtime-translation.service';
import { SettingsPreloadService } from '../../settings/services/settings-preload.service';
import { AuthStateService } from './auth-state.service';

@Injectable({ providedIn: 'root' })
export class AppInitService {
  constructor(
    private readonly authSession: AuthSessionService,
    private readonly authState: AuthStateService,
    private readonly localeService: LocaleService,
    private readonly translationService: RuntimeTranslationService,
    private readonly settingsPreload: SettingsPreloadService,
  ) {}

  async initialize(): Promise<void> {
    const locale = this.localeService.currentLocale;

    await Promise.all([
      firstValueFrom(this.translationService.preload(locale)),
      firstValueFrom(this.settingsPreload.preload()),
      this.hydrateSession(),
    ]);
  }

  private async hydrateSession(): Promise<void> {
    try {
      const payload = await firstValueFrom(this.authSession.getSession());
      this.authState.setSession(payload);
    } catch {
      this.authState.clearSession();
    }
  }
}

