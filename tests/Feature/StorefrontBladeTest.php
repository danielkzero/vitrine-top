<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('public storefront is server rendered with seo product and institutional content', function () {
    $this->seed(DatabaseSeeder::class);

    $store = User::query()->where('slug', 'minha-lojinha')->firstOrFail();
    $product = Product::query()->where('user_id', $store->id)->firstOrFail();

    $this->get(route('vitrine.public.home', $store->slug))
        ->assertRedirect(route('vitrine.public.page', [$store->slug, 'catalogo']));

    $this->get(route('vitrine.public.page', [$store->slug, 'catalogo']))
        ->assertOk()
        ->assertSee('<meta name="description"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee($product->name)
        ->assertDontSee('@inertia', false);

    $this->get(route('vitrine.public.page.id', [$store->slug, 'catalogo', $product->id]))
        ->assertOk()
        ->assertSee('"@type":"Product"', false)
        ->assertSee($product->name)
        ->assertSee('Adicionar ao carrinho');

    $this->get(route('vitrine.public.page', [$store->slug, 'extra']))
        ->assertOk()
        ->assertSee('Termos e Privacidade')
        ->assertSee('Direitos do titular');
});

test('a storefront review is stored as pending for a product owned by that store', function () {
    $this->seed(DatabaseSeeder::class);

    $store = User::query()->where('slug', 'minha-lojinha')->firstOrFail();
    $product = Product::query()->where('user_id', $store->id)->firstOrFail();

    $this->from(route('vitrine.public.page.id', [$store->slug, 'catalogo', $product->id]))
        ->post(route('vitrine.reviews.store', [$store->slug, $product->id]), [
            'customer_name' => 'Cliente Teste',
            'rating' => 5,
            'comment' => 'Uma avaliação criada pela vitrine Blade.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $review = Review::query()->where('customer_name', 'Cliente Teste')->firstOrFail();
    expect($review->user_id)->toBe($store->id)
        ->and($review->product_id)->toBe($product->id)
        ->and($review->status)->toBe('pending');
});

test('sitemap exposes server rendered storefront pages and products', function () {
    $this->seed(DatabaseSeeder::class);

    $store = User::query()->where('slug', 'minha-lojinha')->firstOrFail();
    $product = Product::query()->where('user_id', $store->id)->firstOrFail();

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('vitrine.public.page', [$store->slug, 'catalogo']), false)
        ->assertSee(route('vitrine.public.page.id', [$store->slug, 'catalogo', $product->id]), false);

    $this->get(route('robots'))
        ->assertOk()
        ->assertSee('Sitemap: '.route('sitemap'));
});

test('store customer can log in with whatsapp as well as email', function () {
    $this->seed(DatabaseSeeder::class);

    $store = User::query()->where('slug', 'minha-lojinha')->firstOrFail();
    $customer = $store->customers()->firstOrFail();

    $this->postJson("/api/store/{$store->slug}/customers/login", [
        'identifier' => $customer->whatsapp,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('customer.id', $customer->id)
        ->assertJsonStructure(['token', 'expires_at']);

    $this->postJson("/api/store/{$store->slug}/customers/login", [
        'identifier' => $customer->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('customer.id', $customer->id);
});
