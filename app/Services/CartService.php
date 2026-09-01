<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getOrCreateActiveCart(User $store, Customer $customer): Cart
    {
        return Cart::firstOrCreate(
            [
                'user_id' => $store->id,
                'customer_id' => $customer->id,
                'status' => CartStatus::ACTIVE->value,
            ],
            [
                'last_activity_at' => now(),
                'expires_at' => now()->addDays(7),
            ]
        );
    }

    public function addItem(User $store, Customer $customer, int $productId, int $quantity = 1): Cart
    {
        $product = Product::query()
            ->where('user_id', $store->id)
            ->where('id', $productId)
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => 'Produto nao encontrado para esta loja.',
            ]);
        }

        return DB::transaction(function () use ($store, $customer, $product, $quantity) {
            $cart = $this->getOrCreateActiveCart($store, $customer);

            $item = CartItem::query()->firstOrNew([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ]);

            $item->fill([
                'user_id' => $store->id,
                'customer_id' => $customer->id,
                'quantity' => max(1, (int) ($item->quantity ?? 0) + $quantity),
                'unit_price' => (float) $product->price,
            ]);

            $item->total_price = $item->quantity * (float) $item->unit_price;
            $item->save();

            $cart->forceFill([
                'last_activity_at' => now(),
                'expires_at' => now()->addDays(7),
            ])->save();

            return $cart->load('items.product');
        });
    }

    public function updateQuantity(User $store, Customer $customer, int $itemId, int $quantity): Cart
    {
        return DB::transaction(function () use ($store, $customer, $itemId, $quantity) {
            $item = CartItem::query()
                ->where('id', $itemId)
                ->where('user_id', $store->id)
                ->where('customer_id', $customer->id)
                ->firstOrFail();

            if ($quantity <= 0) {
                $item->delete();
            } else {
                $item->quantity = $quantity;
                $item->total_price = $item->quantity * (float) $item->unit_price;
                $item->save();
            }

            $cart = Cart::query()
                ->where('id', $item->cart_id)
                ->firstOrFail();

            $cart->forceFill(['last_activity_at' => now()])->save();

            return $cart->load('items.product');
        });
    }

    public function removeItem(User $store, Customer $customer, int $itemId): Cart
    {
        return DB::transaction(function () use ($store, $customer, $itemId) {
            $item = CartItem::query()
                ->where('id', $itemId)
                ->where('user_id', $store->id)
                ->where('customer_id', $customer->id)
                ->firstOrFail();

            $cartId = $item->cart_id;
            $item->delete();

            $cart = Cart::query()->findOrFail($cartId);
            $cart->forceFill(['last_activity_at' => now()])->save();

            return $cart->load('items.product');
        });
    }

    public function clear(User $store, Customer $customer): void
    {
        $cart = $this->getOrCreateActiveCart($store, $customer);
        $cart->items()->delete();
        $cart->forceFill(['last_activity_at' => now()])->save();
    }

    public function totals(Cart $cart): array
    {
        $subtotal = (float) $cart->items->sum('total_price');

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'items_count' => (int) $cart->items->sum('quantity'),
        ];
    }
}
