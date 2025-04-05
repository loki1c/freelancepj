<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderUpdateRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function update(OrderUpdateRequest $request, Order $order): JsonResponse
    {
        $order->update($request->validated());
        return response()->json(['message' => 'Заказ обновлён', 'order' => $order]);
    }

    public function adminOrders(): JsonResponse
    {
        $orders = Order::all(); // Получаем все заказы
        return response()->json($orders);
    }

    public function updateOrder(OrderUpdateRequest $request, Order $order): JsonResponse
    {
        $order->update($request->only(['title', 'description', 'price', 'status'])); // Обновляем данные заказа
        return response()->json(['message' => 'Заказ обновлен', 'order' => $order]);
    }

    public function deleteOrder(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(['message' => 'Заказ удален']);
    }
}

