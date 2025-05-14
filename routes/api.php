<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\ChatController;
use App\Http\Controllers\Notification\NotificationController;

Route::post('login', [AuthController::class, 'login']);
Route::post('registration', [RegisterController::class, 'registration']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('/user/orders', [OrderController::class, 'userOrders']); // Получение всех заказов всех пользователей
});

Route::middleware('auth:api')->group(function () {
    Route::middleware('admin')->group(function () {
        Route::get('/admin/orders', [OrderController::class, 'adminOrders']);
        Route::put('/admin/orders/{id}', [OrderController::class, 'updateOrder']);
        Route::delete('/admin/orders/{id}', [OrderController::class, 'deleteOrder']);
    });
});
Route::middleware(['auth:api'])->group(function () {
    // Маршрут для получения чатов
        Route::get('/user/order/chat/{orderId}', [ChatController::class, 'show'])->name('user.order.chat.show');

    // Маршрут для создания нового чата
    Route::post('/user/order/chat', [ChatController::class, 'store'])->name('user.order.chat.store');

    // Маршрут для отправки сообщений в чат
    Route::post('/user/order/chat/{orderId}/message', [ChatController::class, 'sendMessage'])->name('user.order.chat.sendMessage');
});

Route::get('user/profile/{userId}/profile', [ProfileController::class, 'viewProfile']);
    Route::middleware(['auth:api'])->group(function () {
        Route::prefix('user/profile')->group(function () {
            Route::get('/', [ProfileController::class, 'profile']);
            Route::put('/', [ProfileController::class, 'updateProfile']); // Информация о профиле
            Route::get('/orders', [ProfileController::class, 'index']); // Заказы пользователя
            Route::post('/orders', [ProfileController::class, 'store']); // Создание заказа
            Route::get('/orders/{id}', [ProfileController::class, 'show']);
            Route::get('/orders/{id}', [OrderController::class, 'showPublic']); // Просмотр одного заказа
            Route::put('/orders/{id}', [ProfileController::class, 'update']); // Обновление заказа
            Route::delete('/orders/{id}', [ProfileController::class, 'destroy']); // Удаление заказа
            Route::get('/user/profile/download-file/{file}', [ProfileController::class, 'downloadFile']);
            Route::post('/orders/{id}/add-to-cart', [OrderController::class, 'addToCart']);
            Route::get('/cart', [OrderController::class, 'getCartOrders']);
            Route::put('/orders/{id}/close', [OrderController::class, 'closeOrder']); // Закрыть заказ

            // Запросы на заказы
            Route::post('/orders/{id}/send-request', [OrderController::class, 'sendRequest']); // Отправить запрос на заказ
            Route::post('/orders/{orderId}/approve-request/{requestId}', [OrderController::class, 'approveRequest']); // Принять запрос на
            // Новый маршрут для получения количества откликов на заказ
            Route::get('/orders/{id}/view-count', [OrderController::class, 'getOrderViewCount']);
        });
    });
Route::middleware('auth:api')->get('/notifications', [NotificationController::class, 'getNotifications']);
Route::middleware('auth:api')->get('/user/order/my-requests', [OrderController::class, 'myRequests']);


Route::middleware('auth:api')->get('/orders/{id}', [\App\Http\Controllers\Order\PublicOrderController::class, 'show']);
