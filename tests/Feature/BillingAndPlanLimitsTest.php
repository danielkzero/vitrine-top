<?php

use App\Models\Banner;
use App\Models\Page;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('expired account is redirected to required billing page', function () {
    $user = User::factory()->create(['is_active' => true]);

    Subscription::create([
        'user_id' => $user->id,
        'plan' => 'pro',
        'price' => 24.90,
        'billing_period' => 'monthly',
        'status' => 'trial',
        'trial_ends_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get(route('painel.index'))
        ->assertRedirect(route('painel.billing.required'));

    expect($user->fresh()->is_active)->toBeFalse();
});

test('active account can access dashboard', function () {
    $user = User::factory()->create(['is_active' => false]);

    Subscription::create([
        'user_id' => $user->id,
        'plan' => 'pro',
        'price' => 24.90,
        'billing_period' => 'monthly',
        'status' => 'active',
        'next_billing_at' => now()->addMonth(),
    ]);

    $this->actingAs($user)
        ->get(route('painel.index'))
        ->assertOk();

    expect($user->fresh()->is_active)->toBeTrue();
});

test('cannot exceed product limit of thirty items', function () {
    $user = User::factory()->create(['is_active' => true]);

    Subscription::create([
        'user_id' => $user->id,
        'plan' => 'pro',
        'price' => 24.90,
        'billing_period' => 'monthly',
        'status' => 'active',
        'next_billing_at' => now()->addMonth(),
    ]);

    $page = Page::create([
        'user_id' => $user->id,
        'key' => 'catalogo',
        'title' => 'Catalogo',
        'icon' => 'Book',
        'is_active' => true,
        'order' => 1,
        'type' => 'products',
        'content' => '',
    ]);

    for ($i = 1; $i <= 30; $i++) {
        Product::create([
            'user_id' => $user->id,
            'name' => 'Produto '.$i,
            'price' => 10,
            'stock' => 10,
            'is_public' => true,
        ]);
    }

    $payloadPage = [
        'title' => $page->title,
        'icon' => $page->icon,
        'is_active' => true,
        'order' => 1,
        'type' => 'products',
        'content' => '',
    ];

    $payloadProducts = [
        [
            'name' => 'Produto novo',
            'price' => 20,
            'stock' => 3,
            'description' => '',
            'is_public' => true,
            'featured' => false,
            'images' => [],
        ],
    ];

    $this->actingAs($user)
        ->post(route('painel.pages.update', ['key' => $page->key]), [
            'page' => json_encode($payloadPage),
            'categorias' => json_encode([]),
            'produtos' => json_encode($payloadProducts),
        ])
        ->assertSessionHasErrors('produtos');
});

test('cannot exceed gallery limit of thirty images', function () {
    Storage::fake('public_direct');

    $user = User::factory()->create(['is_active' => true]);

    Subscription::create([
        'user_id' => $user->id,
        'plan' => 'pro',
        'price' => 24.90,
        'billing_period' => 'monthly',
        'status' => 'active',
        'next_billing_at' => now()->addMonth(),
    ]);

    for ($i = 1; $i <= 30; $i++) {
        Banner::create([
            'user_id' => $user->id,
            'image_url' => '/storage/banners/fake-'.$i.'.jpg',
        ]);
    }

    $this->actingAs($user)
        ->post(route('painel.banners.store'), [
            'title' => 'Banner limite',
            'image' => UploadedFile::fake()->image('banner.jpg'),
        ])
        ->assertSessionHasErrors('image');
});
