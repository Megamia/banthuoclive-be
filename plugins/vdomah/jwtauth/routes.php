<?php

use Illuminate\Http\Request;
use RainLab\User\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'apiUser'], function () {

    // ===================== LOGIN =====================
    Route::post('login', function (Request $request) {
        $credentials = $request->only(['email', 'password']);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Sai tài khoản hoặc mật khẩu'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Không thể tạo token'], 500);
        }

        $user = JWTAuth::authenticate($token);

        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'email' => $user->email,
            'username' => $user->username,
            'is_activated' => $user->is_activated ?? false,
        ];

        return response()->json([
            'user' => $data,
            'token' => $token
        ]);
    });

    // ===================== SIGNUP =====================
    Route::post('signup', function (Request $request) {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'first_name' => $validated['first_name'] ?? '',
            'last_name' => $validated['last_name'] ?? '',
        ]);

        $token = JWTAuth::fromUser($user);

        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'username' => $user->username,
            'is_activated' => $user->is_activated ?? false,
        ];

        return response()->json([
            'user' => $data,
            'token' => $token
        ]);
    });

    // ===================== PROFILE =====================
    Route::post('profile', function (Request $request) {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json([
                'id' => $user->id,
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'email' => $user->email,
                'username' => $user->username,
                'is_activated' => $user->is_activated ?? false,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    });

    // ===================== LOGOUT =====================
    Route::post('logout', function (Request $request) {
        try {
            $token = $request->bearerToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }

            return response()->json([
                'message' => 'logged_out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Logout failed',
                'message' => $e->getMessage()
            ], 500);
        }
    });

});
