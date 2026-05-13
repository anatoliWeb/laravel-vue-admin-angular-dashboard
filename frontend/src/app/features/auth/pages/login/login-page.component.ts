import { Component } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthSessionService } from '../../../../auth/services/auth-session.service';
import { AuthStateService } from '../../../../core/services/auth-state.service';
import type { SessionAuthPayload } from '../../../../auth/models/session-auth.model';

@Component({
  selector: 'app-login-page',
  templateUrl: './login-page.component.html',
  styleUrls: ['./login-page.component.scss'],
  standalone: false,
})
export class LoginPageComponent {
  isLoading = false;
  errorMessage = '';
  readonly form;

  constructor(
    private readonly fb: FormBuilder,
    private readonly router: Router,
    private readonly authSession: AuthSessionService,
    private readonly authState: AuthStateService,
  ) {
    this.form = this.fb.nonNullable.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required]],
      remember: [false],
    });
  }

  submit(): void {
    if (this.form.invalid || this.isLoading) {
      this.form.markAllAsTouched();
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';

    const value = this.form.getRawValue();
    this.authSession
      .login({
        email: value.email,
        password: value.password,
        remember: value.remember,
      })
      .subscribe({
        next: (payload: SessionAuthPayload) => {
          this.authState.setSession(payload);
          void this.router.navigateByUrl('/dashboard');
        },
        error: () => {
          this.errorMessage = 'Unable to sign in. Please check your credentials.';
          this.isLoading = false;
        },
        complete: () => {
          this.isLoading = false;
        },
      });
  }
}
