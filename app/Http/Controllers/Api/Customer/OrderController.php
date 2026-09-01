<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\Concerns\ResolvesStore;
use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ResolvesStore;

    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $orders = Order::query()
            ->where('user_id', $store->id)
            ->where('customer_id', $customer->id)
            ->with('items')
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json($orders);
    }

    public function show(Request $request, string $storeSlug, int $orderId): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $order = Order::query()
            ->where('user_id', $store->id)
            ->where('customer_id', $customer->id)
            ->where('id', $orderId)
            ->with('items')
            ->firstOrFail();

        return response()->json(['data' => $order]);
    }

    public function checkout(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'payment_method' => ['required', 'string', 'max:50'],
            'shipping_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $address = CustomerAddress::query()
            ->where('id', $data['address_id'])
            ->where('customer_id', $customer->id)
            ->where('user_id', $store->id)
            ->firstOrFail();

        $order = $this->orderService->checkout(
            $store,
            $customer,
            $address,
            $data['payment_method'],
            $data['shipping_method'] ?? null,
            $data['notes'] ?? null
        )->load('items', 'customer');

        return response()->json(['data' => $order], 201);
    }

    public function repeat(Request $request, string $storeSlug, int $orderId): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $order = Order::query()
            ->where('user_id', $store->id)
            ->where('customer_id', $customer->id)
            ->where('id', $orderId)
            ->with('items')
            ->firstOrFail();

        $cart = $this->orderService->repeatOrder($store, $customer, $order);

        return response()->json(['cart' => $cart]);
    }

    public function whatsappLink(Request $request, string $storeSlug, int $orderId): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $order = Order::query()
            ->where('user_id', $store->id)
            ->where('customer_id', $customer->id)
            ->where('id', $orderId)
            ->with('items')
            ->firstOrFail();

        $phone = $store->whatsapp ?: $store->phone_primary;

        if (!$phone) {
            return response()->json(['message' => 'Loja sem WhatsApp configurado.'], 422);
        }

        return response()->json([
            'whatsapp_url' => $this->orderService->generateWhatsappCheckoutLink($order, $phone),
        ]);
    }

    public function latestItems(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $items = \App\Models\OrderItem::query()
            ->where('user_id', $store->id)
            ->where('customer_id', $customer->id)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json(['data' => $items]);
    }
}
