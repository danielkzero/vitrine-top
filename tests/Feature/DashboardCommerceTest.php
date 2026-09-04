<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('store owner sees only their own customers and orders in dashboard pages', function () {
    $this->seed(DatabaseSeeder::class);

    $owner = User::query()->where('slug', 'minha-lojinha')->firstOrFail();
    $other = User::factory()->create(['email_verified_at' => now()]);

    Customer::create([
        'user_id' => $other->id,
        'name' => 'Cliente de outra loja',
        'email' => 'cliente-outra-loja@example.com',
        'whatsapp' => '11999990000',
        'password' => 'password',
    ]);
    Order::create([
        'order_number' => 'ORD-OUTRA-LOJA',
        'user_id' => $other->id,
        'customer_name' => 'Pedido de outra loja',
        'status' => 'pending',
        'total' => 100,
    ]);

    $this->actingAs($owner)
        ->get(route('painel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Customers/Index')
            ->where('customers.data', fn ($customers) => collect($customers)->every(fn ($customer) => $customer['user_id'] === $owner->id))
        );

    $this->actingAs($owner)
        ->get(route('painel.orders.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Orders/Index')
            ->where('orders.data', fn ($orders) => collect($orders)->every(fn ($order) => $order['user_id'] === $owner->id))
        );
});
