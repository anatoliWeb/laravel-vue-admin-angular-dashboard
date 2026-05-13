import { HttpInterceptorFn } from '@angular/common/http';

/**
 * Session interceptor foundation.
 *
 * WHY:
 * Angular dashboard relies on Laravel cookie session auth, so requests must
 * always include credentials.
 */
export const authSessionInterceptor: HttpInterceptorFn = (request, next) => {
  return next(request.clone({ withCredentials: true }));
};

