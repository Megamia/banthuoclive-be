<?php

use Illuminate\Support\Facades\Route;
use Vdomah\JWTAuth\Controllers\AuthController;

Route::group(['prefix' => 'api'], function () {
    Route::get('testabc', [AuthController::class, 'test']);

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('check-token', [AuthController::class, 'checkToken']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('invalidate', [AuthController::class, 'invalidate']);
    Route::post('signup', [AuthController::class, 'signup']);
});