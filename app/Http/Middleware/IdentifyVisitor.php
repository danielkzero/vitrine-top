<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class IdentifyVisitor
{
    public function handle($request, Closure $next)
    {
        if (! $request->cookie('visitor_id')) {
            cookie()->queue(
                cookie(
                    'visitor_id',
                    (string) Str::uuid(),
                    60 * 24 * 365 // 1 ano
                )
            );
        }

        return $next($request);
    }
}

