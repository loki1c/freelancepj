<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\ChatController;

Route::post('login', [AuthController::class, 'login']);
Route::post('registration', [RegisterController::class, 'registration']);

Route::middleware(['auth'])->group(function () {
    Route::get('/user/orders', [OrderController::class, 'userOrders']);
});

Route::middleware('auth:api')->group(function () {
    Route::middleware('admin')->group(function () {
        Route::get('/admin/orders', [OrderController::class, 'adminOrders']);
        Route::put('/admin/orders/{id}', [OrderController::class, 'updateOrder']);
        Route::delete('/admin/orders/{id}', [OrderController::class, 'deleteOrder']);
    });
});
Route::middleware(['auth:api'])->group(function () {
    Route::get('/user/orders', [OrderController::class, 'userOrders']);

    Route::prefix('user/order/chat')->group(function () {
        Route::get('{id}', [ChatController::class, 'show'])->name('user.order.chat.show');
        Route::post('/', [ChatController::class, 'store'])->name('user.order.chat.store');
    });
});

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('user/profile')->group(function () {
        Route::get('/', [ProfileController::class, 'profile']); // Информация о профиле
        Route::get('/orders', [ProfileController::class, 'index']); // Заказы пользователя
        Route::post('/orders', [ProfileController::class, 'store']); // Создание заказа
        Route::get('/orders/{id}', [ProfileController::class, 'show']); // Просмотр одного заказа
        Route::put('/orders/{id}', [ProfileController::class, 'update']); // Обновление заказа
        Route::delete('/orders/{id}', [ProfileController::class, 'destroy']); // Удаление заказа
    });
});
