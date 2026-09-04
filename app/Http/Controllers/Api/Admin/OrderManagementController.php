<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderManagementController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->with(['customer:id,name,email', 'items'])
            ->latest()
            ->paginate(min(100, max(1, (int) $request->integer('per_page', 20))));

        return response()->json($orders);
    }

    public function show(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->with(['customer', 'items.product'])
            ->firstOrFail();

        return response()->json(['data' => $order]);
    }

    public function updateStatus(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,preparing,shipped,delivered,canceled'],
        ]);

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->firstOrFail();

        $order = $this->orderService->updateStatus($order, OrderStatus::from($data['status']));

        return response()->json(['data' => $order]);
    }
}
