<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\OrderController;

Route::post('login', [AuthController::class, 'login']);
Route::post('registration', [RegisterController::class, 'registration']);
Route::middleware(['auth:api', 'isAdmin'])->group(function () {
    Route::put('/orders/{order}', [OrderController::class, 'update']);
});
