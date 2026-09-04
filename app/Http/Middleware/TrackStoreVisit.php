<?php

namespace App\Http\Middleware;

use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\Services\VisitTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackStoreVisit
{
    public function __construct(private readonly VisitTrackingService $trackingService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $storeSlug = (string) ($request->route('storeSlug') ?? $request->route('slug'));

            if ($storeSlug === '') {
                return $response;
            }

            $store = User::query()->where('slug', $storeSlug)->first();

            if (!$store) {
                return $response;
            }

            $visit = $this->trackingService->registerVisit($store, $request);

            $productId = $request->route('productId') ?? $request->route('id');
            if ($productId) {
                $product = Product::query()
                    ->where('user_id', $store->id)
                    ->where('id', (int) $productId)
                    ->first();

                $this->trackingService->registerProductView($store, $product, $visit, $request);

                return $response;
            }

            $pageKey = $request->route('pageKey') ?? $request->route('page');
            if ($pageKey) {
                $page = Page::query()
                    ->where('user_id', $store->id)
                    ->where('key', (string) $pageKey)
                    ->first();

                $this->trackingService->registerPageView($store, $page, $visit, $request);
                return $response;
            }

            if (str_contains((string) $request->path(), '/pages')) {
                $this->trackingService->registerPageView($store, null, $visit, $request);
            }
        } catch (\Throwable) {
            // Tracking nunca deve derrubar a resposta principal.
        }

        return $response;
    }
}
