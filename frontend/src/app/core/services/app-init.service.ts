import { Injectable } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { APP_CONFIG, AppEnvironment } from '../tokens/app-config.token';
import { Inject } from '@angular/core';
import { AuthRuntimeService } from '../../auth/services/auth-runtime.service';
import { LocaleService } from '../../i18n/services/locale.service';
import { RuntimeTranslationService } from '../../i18n/services/runtime-translation.service';
import { SettingsPreloadService } from '../../settings/services/settings-preload.service';
import { AppLoadingService } from './app-loading.service';

@Injectable({ providedIn: 'root' })
export class AppInitService {
  private initialized = false;
  private initPromise: Promise<void> | null = null;

  constructor(
    @Inject(APP_CONFIG) private readonly config: AppEnvironment,
    private readonly authRuntime: AuthRuntimeService,
    private readonly localeService: LocaleService,
    private readonly translationService: RuntimeTranslationService,
    private readonly settingsPreload: SettingsPreloadService,
    private readonly appLoading: AppLoadingService,
  ) {}

  async initialize(): Promise<void> {
    if (this.initialized) {
      return;
    }
    if (this.initPromise) {
      return this.initPromise;
    }

    const locale = this.localeService.currentLocale;
    this.appLoading.show('common.bootstrap.initializing', 'bootstrap');
    if (!this.config.production) {
      console.debug('[Bootstrap] initialize start', { locale });
    }
    this.initPromise = Promise.all([
      firstValueFrom(this.translationService.preload(locale)),
      firstValueFrom(this.settingsPreload.preload()),
      this.authRuntime.hydrateAuth(),
    ]).then(() => {
      this.initialized = true;
      if (!this.config.production) {
        console.debug('[Bootstrap] initialize done');
      }
    }).finally(() => {
      this.initPromise = null;
      this.appLoading.hide();
    });

    return this.initPromise;
  }
}
