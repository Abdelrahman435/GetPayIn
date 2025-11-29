<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PaymentWebhookController;

Route::prefix('payments')->group(function () {
    Route::post('/', [PaymentWebhookController::class, 'store']);
    Route::get('/{payment}', [PaymentWebhookController::class, 'show']);
    Route::get('/', [PaymentWebhookController::class, 'index']);
    Route::post('/webhook', [PaymentWebhookController::class, 'handle']);
    });
