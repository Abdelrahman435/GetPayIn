<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HoldController;


Route::prefix('holds')->group(function () {
    Route::post('/', [HoldController::class, 'store']);
    Route::get('/{hold}', [HoldController::class, 'show']);
    Route::get('/', [HoldController::class, 'index']);
});
