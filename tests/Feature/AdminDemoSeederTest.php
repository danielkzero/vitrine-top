<?php

use App\Models\Payment;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\AdminDemoSeeder;

test('admin demo seeder creates varied clients and remains idempotent', function () {
    $this->seed(AdminDemoSeeder::class);

    $clientsBefore = User::where('email', 'like', 'cliente.demo%@vitrine.test')->count();
    $productsBefore = Product::whereHas('user', fn ($query) => $query->where('email', 'like', 'cliente.demo%@vitrine.test'))->count();
    $paymentsBefore = Payment::where('transaction_id', 'like', 'admin-demo-%')->count();

    $this->seed(AdminDemoSeeder::class);

    expect($clientsBefore)->toBe(8)
        ->and(User::where('email', 'like', 'cliente.demo%@vitrine.test')->count())->toBe(8)
        ->and(Product::whereHas('user', fn ($query) => $query->where('email', 'like', 'cliente.demo%@vitrine.test'))->count())->toBe($productsBefore)
        ->and(Payment::where('transaction_id', 'like', 'admin-demo-%')->count())->toBe($paymentsBefore)
        ->and(Subscription::where('status', 'trial')->count())->toBeGreaterThanOrEqual(2)
        ->and(Subscription::whereNotNull('custom_plan_name')->count())->toBeGreaterThanOrEqual(1);
});
