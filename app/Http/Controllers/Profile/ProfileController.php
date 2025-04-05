<?php
declare(strict_types = 1);
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
        $order = Auth::user()->orders()->where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or unauthorized'], 403);
        }

        $order->update($request->all());

        return response()->json($order);
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

