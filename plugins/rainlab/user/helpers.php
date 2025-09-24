<?php

// use Tymon\JWTAuth\Facades\JWTAuth;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Http\Request;

// function checkToken(Request $request)
// {
//     Log::info('Check token function hit');

//     $token = $request->bearerToken();

//     if (!$token) {
//         Log::error('Token not found in header');
//         return response()->json(['message' => 'Token not provided'], 401);
//     }

//     try {
//         return JWTAuth::setToken($token)->toUser();
//     } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
//         Log::error('Token expired: ' . $e->getMessage());
//         return response()->json(['message' => 'Token expired'], 401);
//     } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
//         Log::error('Token invalid: ' . $e->getMessage());
//         return response()->json(['message' => 'Token invalid'], 401);
//     } catch (\Exception $e) {
//         Log::error('JWT Error: ' . $e->getMessage());
//         return response()->json(['message' => 'JWT error'], 500);
//     }
// }

