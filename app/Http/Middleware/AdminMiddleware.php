<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && (int) auth()->user()->access_level === 1) {
            return $next($request);
        }

        // Em vez de redirect, usamos abort(403)
        // Isso fará o teste de assertStatus(403) passar!
        abort(403, 'Acesso negado.');
    }
}
