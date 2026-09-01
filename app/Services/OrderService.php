<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function checkout(
        User $store,
        Customer $customer,
        CustomerAddress $address,
        string $paymentMethod,
        ?string $shippingMethod = null,
        ?string $notes = null
    ): Order {
        $cart = Cart::query()
            ->with('items.product')
            ->where('user_id', $store->id)
            ->where('customer_id', $customer->id)
            ->where('status', CartStatus::ACTIVE->value)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Carrinho vazio para finalizar o pedido.',
            ]);
        }

        return DB::transaction(function () use ($store, $customer, $address, $paymentMethod, $shippingMethod, $notes, $cart) {
            $totals = $this->cartService->totals($cart);

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $store->id,
                'customer_id' => $customer->id,
                'shipping_address_id' => $address->id,
                'status' => OrderStatus::PENDING->value,
                'subtotal' => $totals['subtotal'],
                'total' => $totals['total'],
                'payment_method' => $paymentMethod,
                'shipping_method' => $shippingMethod,
                'notes' => $notes,
                'address_snapshot' => [
                    'zip' => $address->zip,
                    'street' => $address->street,
                    'number' => $address->number,
                    'complement' => $address->complement,
                    'neighborhood' => $address->neighborhood,
                    'city' => $address->city,
                    'state' => $address->state,
                    'reference' => $address->reference,
                    'notes' => $address->notes,
                ],
                'customer_name' => $customer->name,
                'contact' => $customer->whatsapp,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'user_id' => $store->id,
                    'customer_id' => $customer->id,
                    'product_id' => $item->product_id,
                    'name_snapshot' => $item->product?->name ?? 'Produto removido',
                    'sku_snapshot' => $item->product?->code,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);
            }

            $cart->forceFill([
                'status' => CartStatus::CHECKED_OUT->value,
                'last_activity_at' => now(),
            ])->save();

            OrderCreated::dispatch($order->load('items', 'customer', 'user'));

            return $order;
        });
    }

    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $previous = $order->status;
        $previousStatus = $previous instanceof OrderStatus ? $previous->value : (string) $previous;

        $order->forceFill([
            'status' => $status->value,
            'canceled_at' => $status === OrderStatus::CANCELED ? now() : null,
        ])->save();

        OrderStatusUpdated::dispatch($order->fresh(['items', 'customer', 'user']), $previousStatus, $status->value);

        return $order;
    }

    public function repeatOrder(User $store, Customer $customer, Order $order): Cart
    {
        if ($order->user_id !== $store->id || $order->customer_id !== $customer->id) {
            throw ValidationException::withMessages([
                'order' => 'Pedido nao pertence a loja/cliente informado.',
            ]);
        }

        $this->cartService->clear($store, $customer);

        foreach ($order->items as $item) {
            if ($item->product_id) {
                $this->cartService->addItem($store, $customer, $item->product_id, $item->quantity);
            }
        }

        return $this->cartService->getOrCreateActiveCart($store, $customer)->load('items.product');
    }

    public function generateWhatsappCheckoutLink(Order $order, string $phone): string
    {
        $message = [
            "Pedido {$order->order_number}",
            "Total: R$ " . number_format((float) $order->total, 2, ',', '.'),
            'Itens:',
        ];

        foreach ($order->items as $item) {
            $message[] = "- {$item->quantity}x {$item->name_snapshot}";
        }

        $text = implode("\n", $message);

        return 'https://wa.me/' . preg_replace('/\D+/', '', $phone) . '?text=' . urlencode($text);
    }

    private function generateOrderNumber(): string
    {
        return sprintf('PED-%s-%s', now()->format('YmdHis'), strtoupper(Str::random(5)));
    }
}
