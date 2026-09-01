<?php

namespace App\Http\Middleware;

use App\Services\AccountStatusService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsInGoodStanding
{
    public function __construct(private readonly AccountStatusService $accountStatusService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = (string) $request->route()?->getName();

        if ($this->isWhitelistedRoute($routeName)) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $status = $this->accountStatusService->sync($user);

        if (!$status['allowed']) {
            return redirect()->route('painel.billing.required');
        }

        return $next($request);
    }

    private function isWhitelistedRoute(string $routeName): bool
    {
        if ($routeName === '') {
            return false;
        }

        return str_starts_with($routeName, 'painel.billing.')
            || str_starts_with($routeName, 'checkout.')
            || $routeName === 'logout'
            || $routeName === 'profile.destroy';
    }
}
