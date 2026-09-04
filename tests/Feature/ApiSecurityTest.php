<?php

use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Page;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CustomerAuthTokenService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function securityStore(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'business_name' => 'Loja Segura',
        'slug' => fake()->unique()->slug(2),
        'is_active' => true,
    ], $attributes));
}

test('inactive stores and inactive pages are not exposed by the store api', function () {
    $inactiveStore = securityStore(['slug' => 'loja-inativa', 'is_active' => false]);

    $this->getJson("/api/store/{$inactiveStore->slug}/info")->assertNotFound();

    $store = securityStore(['slug' => 'loja-ativa']);
    $page = Page::query()->create([
        'user_id' => $store->id,
        'key' => 'pagina-oculta',
        'title' => 'Página oculta',
        'type' => 'simple',
        'content' => 'Conteúdo privado',
        'is_active' => false,
    ]);

    $this->getJson("/api/store/{$store->slug}/pages/{$page->key}")->assertNotFound();
});

test('private products are not exposed by either public api', function () {
    $store = securityStore();
    $product = Product::query()->create([
        'user_id' => $store->id,
        'name' => 'Produto privado',
        'price' => 10,
        'is_public' => false,
    ]);

    $this->getJson("/api/store/{$store->slug}/products/{$product->id}")->assertNotFound();
    $this->getJson("/api/v1/users/{$store->slug}/products/{$product->id}")->assertNotFound();
});

test('inactive customers cannot log in', function () {
    $store = securityStore();
    $customer = Customer::query()->create([
        'user_id' => $store->id,
        'name' => 'Cliente bloqueado',
        'email' => 'bloqueado@example.com',
        'password' => 'password',
        'is_active' => false,
    ]);

    $this->postJson("/api/store/{$store->slug}/customers/login", [
        'identifier' => $customer->email,
        'password' => 'password',
    ])->assertUnprocessable()
        ->assertJsonPath('message', 'E-mail, WhatsApp ou senha inválidos.');
});

test('customer login is rate limited', function () {
    $store = securityStore();
    $url = "/api/store/{$store->slug}/customers/login";

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.40'])
            ->postJson($url, ['identifier' => 'nobody@example.com', 'password' => 'invalid'])
            ->assertUnprocessable();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.40'])
        ->postJson($url, ['identifier' => 'nobody@example.com', 'password' => 'invalid'])
        ->assertTooManyRequests();
});

test('a customer cannot favorite a product from another store', function () {
    $store = securityStore();
    $otherStore = securityStore();
    $customer = Customer::query()->create([
        'user_id' => $store->id,
        'name' => 'Cliente',
        'email' => 'cliente@example.com',
        'password' => 'password',
        'is_active' => true,
    ]);
    $otherProduct = Product::query()->create([
        'user_id' => $otherStore->id,
        'name' => 'Produto de outra loja',
        'price' => 20,
        'is_public' => true,
    ]);
    $token = app(CustomerAuthTokenService::class)->issueToken($customer)['token'];

    $this->withToken($token)
        ->postJson("/api/customer/{$store->slug}/favorites", ['product_id' => $otherProduct->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('product_id');

    expect(Favorite::query()->count())->toBe(0);
});

test('public product pagination is capped', function () {
    $store = securityStore();

    foreach (range(1, 55) as $number) {
        Product::query()->create([
            'user_id' => $store->id,
            'name' => "Produto {$number}",
            'price' => $number,
            'is_public' => true,
        ]);
    }

    $this->getJson("/api/store/{$store->slug}/products?per_page=5000")
        ->assertOk()
        ->assertJsonCount(50, 'data')
        ->assertJsonPath('per_page', 50);
});

test('product image uploads are validated on the server', function () {
    Storage::fake('public_direct');

    $store = securityStore();
    Subscription::query()->create([
        'user_id' => $store->id,
        'plan' => 'pro',
        'price' => 59.90,
        'billing_period' => 'monthly',
        'status' => 'active',
        'next_billing_at' => now()->addMonth(),
    ]);
    $page = Page::query()->create([
        'user_id' => $store->id,
        'key' => 'catalogo-seguro',
        'title' => 'Catálogo seguro',
        'type' => 'products',
        'content' => '',
        'is_active' => true,
    ]);

    $this->actingAs($store)
        ->post(route('painel.pages.update', $page->key), [
            'page' => json_encode([
                'title' => $page->title,
                'is_active' => true,
                'type' => 'products',
                'content' => '',
            ]),
            'categorias' => json_encode([]),
            'produtos' => json_encode([]),
            'produtos_images' => [
                UploadedFile::fake()->create('codigo.php', 10, 'application/x-php'),
            ],
        ])
        ->assertSessionHasErrors('produtos_images.0');

    Storage::disk('public_direct')->assertDirectoryEmpty('products');
});
