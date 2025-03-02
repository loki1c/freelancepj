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
        $user = auth()->user();
        if (!$user || !method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $orders = Order::all(); // Получаем все заказы
        return response()->json($orders);
    }

    public function updateOrder(Request $request, Order $order): JsonResponse
    {
        $user = auth()->user();
        if (!$user || !method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $order->update($request->only(['title', 'description', 'price', 'status'])); // Обновляем данные заказа
        return response()->json(['message' => 'Заказ обновлен', 'order' => $order]);
    }

    public function deleteOrder(Order $order): JsonResponse
    {
        $user = auth()->user();
        if (!$user || !method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $order->delete();
        return response()->json(['message' => 'Заказ удален']);
    }
}

