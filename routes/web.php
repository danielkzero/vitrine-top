<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\PaymentController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Exibe páginas dinâmicas criadas pelo usuário
Route::get('/pagina/{key}', [PageController::class, 'show'])->name('pages.show');

// Mostra avaliações públicas (reviews aprovadas)
Route::get('/avaliacoes', [ReviewController::class, 'publicIndex'])->name('reviews.public');

// Página de planos disponíveis (antes de assinar)
Route::get('/planos', [SubscriptionController::class, 'plans'])->name('subscriptions.plans');

// Checkout e pagamentos
Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout', [PaymentController::class, 'process'])->name('checkout.process');

// Páginas de retorno de pagamento
Route::get('/checkout/sucesso', [PaymentController::class, 'success'])->name('checkout.success');
Route::get('/checkout/erro', [PaymentController::class, 'error'])->name('checkout.error');

/*
|--------------------------------------------------------------------------
| Rotas do Dashboard e Configurações
|--------------------------------------------------------------------------
*/

require __DIR__ . '/dashboard.php';
require __DIR__ . '/settings.php';
