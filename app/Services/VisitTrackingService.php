<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitTrackingService
{
    public function registerVisit(User $store, Request $request): Visit
    {
        $rawVisitor = (string) ($request->cookie('visitor_id') ?? ($request->ip() . '|' . ($request->userAgent() ?? 'unknown')));
        $visitorHash = hash('sha256', $rawVisitor);

        return Visit::create([
            'user_id' => $store->id,
            'visitor_hash' => $visitorHash,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'visited_at' => now(),
        ]);
    }

    public function registerPageView(User $store, ?Page $page, ?Visit $visit, Request $request): void
    {
        \App\Models\PageView::create([
            'user_id' => $store->id,
            'page_id' => $page?->id,
            'visit_id' => $visit?->id,
            'page_key' => $page?->key ?? $request->query('page_key'),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'viewed_at' => now(),
        ]);
    }

    public function registerProductView(User $store, ?Product $product, ?Visit $visit, Request $request): void
    {
        ProductView::create([
            'user_id' => $store->id,
            'product_id' => $product?->id,
            'visit_id' => $visit?->id,
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'viewed_at' => now(),
        ]);
    }
}
