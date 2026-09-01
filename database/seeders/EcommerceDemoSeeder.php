<?php

namespace Database\Seeders;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class EcommerceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->orderBy('id')->take(2)->get();

        foreach ($users as $user) {
            $products = Product::query()->where('user_id', $user->id)->take(5)->get();

            if ($products->isEmpty()) {
                continue;
            }

            for ($i = 1; $i <= 2; $i++) {
                $customer = Customer::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'email' => "cliente{$i}.{$user->id}@teste.local",
                    ],
                    [
                        'name' => "Cliente {$i} Loja {$user->id}",
                        'whatsapp' => '1199999000' . $i,
                        'password' => 'password',
                        'is_active' => true,
                        'last_login_at' => now()->subDays(rand(0, 7)),
                    ]
                );

                $address = CustomerAddress::query()->firstOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'zip' => '01001000',
                        'number' => (string) (100 + $i),
                    ],
                    [
                        'user_id' => $user->id,
                        'label' => 'Principal',
                        'is_default' => true,
                        'street' => 'Praca da Se',
                        'complement' => null,
                        'neighborhood' => 'Se',
                        'city' => 'Sao Paulo',
                        'state' => 'SP',
                        'reference' => 'Ao lado da catedral',
                        'notes' => 'Interfone no nome do cliente',
                        'metadata' => ['input_mode' => 'cep'],
                    ]
                );

                $cart = Cart::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'customer_id' => $customer->id,
                        'status' => CartStatus::ACTIVE->value,
                    ],
                    [
                        'last_activity_at' => now(),
                        'expires_at' => now()->addDays(7),
                    ]
                );

                foreach ($products->take(2) as $product) {
                    CartItem::query()->updateOrCreate(
                        [
                            'cart_id' => $cart->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'user_id' => $user->id,
                            'customer_id' => $customer->id,
                            'quantity' => 1,
                            'unit_price' => (float) $product->price,
                            'total_price' => (float) $product->price,
                        ]
                    );

                    Favorite::query()->firstOrCreate([
                        'user_id' => $user->id,
                        'customer_id' => $customer->id,
                        'product_id' => $product->id,
                    ]);
                }

                $order = Order::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'customer_id' => $customer->id,
                        'order_number' => 'DEMO-' . $user->id . '-' . $customer->id,
                    ],
                    [
                        'shipping_address_id' => $address->id,
                        'status' => OrderStatus::DELIVERED->value,
                        'subtotal' => 0,
                        'total' => 0,
                        'payment_method' => 'manual',
                        'shipping_method' => 'retirada',
                        'notes' => 'Pedido de demonstracao',
                        'address_snapshot' => [
                            'zip' => $address->zip,
                            'street' => $address->street,
                            'number' => $address->number,
                            'neighborhood' => $address->neighborhood,
                            'city' => $address->city,
                            'state' => $address->state,
                        ],
                        'customer_name' => $customer->name,
                        'contact' => $customer->whatsapp,
                        'placed_at' => now()->subDays(2),
                    ]
                );

                $subtotal = 0;

                foreach ($products->take(3) as $product) {
                    $qty = rand(1, 3);
                    $unit = (float) $product->price;
                    $total = $qty * $unit;
                    $subtotal += $total;

                    OrderItem::query()->updateOrCreate(
                        [
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'user_id' => $user->id,
                            'customer_id' => $customer->id,
                            'name_snapshot' => $product->name,
                            'sku_snapshot' => $product->code,
                            'quantity' => $qty,
                            'unit_price' => $unit,
                            'total_price' => $total,
                        ]
                    );
                }

                $order->forceFill(['subtotal' => $subtotal, 'total' => $subtotal])->save();

                $visit = Visit::query()->create([
                    'user_id' => $user->id,
                    'customer_id' => $customer->id,
                    'visitor_hash' => sha1("{$user->id}-{$customer->id}-" . now()->timestamp),
                    'ip' => '127.0.0.1',
                    'user_agent' => 'SeederBot',
                    'referrer' => 'https://google.com',
                    'utm_source' => 'seed',
                    'utm_medium' => 'demo',
                    'utm_campaign' => 'launch',
                    'visited_at' => now()->subMinutes(rand(10, 120)),
                ]);

                $page = Page::query()->where('user_id', $user->id)->first();

                if ($page) {
                    \App\Models\PageView::query()->create([
                        'user_id' => $user->id,
                        'page_id' => $page->id,
                        'visit_id' => $visit->id,
                        'customer_id' => $customer->id,
                        'page_key' => $page->key,
                        'url' => "https://app.local/{$user->slug}/{$page->key}",
                        'ip' => '127.0.0.1',
                        'viewed_at' => now()->subMinutes(rand(1, 60)),
                    ]);
                }

                ProductView::query()->create([
                    'user_id' => $user->id,
                    'product_id' => $products->first()->id,
                    'visit_id' => $visit->id,
                    'customer_id' => $customer->id,
                    'url' => "https://app.local/{$user->slug}/produto/{$products->first()->id}",
                    'ip' => '127.0.0.1',
                    'viewed_at' => now()->subMinutes(rand(1, 60)),
                ]);
            }
        }
    }
}
