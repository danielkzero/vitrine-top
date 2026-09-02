<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PlanLimitService;

test('regular users cannot access the platform administration', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get(route('admin.index'))->assertForbidden();
});

test('platform administrators can see clients and resource usage', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $client = User::factory()->create(['business_name' => 'Loja Cliente']);
    $client->products()->create([
        'name' => 'Produto teste',
        'slug' => 'produto-teste',
        'price' => 10,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Index')
            ->where('stats.clients', 1)
            ->where('clients.data.0.business_name', 'Loja Cliente')
            ->where('clients.data.0.usage.products', 1));
});

test('administrator can assign custom limits to a client subscription', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $client = User::factory()->create();
    $plan = Plan::create([
        'code' => 'basic',
        'name' => 'Plano Básico',
        'monthly_price' => 24.90,
        'annual_price_total' => 238.80,
        'annual_monthly_equivalent' => 19.90,
        'products_limit' => 30,
        'product_images_limit' => 3,
        'gallery_images_limit' => 30,
        'banners_limit' => 3,
        'trial_days' => 14,
        'is_active' => true,
    ]);
    Subscription::factory()->create(['user_id' => $client->id, 'plan_id' => $plan->id, 'plan' => 'basic']);

    $this->actingAs($admin)->put(route('admin.clients.subscription.update', $client), [
        'plan_id' => $plan->id,
        'custom_plan_name' => 'Plano Parceiro',
        'price' => 49.90,
        'billing_period' => 'monthly',
        'status' => 'active',
        'trial_ends_at' => null,
        'next_billing_at' => now()->addMonth()->toDateString(),
        'custom_products_limit' => 150,
        'custom_product_images_limit' => 8,
        'custom_gallery_images_limit' => 80,
        'custom_banners_limit' => 12,
    ])->assertRedirect();

    $client->refresh()->load('subscription');
    $effectivePlan = app(PlanLimitService::class)->getPlanForUser($client);

    expect($client->subscription->custom_plan_name)->toBe('Plano Parceiro')
        ->and($effectivePlan->name)->toBe('Plano Parceiro')
        ->and($effectivePlan->products_limit)->toBe(150)
        ->and($effectivePlan->banners_limit)->toBe(12);
});
