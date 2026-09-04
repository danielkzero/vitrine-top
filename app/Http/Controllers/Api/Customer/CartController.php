<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\Concerns\ResolvesStore;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResolvesStore;

    public function __construct(private readonly CartService $cartService) {}

    public function show(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $cart = $this->cartService
            ->getOrCreateActiveCart($store, $customer)
            ->load('items.product');

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function addItem(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $this->cartService->addItem($store, $customer, $data['product_id'], $data['quantity']);

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function updateItem(Request $request, string $storeSlug, int $itemId): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $cart = $this->cartService->updateQuantity($store, $customer, $itemId, $data['quantity']);

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function removeItem(Request $request, string $storeSlug, int $itemId): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $cart = $this->cartService->removeItem($store, $customer, $itemId);

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function clear(Request $request, string $storeSlug): JsonResponse
    {
        $store = $this->resolveStore($storeSlug);
        $customer = $request->attributes->get('customer');

        $this->cartService->clear($store, $customer);
        $cart = $this->cartService->getOrCreateActiveCart($store, $customer)->load('items.product');

        return response()->json([
            'cart' => $cart,
            'totals' => $this->cartService->totals($cart),
        ]);
    }
}
