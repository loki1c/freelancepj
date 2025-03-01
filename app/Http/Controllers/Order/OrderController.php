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
        if (!empty(auth()->user()->is_admin)) {
            if (!auth()->user()->is_admin) {
                return response()->json(['error' => 'Доступ запрещен'], 403);
            }
        }

                $orders = Order::all(); // Получаем все заказы
                return response()->json($orders);
        }
    public function updateOrder(Request $request, $id): JsonResponse
    {
        if (!empty(auth()->user()->is_admin)) {
            if (!auth()->user()->is_admin) {
                return response()->json(['error' => 'Доступ запрещен'], 403);
            }
        }

        $order = Order::findOrFail($id); // Найдет заказ или выбросит 404
        $orders = Order::where('status', 'open')->get(); // Получит все заказы со статусом "open"

        return response()->json(['message' => 'Заказ обновлен', 'order' => $order]);
    }

    public function deleteOrder($id): JsonResponse
    {
        if (!empty(auth()->user()->is_admin)) {
            if (!auth()->check() || !auth()->user()->is_admin) {
                return response()->json(['error' => 'Доступ запрещен'], 403);
            }
        }

        $order = Order::where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Заказ не найден'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Заказ удален']);
    }


}
