<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrameworkAuth
{
    public function handle($request, \Closure $next)
    {
        // WAJIB menyebutkan 'api' agar Laravel tahu kita pakai JWT driver
        if (!auth('api')->check()) { 
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau expired.',
            ], 401);
        }

        return $next($request);
    }
}