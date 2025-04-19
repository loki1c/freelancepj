<?php

// App\Http\Controllers\Order\ChatController.php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $userId = Auth::id();
        $orderId = $request->order_id;

        // Получаем заказ
        $order = Order::find($orderId);

        // Проверка, что пользователь либо является заказчиком, либо откликнувшимся
        if ($order->user_id == $userId) {
            return response()->json(['message' => 'Заказчик не может создать чат сам с собой.'], 400);
        }

        // Проверка, есть ли уже чат между заказчиком и откликнувшимся пользователем
        $chat = OrderChat::where('order_id', $orderId)
            ->where(function($query) use ($userId, $order) {
                $query->where('user_id', $userId)
                    ->orWhere('user_id', $order->user_id);
            })
            ->first();

        if ($chat) {
            return response()->json(['chat' => $chat], 200);
        }

        // Если чата нет, создаем новый
        $chat = OrderChat::create([
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);

        return response()->json(['chat' => $chat], 201);
    }


    public function show($id)
    {
        $userId = Auth::id();

        // Логирование для проверки передаваемых параметров
        Log::info('Fetching chat', ['chat_id' => $id, 'user_id' => $userId]);

        // Получаем чат по ID
        $chat = OrderChat::with(['messages.sender', 'messages.recipient'])
            ->where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('order', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
            ->first();

        // Логирование для проверки, найден ли чат
        if (!$chat) {
            Log::warning('Chat not found', ['chat_id' => $id, 'user_id' => $userId]);
            return response()->json(['message' => 'Chat not found'], 404);
        }

        return response()->json(['chat' => $chat]);
    }


    public function sendMessage(Request $request, $orderId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $userId = auth()->id();
        $order = Order::findOrFail($orderId);

        // Получатель — если текущий пользователь = заказчик, значит получатель — исполнитель и наоборот
        $recipientId = $order->user_id === $userId
            ? $order->accepted_user_id
            : $order->user_id;

        if (!$recipientId) {
            return response()->json(['message' => 'Невозможно определить получателя'], 400);
        }

        // Поиск или создание чата
        $chat = OrderChat::firstOrCreate(
            ['order_id' => $orderId],
            ['user_id' => $userId]
        );

        // Создание сообщения
        $message = Message::create([
            'order_chat_id' => $chat->id,
            'sender_id' => $userId,
            'recipient_id' => $recipientId,
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'message' => $message,
        ]);
    }
}

