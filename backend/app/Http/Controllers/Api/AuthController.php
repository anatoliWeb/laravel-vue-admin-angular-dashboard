<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    /**
     * Build effective permission set for API auth payloads.
     *
     * WHY:
     * Angular token clients and Vue session clients must receive the same
     * resolved permission contract (roles + direct permissions - denies).
     */
    protected function resolveEffectivePermissions(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $user->loadMissing(['roles.permissions', 'permissions', 'deniedPermissions']);

        $rolePermissions = $user->roles->flatMap(fn ($role) => $role->permissions);
        $directPermissions = $user->permissions;
        $denied = $user->deniedPermissions ?? collect();

        return $rolePermissions
            ->merge($directPermissions)
            ->unique('id')
            ->reject(fn ($permission) => $denied->contains('id', $permission->id))
            ->pluck('name')
            ->values()
            ->all();
    }

    /**
     * Issue API token for user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function token(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->errorResponse('Invalid credentials', null, 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Invalid credentials', null, 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'user' => $this->toSessionUser($user),
                'permissions' => $this->resolveEffectivePermissions($user),
                'roles' => $user->roles->pluck('name')->values()->all(),
            ], dt('notifications.success'));

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

        $remember = (bool) ($credentials['remember'] ?? false);
        $attemptCredentials = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ];

        if (!Auth::guard('web')->attempt($attemptCredentials, $remember)) {
            return $this->errorResponse(dt('notifications.error'), [
                'email' => [__('auth.failed')],
            ], 422);
        }

        $request->session()->regenerate();
        $user = $request->user();

        return $this->successResponse([
            'user' => $this->toSessionUser($user),
            'permissions' => $this->resolveEffectivePermissions($user),
            'roles' => $user ? $user->roles->pluck('name')->values()->all() : [],
        ], dt('notifications.success'));
    }

    /**
     * Return authenticated session context.
     */
    public function sessionUser(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'user' => $this->toSessionUser($user),
            'permissions' => $this->resolveEffectivePermissions($user),
            'roles' => $user ? $user->roles->pluck('name')->values()->all() : [],
        ], dt('notifications.success'));
    }

    /**
     * Destroy session for SPA logout.
     */
    public function sessionLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->successResponse([], dt('notifications.success'));
    }

    /**
     * Bearer token identity endpoint for API-first clients (Angular/mobile).
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return $this->successResponse([
            'user' => $this->toSessionUser($user),
            'permissions' => $this->resolveEffectivePermissions($user),
            'roles' => $user ? $user->roles->pluck('name')->values()->all() : [],
        ], dt('notifications.success'));
    }

    /**
     * Revoke current bearer token without touching web session flow.
     */
    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->successResponse([], dt('notifications.success'));
    }

    protected function toSessionUser(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
