import { Component } from '@angular/core';
import { AuthStateService } from '../../../../core/services/auth-state.service';
import { LocaleService } from '../../../../i18n/services/locale.service';

@Component({
  selector: 'app-profile-home',
  template: `
    <section class="profile-page">
      <ui-card>
        <h2>{{ labels.profile }}</h2>
        <p>{{ labels.subtitle }}</p>
      </ui-card>

      <ui-card>
        <h3>{{ labels.accountSummary }}</h3>
        <p><strong>{{ labels.name }}:</strong> {{ (authState.user$ | async)?.name || labels.empty }}</p>
        <p><strong>{{ labels.email }}:</strong> {{ (authState.user$ | async)?.email || labels.empty }}</p>
        <p><strong>{{ labels.roles }}:</strong> {{ ((authState.roles$ | async) || []).join(', ') || labels.empty }}</p>
        <p><strong>{{ labels.permissions }}:</strong> {{ ((authState.permissions$ | async) || []).length }}</p>
      </ui-card>
    </section>
  `,
  styles: [`
    .profile-page{display:grid;gap:12px}
    h2{margin:0 0 6px;color:#f8fafc}
    h3{margin:0 0 8px;color:#f8fafc}
    p{margin:0;color:#94a3b8}
  `],
  standalone: false,
})
export class ProfileHomeComponent {
  constructor(
    public readonly authState: AuthStateService,
    private readonly localeService: LocaleService,
  ) {}

  get labels() {
    const locale = this.localeService.currentLocale;
    if (locale === 'uk') {
      return {
        profile: 'Профіль',
        subtitle: 'Базовий контур профілю підготовлено для майбутніх налаштувань акаунта.',
        accountSummary: 'Підсумок акаунта',
        name: "Ім'я",
        email: 'Email',
        roles: 'Ролі',
        permissions: 'Дозволи',
        empty: '—',
      };
    }

    if (locale === 'de') {
      return {
        profile: 'Profil',
        subtitle: 'Profilgrundlage ist für zukünftige Kontoeinstellungen vorbereitet.',
        accountSummary: 'Kontozusammenfassung',
        name: 'Name',
        email: 'E-Mail',
        roles: 'Rollen',
        permissions: 'Berechtigungen',
        empty: '—',
      };
    }

    return {
      profile: 'Profile',
      subtitle: 'Profile foundation is ready for future account settings workflows.',
      accountSummary: 'Account summary',
      name: 'Name',
      email: 'Email',
      roles: 'Roles',
      permissions: 'Permissions',
      empty: '—',
    };
  }
}
