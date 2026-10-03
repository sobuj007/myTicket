<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DeviceSecurityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    Route::post('/security/device-info', [DeviceSecurityController::class, 'registerDevices']);


    Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'singelDeviceLogout']);
    });

    Route::prefix('device')->group(function () {
        Route::post('/devices/', [DeviceSecurityController::class, 'getDevices']);
        // Route::middleware('device.security')->group(function () {
        //     Route::post('devices/{id}/trust', [DeviceSecurityController::class, 'getTrustedDevice']);
        //     Route::post('devices/{id}/block', [DeviceSecurityController::class, 'blockedDevice']);
        //     Route::delete('devices/{id}', [DeviceSecurityController::class, 'removeDevice']);
        // });
        Route::middleware(['auth:sanctum', 'device.security'])->group(function () {

            Route::post('/security/my-device', function (Request $request) {

                // $device = $request->attributes->get('user_device');

                return response()->json([
                    'success' => true,
                    'device' => "device",
                ]);
            });
        });
    });
});
