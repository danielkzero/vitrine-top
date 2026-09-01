<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

test('deleting a category also deletes all of its products', function () {
    $user = User::factory()->create();
    $category = Category::create(['user_id' => $user->id, 'name' => 'Descontinuados']);

    $products = collect(['Produto A', 'Produto B'])->map(fn (string $name) => Product::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'name' => $name,
        'price' => 10,
    ]));

    $this->actingAs($user)
        ->delete(route('painel.categories.destroy', $category))
        ->assertRedirect();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    $products->each(fn (Product $product) => $this->assertDatabaseMissing('products', ['id' => $product->id]));
});

test('a user cannot delete another users category or products', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $category = Category::create(['user_id' => $owner->id, 'name' => 'Privada']);
    $product = Product::create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'name' => 'Produto privado',
        'price' => 10,
    ]);

    $this->actingAs($intruder)
        ->delete(route('painel.categories.destroy', $category))
        ->assertForbidden();

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('products', ['id' => $product->id]);
});
