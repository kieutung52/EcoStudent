<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckAdmin Middleware
 * 
 * Kiểm tra quyền Admin của user
 * 
 * Flow:
 * 1. Middleware này chạy sau middleware 'auth:api' (JWT)
 * 2. User đã được load vào request bởi JWT middleware
 * 3. Kiểm tra role === 'ADMIN' (theo Design.md)
 * 4. Nếu không phải admin, trả về 403
 */
class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // User đã được load bởi JWT middleware (auth:api)
        // Kiểm tra role === 'ADMIN' (theo Design.md: role enum là 'USER', 'ADMIN')
        if (Auth::check() && Auth::user()->role === 'ADMIN') {
            return $next($request);
        }

        return response()->json([
            'message' => 'Bạn không có quyền truy cập chức năng này.'
        ], 403);
    }
}
