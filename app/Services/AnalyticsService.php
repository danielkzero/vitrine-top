<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductView;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function summary(User $store, ?CarbonInterface $start = null, ?CarbonInterface $end = null): array
    {
        [$start, $end] = $this->normalizeRange($start, $end);

        $orders = Order::query()
            ->where('user_id', $store->id)
            ->whereBetween('created_at', [$start, $end]);

        $totalOrders = (clone $orders)->count();
        $totalRevenue = (clone $orders)->sum('total');

        $topProducts = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as sold_quantity'))
            ->where('user_id', $store->id)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('product_id')
            ->orderByDesc('sold_quantity')
            ->with('product:id,name')
            ->limit(5)
            ->get();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => (float) $totalRevenue,
            'customers_count' => $store->customers()->count(),
            'visits_count' => Visit::where('user_id', $store->id)->whereBetween('visited_at', [$start, $end])->count(),
            'page_views_count' => DB::table('page_views')->where('user_id', $store->id)->whereBetween('viewed_at', [$start, $end])->count(),
            'product_views_count' => ProductView::where('user_id', $store->id)->whereBetween('viewed_at', [$start, $end])->count(),
            'top_products' => $topProducts->map(fn (OrderItem $item) => [
                'product_id' => $item->product_id,
                'name' => $item->product?->name ?? 'Produto removido',
                'sold_quantity' => (int) $item->sold_quantity,
            ])->values(),
        ];
    }

    public function ordersByPeriod(User $store, string $groupBy = 'day', ?CarbonInterface $start = null, ?CarbonInterface $end = null)
    {
        [$start, $end] = $this->normalizeRange($start, $end);

        $format = match ($groupBy) {
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return Order::query()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as revenue')
            ->where('user_id', $store->id)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    private function normalizeRange(?CarbonInterface $start, ?CarbonInterface $end): array
    {
        $start = $start?->copy() ?? now()->startOfMonth();
        $end = $end?->copy() ?? now()->endOfDay();

        return [$start, $end];
    }
}
