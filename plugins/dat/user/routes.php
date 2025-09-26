<?php

Route::group(['prefix' => 'apiUser'], function () {
    Route::post('login', [\Dat\User\Controllers\UserController::class, 'login']);
    Route::post('signup', [\Dat\User\Controllers\UserController::class, 'signup']);
    Route::get('profile', [\Dat\User\Controllers\UserController::class, 'profile']);
    Route::post('change-info', [\Dat\User\Controllers\UserController::class, 'change_infor']);
    Route::post('change-password', [\Dat\User\Controllers\UserController::class, 'change_password']);
    Route::post('logout', [\Dat\User\Controllers\UserController::class, 'logout']);
    Route::get('test', [\Dat\User\Controllers\UserController::class, 'test']);
});