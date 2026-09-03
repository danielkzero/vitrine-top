<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
});

test('the demonstration link opens the seeded storefront catalog', function () {
    $this->get(route('demo'))
        ->assertRedirect('/minha-lojinha/catalogo');
});
