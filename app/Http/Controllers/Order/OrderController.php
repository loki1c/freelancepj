<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderUpdateRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $order->update($request->only(['title', 'description', 'price', 'status', 'category', 'deadline',]));

        return response()->json(['message' => 'Заказ обновлён', 'order' => $order]);
    }

    // Удаление заказа
    public function deleteOrder(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(['message' => 'Заказ удалён']);
    }
    public function showPublic($id): JsonResponse
    {
        $order = Order::with('user')->find($id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if (Auth::check() && Auth::id() !== $order->user_id) {
            // Проверка, чтобы пользователь мог просматривать заказ только один раз
            $viewExists = DB::table('order_views')
                ->where('user_id', Auth::id())
                ->where('order_id', $order->id)
                ->exists();

            if (!$viewExists) {
                // Добавляем новый просмотр для этого заказа
                DB::table('order_views')->insert([
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json([
            'id' => $order->id,
            'title' => $order->title,
            'description' => $order->description,
            'price' => $order->price,
            'category' => $order->category,
            'deadline' => $order->deadline,
            'file' => $order->file,
            'status' => $order->status,
            'createdAt' => $order->created_at,
            'updatedAt' => $order->updated_at,
            'user' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
        ]);
    }
    public function getOrderViewCount($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Получаем количество просмотров из таблицы order_views
        $viewCount = DB::table('order_views')
            ->where('order_id', $id)
            ->count();

        return response()->json(['view_count' => $viewCount]);
    }
}
