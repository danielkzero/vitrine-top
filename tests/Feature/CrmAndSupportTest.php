<?php

use App\Models\SupportTicket;
use App\Models\User;

test('client can open and reply to own support ticket', function () {
    $client = User::factory()->create();

    $this->actingAs($client)->post(route('painel.support.store'), [
        'subject' => 'Preciso de ajuda',
        'category' => 'technical',
        'message' => 'Não consigo cadastrar um produto.',
    ])->assertRedirect();

    $ticket = SupportTicket::where('user_id', $client->id)->firstOrFail();
    expect($ticket->messages)->toHaveCount(1);

    $this->actingAs($client)->post(route('painel.support.reply', $ticket), [
        'message' => 'Envio mais detalhes do problema.',
    ])->assertRedirect();

    expect($ticket->messages()->count())->toBe(2);
});

test('client cannot reply to another clients ticket', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $ticket = SupportTicket::create([
        'number' => 'TKT-SECURITY-1', 'user_id' => $owner->id, 'subject' => 'Privado',
        'category' => 'question', 'priority' => 'normal', 'status' => 'open', 'last_activity_at' => now(),
    ]);

    $this->actingAs($intruder)->post(route('painel.support.reply', $ticket), ['message' => 'Tentativa'])->assertForbidden();
});

test('administrator can register crm interaction and update client profile', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $client = User::factory()->create();

    $this->actingAs($admin)->put(route('admin.clients.crm.update', $client), [
        'health' => 'attention', 'score' => 55, 'tags' => ['financeiro', 'acompanhamento'],
        'summary' => 'Cliente precisa de acompanhamento.', 'next_follow_up_at' => now()->addWeek(),
    ])->assertRedirect();
    $this->actingAs($admin)->post(route('admin.clients.notes.store', $client), [
        'type' => 'whatsapp', 'content' => 'Cliente respondeu e pediu retorno amanhã.',
    ])->assertRedirect();

    expect($client->crmProfile()->first()->health)->toBe('attention')
        ->and($client->crmNotes()->count())->toBe(1);
});
