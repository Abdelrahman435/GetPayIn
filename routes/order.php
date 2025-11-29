<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OrderController;


Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{order}', [OrderController::class, 'show']);
    Route::get('/', [OrderController::class, 'index']);
});
