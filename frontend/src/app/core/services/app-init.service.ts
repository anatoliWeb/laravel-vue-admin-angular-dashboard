import { Injectable } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { AuthRuntimeService } from '../../auth/services/auth-runtime.service';
import { LocaleService } from '../../i18n/services/locale.service';
import { RuntimeTranslationService } from '../../i18n/services/runtime-translation.service';
import { SettingsPreloadService } from '../../settings/services/settings-preload.service';

@Injectable({ providedIn: 'root' })
export class AppInitService {
  constructor(
    private readonly authRuntime: AuthRuntimeService,
    private readonly localeService: LocaleService,
    private readonly translationService: RuntimeTranslationService,
    private readonly settingsPreload: SettingsPreloadService,
  ) {}

  async initialize(): Promise<void> {
    const locale = this.localeService.currentLocale;

    await Promise.all([
      firstValueFrom(this.translationService.preload(locale)),
      firstValueFrom(this.settingsPreload.preload()),
      this.authRuntime.hydrateAuth(),
    ]);
  }
}
