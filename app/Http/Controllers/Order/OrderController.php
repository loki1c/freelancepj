<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderUpdateRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CartOrder;
use App\Models\OrderRequest;
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
    public function addToCart($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Проверяем, не добавлен ли уже в корзину этим пользователем
        $exists = CartOrder::where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Этот заказ уже в вашей корзине'], 400);
        }

        // Добавляем заказ в корзину
        CartOrder::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Заказ добавлен в корзину']);
    }
    public function getCartOrders()
    {
        $userId = Auth::id();
        $cart = CartOrder::with('order')->where('user_id', $userId)->get();

        $orders = $cart->map(function ($item) {
            return [
                'id' => $item->order->id,
                'title' => $item->order->title,
                'description' => $item->order->description,
                'price' => $item->order->price,
            ];
        });

        return response()->json($orders);
    }
    public function closeOrder($id): JsonResponse
    {
        $order = Order::findOrFail($id);

        // Проверка, если заказ уже закрыт
        if ($order->status === 'Закрыт') {
            return response()->json(['message' => 'Этот заказ уже закрыт'], 400);
        }

        // Обновление статуса заказа на "Закрыт"
        $order->status = 'Закрыт';
        $order->save();

        return response()->json(['message' => 'Заказ успешно закрыт', 'order' => $order]);
    }

    public function sendRequest($orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        // Проверка, не является ли пользователь владельцем этого заказа
        if ($order->user_id === Auth::id()) {
            return response()->json(['message' => 'Вы не можете отправить запрос на свой собственный заказ'], 400);
        }

        // Проверка, доступен ли заказ для принятия
        if ($order->status !== 'Открыт') {
            return response()->json(['message' => 'Этот заказ уже не доступен для принятия'], 400);
        }

        // Проверка, не отправлял ли пользователь уже запрос на принятие
        $exists = OrderRequest::where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Вы уже отправили запрос на принятие этого заказа'], 400);
        }

        // Создание нового запроса на принятие
        $orderRequest = OrderRequest::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'status' => 'Ожидает подтверждения',
        ]);

        return response()->json([
            'message' => 'Запрос на принятие заказа отправлен',
            'request' => $orderRequest
        ]);
    }

    public function approveRequest($orderId, $requestId)
    {
        $order = Order::findOrFail($orderId);

        // Проверка, что текущий пользователь — владелец заказа
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Вы не являетесь владельцем этого заказа'], 403);
        }

        // Проверка, одобрен ли уже какой-то запрос
        $alreadyApproved = OrderRequest::where('order_id', $orderId)
            ->where('status', 'Подтвержден')
            ->exists();

        if ($alreadyApproved) {
            return response()->json(['message' => 'Вы уже одобрили один из запросов на этот заказ'], 400);
        }

        // Одобряем выбранный запрос
        $request = OrderRequest::where('order_id', $orderId)
            ->where('id', $requestId)
            ->firstOrFail();

        $request->status = 'Подтвержден';
        $request->save();

        // Отклоняем все остальные запросы
        OrderRequest::where('order_id', $orderId)
            ->where('id', '!=', $requestId)
            ->update(['status' => 'Отклонен']);

        return response()->json(['message' => 'Запрос одобрен, остальные отклонены']);
    }

    public function myRequests()
    {
        $user = Auth::user();

        $requests = OrderRequest::with('order') // подгружаем связанную информацию о заказе
        ->where('user_id', $user->id)
            ->get();

        return response()->json($requests);
    }
}
