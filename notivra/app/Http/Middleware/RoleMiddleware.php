<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Pastikan user sudah login
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah role user saat ini ada di dalam daftar role yang diizinkan
        if (!in_array($request->user()->role->value, $roles)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
