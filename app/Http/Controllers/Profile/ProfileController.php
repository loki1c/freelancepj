<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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
            'file' => 'nullable|file|max:10240',
            'category' => 'nullable|string|max:255',
            'deadline' => 'nullable|date|after_or_equal:today',
            'status' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('order_files', 'public');
        }

        $order = Auth::user()->orders()->create($validated);

        return response()->json($order, 201);
    }


    // Просмотр конкретного заказа пользователя
    public function show($id): \Illuminate\Http\JsonResponse
    {
        // Получаем заказ по ID для текущего пользователя
        $order = Auth::user()->orders()->where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or unauthorized'], 403);
        }

        // При необходимости можно добавить поле файла или другие связи (например, связь с категорией)
        return response()->json([
            'id' => $order->id,
            'title' => $order->title,
            'description' => $order->description,
            'price' => $order->price,
            'category' => $order->category, // если поле категорий сохранено в заказе
            'deadline' => $order->deadline,
            'file' => $order->file, // возвращаем путь к файлу
            'status' => $order->status,
            'createdAt' => $order->created_at,
            'updatedAt' => $order->updated_at,
            'user' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
        ]);
    }

    // Обновление заказа
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $order = Auth::user()->orders()->where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'category' => 'nullable|string|max:255',
            'deadline' => 'nullable|date|after_or_equal:today',
            'file' => 'nullable|file|max:10240', // до 10 MB
        ]);

        if ($request->hasFile('file')) {
            // Удаляем старый файл, если он есть
            if ($order->file) {
                Storage::disk('public')->delete($order->file); // удаляем файл с диска
            }
            $order->delete();

            // Сохраняем новый
            if ($request->hasFile('file')) {
                // Если есть новый файл, удаляем старый
                if ($order->file) {
                    Storage::disk('public')->delete($order->file);
                }

                // Сохраняем новый файл
                $validated['file'] = $request->file('file')->store('order_files', 'public');
            }
        }

        $order->update($validated);

        return response()->json($order);
    }
    public function downloadFile($id)
    {
        // Ищем заказ по ID для текущего пользователя
        $order = Auth::user()->orders()->findOrFail($id);

        // Проверяем, существует ли файл и доступен ли он
        if (!$order->file || !Storage::disk('public')->exists($order->file)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        // Скачивание файла
        return Storage::disk('public')->download($order->file);
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
