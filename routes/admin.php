<?php

use App\Http\Controllers\Admin\ClientCrmController;
use App\Http\Controllers\Admin\PlatformAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [PlatformAdminController::class, 'index'])->name('index');
        Route::put('/clientes/{user}/assinatura', [PlatformAdminController::class, 'updateSubscription'])
            ->name('clients.subscription.update');
        Route::get('/clientes/{user}', [ClientCrmController::class, 'show'])->name('clients.show');
        Route::put('/clientes/{user}/crm', [ClientCrmController::class, 'updateProfile'])->name('clients.crm.update');
        Route::post('/clientes/{user}/notas', [ClientCrmController::class, 'addNote'])->name('clients.notes.store');
        Route::post('/clientes/{user}/tickets', [ClientCrmController::class, 'createTicket'])->name('clients.tickets.store');
        Route::put('/tickets/{ticket}', [ClientCrmController::class, 'updateTicket'])->name('tickets.update');
    });
