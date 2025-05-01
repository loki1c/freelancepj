<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getNotifications(): JsonResponse
    {
        $user = Auth::user(); // уже будет доступен через middleware 'auth:sanctum'

        // Получаем ID заказов текущего пользователя
        $orderIds = Order::where('user_id', $user->id)->pluck('id');

        // Получаем отклики на эти заказы
        $requests = OrderRequest::whereIn('order_id', $orderIds)
            ->where('status', 'Ожидает подтверждения')
            ->with(['order', 'user']) // <-- отношения
            ->orderByDesc('created_at')
            ->get();

        return response()->json($requests);
    }
}


