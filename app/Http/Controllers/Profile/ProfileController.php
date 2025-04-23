<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    // Информация о профиле пользователя
    public function profile(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'user' => Auth::user(),
            'orders_url' => url('/api/profile/orders')
        ]);
    }

    // Получение всех заказов пользователя
    public function index(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Auth::user()->orders);
    }

    // Создание заказа
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'nullable|string|max:255',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);

        $order = Auth::user()->orders()->create($validated);

        return response()->json($order, 201);
    }

    // Просмотр конкретного заказа пользователя
    public function show($id): \Illuminate\Http\JsonResponse
    {
        $order = Auth::user()->orders()->where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or unauthorized'], 403);
        }

        return response()->json($order);
    }

    // Обновление заказа
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        // Находим заказ по ID, принадлежащий текущему пользователю
        $order = Auth::user()->orders()->where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or unauthorized'], 403);
        }

        // Валидация данных для обновления
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'category' => 'nullable|string|max:255',
            'deadline' => 'nullable|date|after_or_equal:today',
        ]);

        // Обновляем заказ с новыми данными
        $order->update($validated);

        return response()->json($order);
    }

    // Обновление профиля пользователя
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        return response()->json(['message' => 'Профиль обновлен', 'user' => $user]);
    }

    // Удаление заказа
    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $order = Auth::user()->orders()->where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or unauthorized'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted']);
    }
}
