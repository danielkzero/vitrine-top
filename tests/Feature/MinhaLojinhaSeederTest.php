<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('database seeder creates the complete minha lojinha demonstration', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $user = User::query()->where('email', 'danikzero@hotmail.com')->firstOrFail();

    expect($user->business_name)->toBe('Minha Lojinha')
        ->and($user->slug)->toBe('minha-lojinha')
        ->and($user->subtitle)->toBe('Bem vindo à minha lojinha')
        ->and($user->categories()->count())->toBe(4);

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
});
