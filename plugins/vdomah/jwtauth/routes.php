<?php

use RainLab\User\Models\User as UserModel;
use Vdomah\JWTAuth\Models\Settings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

Route::group(['prefix' => 'api'], function () {

    // ===== LOGIN =====
    Route::post('login', function (Request $request) {
        if (Settings::get('is_login_disabled'))
            App::abort(404, 'Page not found');

        $login_fields = Settings::get('login_fields', ['email', 'password']);
        $credentials = $request->only($login_fields);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Sai tài khoản hoặc mật khẩu'], 205);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $userModel = JWTAuth::authenticate($token);

        $user = [
            'id' => $userModel->id,
            'first_name' => $userModel->first_name,
            'last_name' => $userModel->last_name,
            'email' => $userModel->email,
            'username' => $userModel->username,
            'additional_user' => $userModel->additional_user,
            'is_activated' => $userModel->is_activated ?? false,
        ];

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    });

    // ===== LOGOUT =====
    Route::post('logout', function (Request $request) {
        try {
            $token = $request->bearerToken(); 
            if ($token) {
                JWTAuth::invalidate($token);
            }
            return response()->json(['message' => 'logged_out']);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Logout failed',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // ===== CHECK TOKEN =====
    Route::post('check-token', function (Request $request) {
        $token = $request->bearerToken();
        if (!$token)
            return response()->json(['message' => 'Token not found'], 401);

        try {
            $user = JWTAuth::setToken($token)->toUser();
            return response()->json(['message' => 'Token is valid', 'user' => $user], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }
    });

    // ===== REFRESH =====
    Route::post('refresh', function (Request $request) {
        $token = $request->bearerToken();
        if (!$token)
            return response()->json(['message' => 'Token not found'], 401);

        try {
            $newToken = JWTAuth::refresh($token);
            return response()->json(['token' => $newToken]);
        } catch (Exception $e) {
            return response()->json(['error' => 'could_not_refresh_token'], 500);
        }
    });

    // ===== INVALIDATE =====
    Route::post('invalidate', function (Request $request) {
        $token = $request->bearerToken();
        if (!$token)
            return response()->json(['message' => 'Token not found'], 401);

        try {
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'token_invalidated']);
        } catch (Exception $e) {
            return response()->json(['error' => 'could_not_invalidate_token'], 500);
        }
    });

    // ===== SIGNUP =====
    Route::post('signup', function (Request $request) {
        if (Settings::get('is_signup_disabled'))
            App::abort(404, 'Page not found');

        $signup_fields = Settings::get('signup_fields', ['email', 'password', 'password_confirmation']);
        $credentials = $request->only($signup_fields);

        try {
            $userModel = UserModel::create($credentials);
            $token = JWTAuth::fromUser($userModel);

            $user = [
                'id' => $userModel->id,
                'first_name' => $userModel->first_name,
                'last_name' => $userModel->last_name,
                'username' => $userModel->username,
                'email' => $userModel->email,
                'is_activated' => $userModel->is_activated,
            ];

            return response()->json(compact('user', 'token'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    });
});
