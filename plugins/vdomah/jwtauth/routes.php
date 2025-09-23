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

    Route::post('login', function (Request $request) {
        if (Settings::get('is_login_disabled')) {
            App::abort(404, 'Page not found');
        }

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
            'token' => $token,
        ]);
    });

    Route::post('signup', function (Request $request) {
        if (Settings::get('is_signup_disabled')) {
            App::abort(404, 'Page not found');
        }

        $signup_fields = Settings::get('signup_fields', ['email', 'password', 'password_confirmation']);
        $credentials = $request->only($signup_fields);

        try {
            $userModel = UserModel::create($credentials);

            $user = [
                'id' => $userModel->id,
                'first_name' => $userModel->first_name,
                'last_name' => $userModel->last_name,
                'username' => $userModel->username,
                'email' => $userModel->email,
                'is_activated' => $userModel->is_activated,
            ];

        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 401);
        }

        $token = JWTAuth::fromUser($userModel);

        return Response::json([
            'user' => $user,
            'token' => $token
        ]);
    });

    Route::post('logout', function (Request $request) {
        try {
            $token = $request->header('Authorization');
            if ($token) {
                $token = str_replace('Bearer ', '', $token);
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

    Route::post('refresh', function (Request $request) {
        $token = $request->header('Authorization');
        if (!$token)
            return response()->json(['error' => 'Token not provided'], 400);

        $token = str_replace('Bearer ', '', $token);

        try {
            $token = JWTAuth::refresh($token);
        } catch (Exception $e) {
            return response()->json(['error' => 'could_not_refresh_token'], 500);
        }

        return response()->json(['token' => $token]);
    });

    Route::post('check-token', function (Request $request) {
        $token = $request->header('Authorization');
        if (!$token)
            return response()->json(['message' => 'Token not provided'], 401);

        $token = str_replace('Bearer ', '', $token);

        try {
            $user = JWTAuth::setToken($token)->toUser();
            return response()->json(['message' => 'Token is valid', 'user' => $user]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }
    });

    Route::post('invalidate', function (Request $request) {
        $token = $request->header('Authorization');
        if (!$token)
            return response()->json(['message' => 'Token not provided'], 401);

        $token = str_replace('Bearer ', '', $token);

        try {
            JWTAuth::invalidate($token);
            return response()->json(['message' => 'token_invalidated']);
        } catch (Exception $e) {
            return response()->json(['error' => 'could_not_invalidate_token'], 500);
        }
    });

});
