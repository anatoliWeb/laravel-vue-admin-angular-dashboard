<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
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
                return $this->errorResponse('Invalid credentials', null, 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Invalid credentials', null, 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
            ], 'Token issued');

        } catch (\Throwable $e) {

            Log::error('Token generation failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Internal Server Error', null, 500);
        }
    }
}
