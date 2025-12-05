<?php

use App\Http\Controllers\dashboard\PageController;
use App\Http\Controllers\dashboard\PaymentController;
use App\Http\Controllers\dashboard\ReviewController;
use App\Http\Controllers\dashboard\SubscriptionController;
use App\Http\Controllers\Vitrine\VitrineController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Página inicial
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

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
 * |--------------------------------------------------------------------------
 * | Rotas do Dashboard e Configurações
 * |--------------------------------------------------------------------------
 */

require __DIR__ . '/dashboard.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/banner.php';

// Exibe páginas dinâmicas criadas pelo usuário
// vitrine.top/minha_loja
Route::prefix('{slug}')->group(function () {
    // Página inicial da vitrine
    Route::get('/', [VitrineController::class, 'home'])
        ->name('vitrine.public.home');

    // Páginas dinâmicas
    Route::get('/{page}', [VitrineController::class, 'page'])
        ->name('vitrine.public.page');

    // Páginas com ID (ex: produto, item da galeria)
    Route::get('/{page}/{id}', [VitrineController::class, 'pageWithId'])
        ->whereNumber('id')
        ->name('vitrine.public.page.id');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])
        ->name('vitrine.reviews.store');
});
