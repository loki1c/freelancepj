<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
class ProfileController extends Controller
{
    // Информация о профиле пользователя
    public function profile(Request $request): \Illuminate\Http\JsonResponse
    {
        $userId = $request->query('user_id');

        if ($userId && $userId != Auth::id()) {
            $user = User::withCount('orders')->find($userId);

            if (!$user) {
                return response()->json(['error' => 'Пользователь не найден'], 404);
            }

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'phone' => $user->phone,  // Добавляем phone
                    'city' => $user->city,
                    'photo' => $user->photo,  // Добавляем photo
                    'orders_count' => $user->orders_count,
                    'created_at' => $user->created_at,
                ],
                'is_current_user' => false,
            ]);
        }

        $user = Auth::user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone' => $user->phone,  // Добавляем phone
                'city' => $user->city,
                'photo' => $user->photo,  // Добавляем photo
                'created_at' => $user->created_at,
            ],
            'orders_url' => url('/api/profile/orders'),
            'is_current_user' => true,
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

        // Проверяем, был ли просмотр
        $viewExists = DB::table('order_views')
            ->where('user_id', Auth::id())
            ->where('order_id', $order->id)
            ->exists();

        if (!$viewExists) {
            // Сохраняем информацию о просмотре
            DB::table('order_views')->insert([
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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

    public function viewProfile($userId): \Illuminate\Http\JsonResponse
    {
        // Получаем пользователя по ID
        $user = User::findOrFail($userId);

        // Отфильтровываем только необходимые поля
        $profileData = $user->only(['firstname', 'lastname', 'email', 'phone', 'city', 'photo']);

        // Если фото пользователя есть, генерируем URL
        if ($user->photo) {
            $profileData['photo_url'] = asset('storage/' . $user->photo); // генерируем публичный URL
        }

        // Возвращаем данные профиля пользователя
        return response()->json([
            'user' => $profileData
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
    // Обновление профиля пользователя
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        // Валидация данных
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'city' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Получаем текущего авторизованного пользователя
        $user = Auth::user();

        // Если есть фото, сохраняем его
        if ($request->hasFile('photo')) {
            // Сохраняем фото в папку storage/app/public/photos
            $path = $request->file('photo')->store('photos', 'public'); // Указываем диск public

            // Заменяем старое фото на новое (если есть)
            $user->photo = str_replace('public/', 'storage/', $path);
        }

        // Обновляем информацию пользователя
        $user->update([
            'firstname' => $validatedData['firstname'],
            'lastname' => $validatedData['lastname'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'city' => $validatedData['city'],
        ]);

        // Возвращаем обновленные данные пользователя
        return response()->json([
            'message' => 'Профиль обновлен',
            'user' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city,
                'photo' => $user->photo ? asset($user->photo) : null, // Отдаем фото, если оно есть
            ],
        ]);
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
