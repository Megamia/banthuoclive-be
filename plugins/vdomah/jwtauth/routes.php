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
    Route::get("testApi", function () {
        return response()->json(['message' => 'API login test OK']);
    });
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

        if ($userModel->methodExists('getAuthApiSigninAttributes')) {
            $user = $userModel->getAuthApiSigninAttributes();
            \Log::info('User login:', ['user' => $user]);

        } else {
            $user = [
                'id' => $userModel->id,
                'first_name' => $userModel->first_name,
                'last_name' => $userModel->last_name,
                'email' => $userModel->email,
                'additional_user' => $userModel->additional_user,
            ];
        }

        //LOCAL
        // $cookie = cookie(
        //     name: 'token',
        //     value: $token,
        //     minutes: 1440,
        //     path: '/',
        //     domain: 'localhost',
        //     sameSite: 'Lax',
        //     secure: false,
        //     httpOnly: true,
        // );
        //LOCAL

        //DEPLOY
        $secure = true;
        $cookie = cookie(
            name: 'token',
            value: $token,
            minutes: 1440,
            path: '/',
            domain: 'banthuoclive-fe.vercel.app',
            sameSite: 'None',
            secure: $secure,
            httpOnly: true,
        );
        //DEPLOY

        // if no errors are encountered we can return a JWT
        return response()->json(compact('user'))->cookie(cookie: $cookie);
    });

    Route::post('refresh', function (Request $request) {
        if (Settings::get('is_refresh_disabled'))
            App::abort(404, 'Page not found');

        $token = Request::get('token');

        try {
            // attempt to refresh the JWT
            if (!$token = JWTAuth::refresh($token)) {
                return response()->json(['error' => 'could_not_refresh_token'], 401);
            }
        } catch (Exception $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_refresh_token'], 500);
        }

        // if no errors are encountered we can return a new JWT
        return response()->json(compact('token'));
    });

    Route::post('invalidate', function (Request $request) {
        if (Settings::get('is_invalidate_disabled'))
            App::abort(404, 'Page not found');

        $token = Request::get('token');

        try {
            // invalidate the token
            JWTAuth::invalidate($token);
        } catch (Exception $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_invalidate_token'], 500);
        }

        // if no errors we can return a message to indicate that the token was invalidated
        return response()->json('token_invalidated');
    });

    Route::post('logout', function (Request $request) {
        $cookie = cookie(
            name: 'token',
            value: '',
            minutes: -1,
            path: '/',
            sameSite: 'None',
            secure: true,
            httpOnly: true,
        );
        return response()->json(['message' => 'logged_out'])->cookie($cookie);
    });

    Route::post('signup', function (Request $request) {
        if (Settings::get('is_signup_disabled'))
            App::abort(404, 'Page not found');

        $login_fields = Settings::get('signup_fields', ['email', 'password', 'password_confirmation']);
        $credentials = $request->only($login_fields);

        try {
            $userModel = UserModel::create($credentials);

            if ($userModel->methodExists('getAuthApiSignupAttributes')) {
                $user = $userModel->getAuthApiSignupAttributes();
            } else {
                $user = [
                    'id' => $userModel->id,
                    'first_name' => $userModel->first_name,
                    'surname' => $userModel->surname,
                    'username' => $userModel->username,
                    'email' => $userModel->email,
                    'is_activated' => $userModel->is_activated,
                ];
            }
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 401);
        }

        $token = JWTAuth::fromUser($userModel);

        return Response::json(compact('token', 'user'));
    });

    Route::post('check-token', function (Request $request) {
        Log::info('Check token endpoint hit');
        $token = Request::cookie('token');

        if (!$token) {
            Log::error('Token not found in cookie');
            return response()->json(['message' => 'Token not found'], 401);
        }

        try {
            $user = JWTAuth::setToken($token)->toUser();
            return response()->json(['message' => 'Token is valid', 'user' => $user], 200);
        } catch (Exception $e) {
            Log::error('Error validating token: ' . $e->getMessage());
            return response()->json(['message' => 'Token is invalid or expired'], 500);
        }
    });

});