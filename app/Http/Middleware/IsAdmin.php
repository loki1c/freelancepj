<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user(); // Получаем пользователя
        if (!$user || $user->is_admin === 0) { // Используем метод isAdmin()
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        return $next($request);
    }
}

