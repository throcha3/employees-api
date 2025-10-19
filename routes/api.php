<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
        'device_name' => ['nullable', 'string'],
    ]);

    $user = User::where('email', $request->string('email'))->first();

    if (!$user || !Hash::check($request->string('password'), (string) $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 422);
    }

    $token = $user->createToken((string) ($request->string('device_name') ?: 'api'));

    return response()->json([
        'access_token' => $token->accessToken,
        'token_type' => 'Bearer',
    ]);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', function (Request $request) {
        $request->user()->token()?->revoke();
        return response()->json(['message' => 'Logged out']);
    });
});


