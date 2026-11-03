<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Api\LicenseController;
use Illuminate\Support\Facades\Route;

/*
| Client software (karimgroup) -> https://sla.4axiz.com/api/...
*/
Route::middleware('throttle:30,1')->group(function () {
    Route::post('activate', [LicenseController::class, 'activate']);
    Route::post('status', [LicenseController::class, 'status']);
});

/*
| Admin panel (Next.js) -> https://sla.4axiz.com/api/admin/...
*/
Route::prefix('admin')->group(function () {
    Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [Admin\AuthController::class, 'me']);
        Route::post('logout', [Admin\AuthController::class, 'logout']);

        Route::get('dashboard', Admin\DashboardController::class);

        Route::apiResource('clients', Admin\ClientController::class)->except('destroy');
        Route::apiResource('applications', Admin\ApplicationController::class)->only('index', 'store', 'update');

        Route::post('subscriptions', [Admin\SubscriptionController::class, 'store']);
        Route::patch('subscriptions/{subscription}', [Admin\SubscriptionController::class, 'update']);
        Route::post('subscriptions/{subscription}/reset-install', [Admin\SubscriptionController::class, 'resetInstall']);
        Route::get('subscriptions/{subscription}/suggest-period', [Admin\PaymentController::class, 'suggest']);

        Route::get('payments', [Admin\PaymentController::class, 'index']);
        Route::post('payments', [Admin\PaymentController::class, 'store']);

        Route::get('codes', [Admin\ActivationCodeController::class, 'index']);
        Route::post('codes/{activationCode}/revoke', [Admin\ActivationCodeController::class, 'revoke']);
        Route::get('codes/{activationCode}/offline-key', [Admin\ActivationCodeController::class, 'offlineKey']);
        Route::get('logs', [Admin\ActivationCodeController::class, 'logs']);
    });
});
