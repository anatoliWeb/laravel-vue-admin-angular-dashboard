<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
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
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'token' => $token
            ]);

        } catch (\Throwable $e) {

            Log::error('Token generation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Internal Server Error'
            ], 500);
        }
    }
}