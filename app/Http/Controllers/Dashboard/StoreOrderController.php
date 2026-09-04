<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreOrderController extends BaseController
{
    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');

        $orders = Order::query()
            ->where('user_id', $this->user->id)
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->with(['customer:id,name,email,whatsapp', 'items.product:id,name'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Dashboard/Orders/Index', [
            'orders' => $orders,
            'filters' => ['status' => $status],
        ]);
    }
}
