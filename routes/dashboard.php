<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Dashboard\BaseController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\ReviewController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\PaymentController;

/*
|--------------------------------------------------------------------------
| Rotas do Painel do Usuário (Dashboard)
|--------------------------------------------------------------------------
*/

Route::get('/painel', function () {
    return redirect()->route('painel.index');
})->middleware(['auth', 'verified']);

Route::middleware(['auth', 'verified'])
    ->prefix('painel')
    ->name('painel.')
    ->group(function () {

        // Página inicial do painel
        Route::get('/', [BaseController::class, 'index'])->name('index');

        /*
        |--------------------------------------------------------------------------
        | Categorias, Avaliações, Assinaturas, Pagamentos
        |--------------------------------------------------------------------------
        */
        Route::resource('categories', CategoryController::class);
        Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);
        Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('payments', PaymentController::class)->only(['index', 'show', 'store']);
    });

/*
|--------------------------------------------------------------------------
| Páginas (definidas manualmente para usar "key" em vez de ID)
|--------------------------------------------------------------------------
*/
Route::prefix('painel/pages')
    ->middleware(['auth', 'verified'])
    ->name('painel.pages.')
    ->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('index');
        Route::get('/edit/{key}', [PageController::class, 'edit'])->name('edit');
        Route::post('/update/{key}', [PageController::class, 'update'])->name('update');
        Route::post('/reorder', [PageController::class, 'reorder'])->name('reorder');

    });
