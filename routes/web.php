<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Vitrine\VitrineController;

// Página inicial
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Público: avaliações
Route::get('/avaliacoes', [ReviewController::class, 'publicIndex'])->name('reviews.public');

// Planos
Route::get('/planos', [SubscriptionController::class, 'plans'])->name('subscriptions.plans');

// Pagamentos
Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout', [PaymentController::class, 'process'])->name('checkout.process');
Route::get('/checkout/sucesso', [PaymentController::class, 'success'])->name('checkout.success');
Route::get('/checkout/erro', [PaymentController::class, 'error'])->name('checkout.error');

// Outras rotas internas
require __DIR__ . '/dashboard.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/banner.php';

require __DIR__ . '/api.php'; // <- isso coloca a API ANTES da vitrine
// A vitrine deve SEMPRE ser a ÚLTIMA ROTA!
Route::prefix('{slug}')->group(function () {

    Route::get('/', [VitrineController::class, 'home'])
        ->name('vitrine.public.home');

    Route::get('/{page}', [VitrineController::class, 'page'])
        ->name('vitrine.public.page');

    Route::get('/{page}/{id}', [VitrineController::class, 'pageWithId'])
        ->whereNumber('id')
        ->name('vitrine.public.page.id');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])
        ->name('vitrine.reviews.store');
});
