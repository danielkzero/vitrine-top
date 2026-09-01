<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;

test('public page api exposes the selected catalog mode', function () {
    $user = User::factory()->create([
        'business_name' => 'Loja Afiliada',
        'slug' => 'loja-afiliada',
        'is_active' => true,
    ]);

    Page::create([
        'user_id' => $user->id,
        'key' => 'catalogo',
        'title' => 'Ofertas',
        'type' => 'products',
        'catalog_mode' => 'affiliate',
        'is_active' => true,
    ]);

    $this->getJson('/api/v1/users/loja-afiliada/pages')
        ->assertOk()
        ->assertJsonPath('data.0.catalog_mode', 'affiliate');
});

test('public product api exposes its conversion action', function () {
    $user = User::factory()->create([
        'business_name' => 'Loja Hibrida',
        'slug' => 'loja-hibrida',
        'is_active' => true,
    ]);
    $category = Category::create(['user_id' => $user->id, 'name' => 'Ofertas']);

    Product::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'name' => 'Produto parceiro',
        'price' => 99.90,
        'conversion_type' => 'external',
        'external_url' => 'https://example.com/oferta?ref=vitrine',
        'cta_label' => 'Ver oferta',
        'is_public' => true,
    ]);

    $this->getJson('/api/v1/users/loja-hibrida/products')
        ->assertOk()
        ->assertJsonPath('data.0.conversion_type', 'external')
        ->assertJsonPath('data.0.external_url', 'https://example.com/oferta?ref=vitrine')
        ->assertJsonPath('data.0.cta_label', 'Ver oferta');
});
