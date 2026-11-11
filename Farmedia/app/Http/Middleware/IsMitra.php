<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsMitra
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // cek apakah sudah login dan rolenya mitra
        if (!Auth::check() || Auth::user()->role !== 'mitra') {
            abort(403, 'Anda bukan mitra.');
        }

        return $next($request);
    }
}
