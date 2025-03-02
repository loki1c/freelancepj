<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Order\OrderController;

Route::post('login', [AuthController::class, 'login']);
Route::post('registration', [RegisterController::class, 'registration']);

Route::middleware(['auth'])->group(function () {
    Route::get('/user/orders', [OrderController::class, 'userOrders']);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/admin/orders', [OrderController::class, 'adminOrders']);
    Route::put('/admin/orders/{id}', [OrderController::class, 'updateOrder']);
    Route::delete('/admin/orders/{id}', [OrderController::class, 'deleteOrder']);
});
