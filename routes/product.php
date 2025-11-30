<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;

Route::prefix('products')->group(function () {

    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::get('/', [ProductController::class, 'index']);

});
