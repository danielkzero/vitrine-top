<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\dashboard\PageController;
use App\Http\Controllers\dashboard\ReviewController;
use App\Http\Controllers\dashboard\SubscriptionController;
use App\Http\Controllers\dashboard\PaymentController;
use App\Http\Controllers\Vitrine\VitrineController;

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
|--------------------------------------------------------------------------
| Rotas do Dashboard e Configurações
|--------------------------------------------------------------------------
*/

require __DIR__ . '/dashboard.php';
require __DIR__ . '/settings.php';

// Exibe páginas dinâmicas criadas pelo usuário
// vitrine.top/minha_loja
//Route::get('/{key}', [PageController::class, 'show'])->name('pages.show');
Route::get('/{slug}', [VitrineController::class, 'show'])->name('vitrine.public');
// vitrine.top/minha_loja/principal
// vitrine.top/minha_loja/catalogo
// vitrine.top/minha_loja/catalogo/id_produto
// vitrine.top/minha_loja/catalogo/?p=1&outras_condicionais
// vitrine.top/minha_loja/galeria
// vitrine.top/minha_loja/galeria/?p=1&outras_condicionais
// vitrine.top/minha_loja/sobre
// vitrine.top/minha_loja/avaliacoes
// vitrine.top/minha_loja/avaliacoes/?p=1&outras_condicionais
