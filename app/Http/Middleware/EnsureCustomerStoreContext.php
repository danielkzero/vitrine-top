<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerStoreContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->attributes->get('customer');
        $storeSlug = (string) $request->route('storeSlug');

        if (!$customer || !$customer->user || $customer->user->slug !== $storeSlug) {
            return response()->json(['message' => 'Cliente nao pertence a esta loja.'], 403);
        }

        return $next($request);
    }
}
