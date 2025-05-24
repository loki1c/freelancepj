<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class PublicOrderController extends Controller
{

    public function show($id, Request $request)
    {
        $order = Order::with('user')->findOrFail($id);

        if ($order->status === "Закрыт") {
          return response()->json(['message' => 'Закрыт заказ'], 404);
        }
        if ($order->status === "В разработке") {
            return response()->json(['message' => 'Заказ в разработке'], 404);
        }
        // Проверка: является ли пользователь владельцем заказа
        $isOwner = $order->user_id === $request->user()->id;

        return response()->json([
            'id' => $order->id,
            'title' => $order->title,
            'description' => $order->description,
            'price' => $order->price,
            'category' => $order->category, // Добавляем category
            'deadline' => $order->deadline, // Добавляем deadline
            'user_id' => $order->user_id,
            'is_owner' => $isOwner,
        ]);
    }


}
