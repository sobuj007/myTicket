<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::get('test', [AuthController::class, 'test']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('verify-forget-otp', [AuthController::class, 'verifyForgetOtp']);
    Route::post('login', [AuthController::class, 'login']);

    Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'singelDeviceLogout']);
    });
});
