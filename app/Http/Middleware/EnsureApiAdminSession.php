<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        return $next($request);
    }
}
