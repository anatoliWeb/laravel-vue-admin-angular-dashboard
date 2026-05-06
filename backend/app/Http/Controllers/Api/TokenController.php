<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * API Token Controller
 *
 * Handles personal access token lifecycle for authenticated users.
 *
 * Responsibilities:
 * - List user tokens
 * - Create new token (one-time secret exposure)
 * - Delete token with strict ownership validation
 */
class TokenController extends Controller
{
    /**
     * List all tokens for the authenticated user.
     *
     * WHY:
     * Tokens are always scoped to the authenticated user
     * to prevent accidental data leakage across accounts.
     */
    public function index(Request $request)
    {
        $owner = $request->user();

        $tokens = $owner
            ->tokens()
            ->select(['id', 'name', 'created_at']) // WHY: limit fields for performance & security
            ->orderByDesc('id') // WHY: newest tokens first (better UX)
            ->get()
            ->map(function ($token) use ($owner) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'created_at' => $token->created_at,

                    // WHY:
                    // Including owner info avoids extra API calls on frontend
                    'owner' => [
                        'id' => $owner->id,
                        'name' => $owner->name,
                    ],
                ];
            })
            ->values(); // WHY: ensure clean indexed array for JSON response

        return response()->json([
            'data' => $tokens,
        ]);
    }

    /**
     * Create a new personal access token.
     *
     * WHY:
     * Token secret is returned ONLY once and never stored in plain text.
     * This follows standard API token security practices.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->user()->createToken($validated['name']);

        return response()->json([
            'data' => [
                'id' => $token->accessToken->id,
                'name' => $token->accessToken->name,
                'created_at' => $token->accessToken->created_at,

                // WHY:
                // Echo owner info for UI consistency (same shape as index response)
                'owner' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                ],
            ],

            // WHY:
            // Plain text token is exposed ONLY here.
            // It must be stored by client immediately.
            'token' => $token->plainTextToken,

        ], 201);
    }

    /**
     * Delete a token by ID.
     *
     * WHY:
     * Ownership check is mandatory to prevent deleting чужих токенів.
     * Sanctum tokens are global, so we must manually enforce scope.
     */
    public function destroy(Request $request, int $id)
    {
        $token = PersonalAccessToken::findOrFail($id);

        // WHY:
        // Ensure token belongs to the current user AND correct model type
        // to avoid cross-user or polymorphic misuse.
        if (
            (int) $token->tokenable_id !== (int) $request->user()->id ||
            $token->tokenable_type !== get_class($request->user())
        ) {
            abort(403);
        }

        $token->delete();

        return response()->json([
            'message' => 'Token deleted successfully',
        ]);
    }
}
