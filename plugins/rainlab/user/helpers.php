<?php

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

function checkToken(Request $request)
{
    Log::info('Check token function hit');

    $token = $request->cookie('token');

    if (!$token) {
        Log::error('Token not found in cookie');
        return response()->json(['message' => 'Token not found'], 205);
    }

    try {
        return JWTAuth::setToken($token)->toUser();
    } catch (Exception $e) {
        Log::error('Error validating token: ' . $e->getMessage());
        $cookie = cookie(
            name: 'token',
            value: '',
            minutes: -1,
            path: '/',
            sameSite: 'None',
            secure: true,
            httpOnly: true,
        );
        return response()->json(['message' => 'Token is invalid or expired'], 204)->cookie($cookie);
    }
}
