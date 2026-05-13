import { Component } from '@angular/core';
import { AuthRuntimeService } from '../../../auth/services/auth-runtime.service';
import { AuthStateService } from '../../../core/services/auth-state.service';
import { LocaleService } from '../../../i18n/services/locale.service';
import { RuntimeTranslationService } from '../../../i18n/services/runtime-translation.service';
import { PermissionService } from '../../../rbac/services/permission.service';

@Component({
  selector: 'app-dashboard-shell',
  templateUrl: './dashboard-shell.component.html',
  styleUrls: ['./dashboard-shell.component.scss'],
  standalone: false,
})
export class DashboardShellComponent {
  constructor(
    public readonly authState: AuthStateService,
    public readonly permissionService: PermissionService,
    public readonly localeService: LocaleService,
    private readonly authRuntime: AuthRuntimeService,
    private readonly runtimeTranslation: RuntimeTranslationService,
  ) {}

  async logout(): Promise<void> {
    await this.authRuntime.logout();
  }

  switchLocale(locale: string): void {
    this.localeService.setLocale(locale);
    this.runtimeTranslation.preload(locale).subscribe();
  }

  handleLocaleChange(event: Event): void {
    const locale = (event.target as HTMLSelectElement).value;
    this.switchLocale(locale);
  }
}
