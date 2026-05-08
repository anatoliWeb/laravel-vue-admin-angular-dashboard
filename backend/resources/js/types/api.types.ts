/**
 * Shared API contract types for frontend layer.
 *
 * Keeping response types centralized ensures consistent integration
 * across modules and reduces ad-hoc typing inside components.
 */
export interface ApiSuccess<TData = unknown> {
  success: true;
  message: string;
  data: TData;
  meta?: Record<string, unknown>;
}

export interface ApiError<TError = unknown> {
  success: false;
  message: string;
  errors: TError;
  meta?: Record<string, unknown>;
}

export type ApiResponse<TData = unknown, TError = unknown> = ApiSuccess<TData> | ApiError<TError>;

