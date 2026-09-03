<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('database seeder creates the complete minha lojinha demonstration', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $user = User::query()->where('email', 'danikzero@hotmail.com')->firstOrFail();

    expect($user->business_name)->toBe('Minha Lojinha')
        ->and($user->slug)->toBe('minha-lojinha')
        ->and($user->subtitle)->toBe('Bem vindo à minha lojinha')
        ->and($user->categories()->count())->toBe(4)
        ->and($user->banners()->where('is_active', true)->count())->toBeGreaterThanOrEqual(1)
        ->and($user->pages()->where('is_active', true)->whereIn('key', ['catalogo', 'sobre', 'links', 'avaliacoes', 'extra'])->count())->toBe(5);

    expect(Product::query()
        ->where('user_id', $user->id)
        ->whereIn('name', [
            'Caneca Nuvem Estrelada',
            'Luminária Cogumelo Encantado',
            'Bolsa Lilás Sweet Heart',
            'Vela Aromática Jardim de Algodão',
        ])
        ->whereHas('images')
        ->count())->toBe(4);

    expect(Review::query()->where('user_id', $user->id)->approved()->count())->toBeGreaterThanOrEqual(8)
        ->and((float) Review::query()->where('user_id', $user->id)->approved()->avg('rating'))->toBeGreaterThanOrEqual(4.5)
        ->and($user->pages()->where('key', 'extra')->value('content'))->toContain('Dados coletados', 'Direitos do titular');
});
