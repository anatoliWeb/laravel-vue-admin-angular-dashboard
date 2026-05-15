<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    /**
     * Issue API token for user.
     */
    public function token(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            return $this->successResponse(
                $this->authService->issueToken($credentials),
                dt('notifications.success')
            );
        } catch (ValidationException $e) {
            return $this->errorResponse('Invalid credentials', null, 401);
        } catch (\Throwable $e) {
            Log::error('Token generation failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Internal Server Error', null, 500);
        }
    }

    /**
     * Session-based login for embedded admin SPA.
     *
     * WHY:
     * Vue admin is mounted inside Laravel and should support first-party
     * cookie/session authentication in addition to API token workflows.
     */
    public function sessionLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        try {
            return $this->successResponse(
                $this->authService->sessionLogin($request, $credentials),
                dt('notifications.success')
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(dt('notifications.error'), [
                'email' => [__('auth.failed')],
            ], 422);
        }
    }

    /**
     * Return authenticated session context.
     */
    public function sessionUser(Request $request)
    {
        return $this->successResponse(
            $this->authService->context($request->user()),
            dt('notifications.success')
        );
    }

    /**
     * Destroy session for SPA logout.
     */
    public function sessionLogout(Request $request)
    {
        $this->authService->sessionLogout($request);

        return $this->successResponse([], dt('notifications.success'));
    }

    /**
     * Bearer token identity endpoint for API-first clients (Angular/mobile).
     */
    public function me(Request $request)
    {
        return $this->successResponse(
            $this->authService->context($request->user()),
            dt('notifications.success')
        );
    }

    /**
     * Revoke current bearer token without touching web session flow.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        $this->authService->logoutToken($user instanceof User ? $user : null);

        return $this->successResponse([], dt('notifications.success'));
    }
}
