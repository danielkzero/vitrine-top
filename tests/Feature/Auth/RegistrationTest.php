<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Daniel',
        'email' => 'danikzero@hotmail.com',
        'password' => 'Vlp@@2025Put',
        'password_confirmation' => 'Vlp@@2025Put',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('painel.index', absolute: false));
});
