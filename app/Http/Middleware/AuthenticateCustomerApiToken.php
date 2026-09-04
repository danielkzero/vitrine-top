<?php

namespace App\Http\Middleware;

use App\Services\CustomerAuthTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateCustomerApiToken
{
    public function __construct(private readonly CustomerAuthTokenService $tokenService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return response()->json(['message' => 'Token de cliente ausente.'], 401);
        }

        $token = $this->tokenService->findValidToken($bearer);

        if (! $token || ! $token->customer || ! $token->customer->is_active) {
            return response()->json(['message' => 'Token de cliente inválido.'], 401);
        }

        $request->attributes->set('customer', $token->customer);
        $request->attributes->set('customer_token', $token);

        return $next($request);
    }
}
