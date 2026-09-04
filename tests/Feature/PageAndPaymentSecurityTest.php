<?php

use App\Models\Page;
use App\Models\Product;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;

test('user cannot edit another users page by key', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $page = Page::create([
        'user_id' => $owner->id,
        'key' => 'private-page',
        'title' => 'Private',
        'icon' => 'FileText',
        'is_active' => true,
        'order' => 1,
        'type' => 'simple',
        'content' => 'hidden',
    ]);

    $this->actingAs($intruder)
        ->get(route('painel.pages.edit', ['key' => $page->key]))
        ->assertNotFound();
});

test('user cannot reorder another users pages', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $page = Page::create([
        'user_id' => $owner->id,
        'key' => 'owner-page',
        'title' => 'Owner Page',
        'icon' => 'FileText',
        'is_active' => true,
        'order' => 1,
        'type' => 'simple',
        'content' => '',
    ]);

    $this->actingAs($intruder)
        ->post(route('painel.pages.reorder'), [
            'pages' => [
                ['id' => $page->id, 'order' => 99],
            ],
        ])
        ->assertSessionHasErrors('pages.0.id');

    expect($page->fresh()->order)->toBe(1);
});

test('payment store rejects subscription from another user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $subscription = Subscription::create([
        'user_id' => $owner->id,
        'plan' => 'pro',
        'price' => 59.90,
        'billing_period' => 'monthly',
        'status' => 'active',
    ]);

    $this->actingAs($intruder)
        ->post(route('painel.payments.store'), [
            'subscription_id' => $subscription->id,
            'transaction_id' => 'tx-123',
            'amount' => 59.90,
            'currency' => 'BRL',
            'method' => 'pix',
        ])
        ->assertSessionHasErrors('subscription_id');

    $this->assertDatabaseMissing('payments', [
        'user_id' => $intruder->id,
        'subscription_id' => $subscription->id,
    ]);
});

test('store owner cannot associate a review with another stores product', function () {
    $owner = User::factory()->create();
    $otherStore = User::factory()->create();

    Subscription::query()->create([
        'user_id' => $owner->id,
        'plan' => 'pro',
        'price' => 59.90,
        'billing_period' => 'monthly',
        'status' => 'active',
    ]);

    $ownerProduct = Product::query()->create([
        'user_id' => $owner->id,
        'name' => 'Produto da loja',
        'price' => 10,
    ]);
    $otherProduct = Product::query()->create([
        'user_id' => $otherStore->id,
        'name' => 'Produto externo',
        'price' => 20,
    ]);
    $review = Review::query()->create([
        'user_id' => $owner->id,
        'product_id' => $ownerProduct->id,
        'customer_name' => 'Cliente',
        'rating' => 5,
        'comment' => 'Ótimo produto',
        'status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->put(route('painel.reviews.update', $review), [
            'product_id' => $otherProduct->id,
            'status' => 'approved',
        ])
        ->assertSessionHasErrors('product_id');

    expect($review->fresh()->product_id)->toBe($ownerProduct->id)
        ->and($review->fresh()->status)->toBe('pending');
});
