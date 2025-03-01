<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty(auth()->user()->is_admin)) {
            if (!auth()->user() || !auth()->user()->is_admin) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }
        }
        return $next($request);
    }
}

