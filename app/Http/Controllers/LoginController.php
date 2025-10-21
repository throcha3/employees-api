<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 *
 * APIs for auth
 */
class LoginController extends Controller
{
    /**
     * Handle user login
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->string('email'))->first();

        if (!$user || !Hash::check($request->string('password'), (string) $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $token = $user->createToken((string) ($request->string('device_name') ?: 'api'));

        return response()->json([
            'access_token' => $token->accessToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get authenticated user information
     */
    public function me(Request $request)
    {
        return $request->user();
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        $request->user()->token()?->revoke();
        return response()->json(['message' => 'Logged out']);
    }
}
