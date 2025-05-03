<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Создание нового чата или получение существующего
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $userId = Auth::id();
        $orderId = $request->order_id;

        $order = Order::findOrFail($orderId);

        // Заказчик не может создать чат сам с собой
        if ($order->user_id == $userId) {
            return response()->json(['message' => 'Заказчик не может создать чат сам с собой.'], 400);
        }

        // Определяем двух участников
        $user1 = min($userId, $order->user_id);
        $user2 = max($userId, $order->user_id);

        // Проверка существующего чата
        $chat = OrderChat::where('order_id', $orderId)
            ->where(function ($q) use ($user1, $user2) {
                $q->where('user1_id', $user1)
                    ->where('user2_id', $user2);
            })->first();

        if ($chat) {
            return response()->json(['chat' => $chat], 200);
        }

        // Создание нового чата
        $chat = OrderChat::create([
            'order_id' => $orderId,
            'user1_id' => $user1,
            'user2_id' => $user2,
        ]);

        return response()->json(['chat' => $chat], 201);
    }

    // Получение чата по ID
    public function show($orderId)
    {
        $userId = Auth::id();

        $chat = OrderChat::with(['messages.sender', 'messages.recipient'])
            ->where('order_id', $orderId)
            ->where(function ($query) use ($userId) {
                $query->where('user1_id', $userId)
                    ->orWhere('user2_id', $userId);
            })
            ->first();

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        return response()->json(['chat' => $chat]);
    }

    // Отправка нового сообщения в чат
    public function sendMessage(Request $request, $orderId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $senderId = auth()->id();
        $order = Order::findOrFail($orderId);

        // Попробовать взять recipient_id из запроса
        $recipientId = $request->input('recipient_id');

        // Если не передан — попытаться определить
        if (!$recipientId) {
            $recipientId = $order->user_id === $senderId
                ? $order->accepted_user_id
                : $order->user_id;
        }

        if (!$recipientId) {
            return response()->json(['message' => 'Невозможно определить получателя'], 400);
        }

        // Определяем user1 и user2
        $user1 = min($senderId, $recipientId);
        $user2 = max($senderId, $recipientId);

        // Поиск или создание чата
        $chat = OrderChat::firstOrCreate([
            'order_id' => $orderId,
            'user1_id' => $user1,
            'user2_id' => $user2,
        ]);

        // Создание сообщения
        $message = Message::create([
            'order_chat_id' => $chat->id,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => $message,
        ]);
    }

}

