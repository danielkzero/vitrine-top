<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('painel'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the painel', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('painel'));
    $response->assertStatus(200);
});