<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderUpdateRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Получение всех заказов всех пользователей
    public function userOrders(): JsonResponse
    {
        $orders = Order::all(); // Получаем все заказы
        return response()->json($orders);
    }

    // Обновление заказа
    public function update(OrderUpdateRequest $request, Order $order): JsonResponse
    {
        // Обновляем данные заказа, включая новые поля (category и deadline)
        $order->update($request->validated());

        return response()->json(['message' => 'Заказ обновлён', 'order' => $order]);
    }

    // Обновление данных заказа (с учётом новых полей)
    public function updateOrder(OrderUpdateRequest $request, Order $order): JsonResponse
    {
        // Обновляем только определённые поля, включая category и deadline
        $order->update($request->only(['title', 'description', 'price', 'status', 'category', 'deadline']));

        return response()->json(['message' => 'Заказ обновлён', 'order' => $order]);
    }

    // Удаление заказа
    public function deleteOrder(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(['message' => 'Заказ удалён']);
    }
}
